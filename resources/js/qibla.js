import { bindPlaceSearch, timezoneFromLng } from './places';

const KAABA = { lat: 21.422487, lng: 39.826206 };

function toRad(value) {
    return (value * Math.PI) / 180;
}

function toDeg(value) {
    return (value * 180) / Math.PI;
}

export function qiblaBearing(lat, lng) {
    const lat1 = toRad(lat);
    const lat2 = toRad(KAABA.lat);
    const dLng = toRad(KAABA.lng - lng);
    const y = Math.sin(dLng);
    const x = Math.cos(lat1) * Math.tan(lat2) - Math.sin(lat1) * Math.cos(dLng);
    return (toDeg(Math.atan2(y, x)) + 360) % 360;
}

export function distanceKm(lat, lng) {
    const dLat = toRad(KAABA.lat - lat);
    const dLng = toRad(KAABA.lng - lng);
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat)) * Math.cos(toRad(KAABA.lat)) * Math.sin(dLng / 2) ** 2;
    return 6371.0088 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function cardinal(bearing) {
    const labels = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
    return labels[Math.round(bearing / 22.5) % 16];
}

function formatLocation(lat, lng, label) {
    if (label) {
        return label;
    }
    const ns = lat >= 0 ? 'N' : 'S';
    const ew = lng >= 0 ? 'E' : 'W';
    return `${Math.abs(lat).toFixed(4)}° ${ns}, ${Math.abs(lng).toFixed(4)}° ${ew}`;
}

class QiblaApp {
    constructor(root) {
        this.root = root;
        this.rose = root.querySelector('[data-rose]');
        this.needle = root.querySelector('[data-needle]');
        this.status = root.querySelector('[data-status]');
        this.overlay = root.querySelector('[data-permission-overlay]');
        this.bearingEl = document.querySelector('[data-bearing]');
        this.locationEl = document.querySelector('[data-location]');
        this.distanceEl = document.querySelector('[data-distance]');
        this.headingEl = document.querySelector('[data-heading]');
        this.hubHeading = root.querySelector('[data-compass-heading]');
        this.hubQibla = root.querySelector('[data-compass-qibla]');
        this.hubPlace = root.querySelector('[data-compass-place]');
        this.alignBadge = root.querySelector('[data-aligned]');
        this.cityInput = root.querySelector('[data-city-search]');
        this.cityList = root.querySelector('[data-city-results]');
        this.mapEl = document.getElementById('qibla-map');
        this.cities = JSON.parse(root.dataset.cities || '[]');
        this.i18n = JSON.parse(root.dataset.i18n || '{}');
        this.placesUrl = root.dataset.placesUrl || '/places/search';
        this.settings = {
            vibration: localStorage.getItem('qf_vib') !== '0',
            audio: localStorage.getItem('qf_audio') === '1',
            mode: localStorage.getItem('qf_mode') || 'compass',
        };
        this.state = {
            lat: null,
            lng: null,
            label: null,
            qibla: null,
            heading: null,
            aligned: false,
            sensor: false,
        };
        this.map = null;
        this.userMarker = null;
        this.line = null;
        this.audioCtx = null;
        this.compassStarted = false;
        this.geoWatch = null;
        this.bind();
        this.restore();
        this.askLocation();
        this.startCompass();
    }

    bind() {
        this.root.querySelectorAll('[data-locate]').forEach((button) => {
            button.addEventListener('click', () => {
                this.startCompass();
                this.askLocation(true);
            });
        });
        this.root.querySelector('[data-calibrate]')?.addEventListener('click', () => {
            this.startCompass();
            this.showCalibrate(true);
        });
        this.root.querySelectorAll('[data-close-calibrate]').forEach((el) => {
            el.addEventListener('click', () => this.showCalibrate(false));
        });
        this.root.querySelectorAll('[data-mode]').forEach((el) => {
            el.addEventListener('click', () => this.setMode(el.dataset.mode));
        });
        this.root.querySelector('[data-toggle-vib]')?.addEventListener('change', (e) => {
            this.settings.vibration = e.target.checked;
            localStorage.setItem('qf_vib', e.target.checked ? '1' : '0');
        });
        this.root.querySelector('[data-toggle-audio]')?.addEventListener('change', (e) => {
            this.settings.audio = e.target.checked;
            localStorage.setItem('qf_audio', e.target.checked ? '1' : '0');
        });
        this.root.querySelector('[data-settings-open]')?.addEventListener('click', () => this.showSettings(true));
        this.root.querySelectorAll('[data-settings-close]').forEach((el) => {
            el.addEventListener('click', () => this.showSettings(false));
        });
        bindPlaceSearch({
            input: this.cityInput,
            list: this.cityList,
            cities: this.cities,
            endpoint: this.placesUrl,
            searchingLabel: this.i18n.searching_places || 'Searching…',
            emptyLabel: this.i18n.no_places || 'No places found.',
            onPick: (place) => {
                this.setLocation(place.lat, place.lng, place.label);
                this.startCompass();
            },
        });
        this.syncToggles();
    }

    restore() {
        const saved = localStorage.getItem('qf_last_location');
        if (! saved) {
            this.showPermission(true);
            return;
        }
        const parsed = JSON.parse(saved);
        this.setLocation(parsed.lat, parsed.lng, parsed.label, false);
        this.showPermission(false);
    }

    showPermission(open) {
        this.overlay?.classList.toggle('hidden', ! open);
    }

    syncToggles() {
        const vib = this.root.querySelector('[data-toggle-vib]');
        const audio = this.root.querySelector('[data-toggle-audio]');
        if (vib) vib.checked = this.settings.vibration;
        if (audio) audio.checked = this.settings.audio;
        this.root.querySelectorAll('[data-mode]').forEach((el) => {
            el.classList.toggle('is-active', el.dataset.mode === this.settings.mode);
        });
    }

    showCalibrate(open) {
        this.root.querySelector('[data-calibrate-modal]')?.classList.toggle('hidden', !open);
    }

    showSettings(open) {
        this.root.querySelector('[data-settings-modal]')?.classList.toggle('hidden', !open);
    }

    setMode(mode) {
        this.settings.mode = mode;
        localStorage.setItem('qf_mode', mode);
        this.syncToggles();
        this.render();
    }

    askLocation(fromGesture = false) {
        this.setStatus(this.i18n.requesting_location || 'Requesting location…');
        if (! navigator.geolocation) {
            this.setStatus(this.i18n.geo_unavailable_city || 'Geolocation is not available. Choose a city instead.');
            return;
        }

        const applyPosition = (pos, reverse) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            if (reverse) {
                this.reverseAndSet(lat, lng);
                return;
            }
            this.setLocation(lat, lng, this.state.label);
            this.showPermission(false);
            this.startCompass();
        };

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                applyPosition(pos, true);
                if (this.geoWatch == null) {
                    this.geoWatch = navigator.geolocation.watchPosition(
                        (next) => applyPosition(next, false),
                        () => {},
                        { enableHighAccuracy: true, maximumAge: 15000 },
                    );
                }
            },
            () => {
                if (this.state.lat == null) {
                    this.showPermission(true);
                }
                this.setStatus(this.i18n.location_denied_qibla || 'Location denied. Search a city or enter coordinates in settings.');
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: fromGesture ? 0 : 60000,
            },
        );
    }

    async reverseAndSet(lat, lng) {
        let label = this.state.label;
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`,
                { headers: { Accept: 'application/json' } },
            );
            if (res.ok) {
                const json = await res.json();
                label = json.address?.city || json.address?.town || json.address?.village || json.address?.suburb || json.display_name;
            }
        } catch {
            // Reverse geocoding is optional.
        }
        this.setLocation(lat, lng, label);
        this.showPermission(false);
        this.startCompass();
    }

    setLocation(lat, lng, label = null, persist = true) {
        this.state.lat = lat;
        this.state.lng = lng;
        this.state.label = label;
        this.state.qibla = qiblaBearing(lat, lng);
        if (persist) {
            localStorage.setItem('qf_last_location', JSON.stringify({ lat, lng, label, timezone: timezoneFromLng(lng) }));
        }
        this.showPermission(false);
        this.render();
        this.drawMap();
        this.setStatus(this.i18n.location_locked || 'Location locked. Hold your phone flat and turn toward the gold notch.');
    }

    async startCompass() {
        try {
            if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                const permission = await DeviceOrientationEvent.requestPermission();
                if (permission !== 'granted') {
                    this.setStatus(this.i18n.motion_permission || 'Enable Motion & Orientation Access in Safari settings, then retry.');
                    return;
                }
            }
        } catch {
            // Desktop browsers throw here.
        }

        if (this.compassStarted) {
            return;
        }
        this.compassStarted = true;
        window.addEventListener('deviceorientationabsolute', (e) => this.onOrientation(e), true);
        window.addEventListener('deviceorientation', (e) => this.onOrientation(e), true);
    }

    onOrientation(event) {
        let heading = null;
        if (typeof event.webkitCompassHeading === 'number') {
            heading = event.webkitCompassHeading;
        } else if (typeof event.alpha === 'number') {
            heading = (360 - event.alpha) % 360;
        }
        if (heading === null || Number.isNaN(heading)) {
            return;
        }
        this.state.sensor = true;
        this.state.heading = heading;
        this.render();
    }

    render() {
        const { lat, lng, qibla, heading, label } = this.state;
        if (this.hubHeading) {
            this.hubHeading.textContent = heading == null ? '—' : `${heading.toFixed(0)}°`;
        }
        if (this.hubPlace) {
            this.hubPlace.textContent = label || (lat == null ? this.i18n.permission_title || 'Allow location' : formatLocation(lat, lng, label));
        }

        if (lat == null || qibla == null) {
            return;
        }

        const km = distanceKm(lat, lng);
        const qiblaText = `${qibla.toFixed(1)}° ${cardinal(qibla)}`;
        if (this.bearingEl) {
            this.bearingEl.textContent = qiblaText;
        }
        if (this.hubQibla) {
            this.hubQibla.textContent = `${this.i18n.qibla_short || 'Qibla'} ${qiblaText}`;
        }
        if (this.locationEl) {
            this.locationEl.textContent = formatLocation(lat, lng, label);
        }
        if (this.distanceEl) {
            this.distanceEl.textContent = `${km.toFixed(0)} km · ${(km * 0.621371).toFixed(0)} mi`;
        }
        if (this.headingEl) {
            this.headingEl.textContent = heading == null ? this.i18n.true_north || 'True north' : `${heading.toFixed(0)}°`;
        }

        const device = heading ?? 0;
        if (this.settings.mode === 'compass') {
            if (this.rose) this.rose.style.transform = `rotate(${-device}deg)`;
            if (this.needle) this.needle.setAttribute('transform', `rotate(${qibla} 200 200)`);
        } else {
            if (this.rose) this.rose.style.transform = 'rotate(0deg)';
            if (this.needle) this.needle.setAttribute('transform', `rotate(${qibla - device} 200 200)`);
        }

        const delta = Math.abs((((qibla - device + 540) % 360) - 180));
        const aligned = this.state.sensor && delta <= 8;
        this.root.classList.toggle('aligned', aligned);
        this.alignBadge?.classList.toggle('hidden', !aligned);
        if (aligned && !this.state.aligned) {
            this.celebrate();
        }
        this.state.aligned = aligned;
    }

    celebrate() {
        this.setStatus(this.i18n.facing_qibla || 'You are facing the Qibla.');
        if (this.settings.vibration && navigator.vibrate) {
            navigator.vibrate([40, 40, 80]);
        }
        if (this.settings.audio) {
            this.chime();
        }
    }

    chime() {
        try {
            this.audioCtx ??= new AudioContext();
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.frequency.value = 528;
            gain.gain.value = 0.04;
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.start();
            osc.stop(this.audioCtx.currentTime + 0.18);
        } catch {
            // Audio is optional.
        }
    }

    setStatus(text) {
        if (this.status) this.status.textContent = text;
    }

    drawMap() {
        if (!this.mapEl || typeof window.L === 'undefined' || this.state.lat == null) {
            return;
        }
        if (!this.map) {
            this.map = window.L.map(this.mapEl, { zoomControl: true, attributionControl: true }).setView(
                [this.state.lat, this.state.lng],
                3,
            );
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap',
            }).addTo(this.map);
        }
        const user = [this.state.lat, this.state.lng];
        const kaaba = [KAABA.lat, KAABA.lng];
        if (this.userMarker) this.map.removeLayer(this.userMarker);
        if (this.line) this.map.removeLayer(this.line);
        this.userMarker = window.L.marker(user).addTo(this.map).bindPopup(this.i18n.you || 'You');
        window.L.circleMarker(kaaba, { radius: 8, color: '#c9a227', fillColor: '#c9a227', fillOpacity: 1 })
            .addTo(this.map)
            .bindPopup(this.i18n.kaaba || 'Kaaba');
        this.line = window.L.polyline([user, kaaba], { color: '#0d3b2e', weight: 2, dashArray: '6 8' }).addTo(this.map);
        this.map.fitBounds([user, kaaba], { padding: [40, 40] });
    }
}

const root = document.querySelector('[data-qibla-app]');
if (root) {
    window.qiblaApp = new QiblaApp(root);
}
