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

function storedFlag(key, fallback) {
    const stored = localStorage.getItem(key);
    if (stored === '1') {
        return true;
    }
    if (stored === '0') {
        return false;
    }

    return fallback;
}

function storedValue(key, fallback) {
    const stored = localStorage.getItem(key);

    return stored === null || stored === '' ? fallback : stored;
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

function screenHeadingOffset() {
    if (typeof screen.orientation?.angle === 'number') {
        return screen.orientation.angle;
    }
    if (typeof window.orientation === 'number') {
        return window.orientation;
    }

    return 0;
}

function normalizeDegrees(value) {
    return ((value % 360) + 360) % 360;
}

function shortestDelta(from, to) {
    return ((((to - from) % 360) + 540) % 360) - 180;
}

function unwrapToward(previous, nextNormalized) {
    if (previous == null || Number.isNaN(previous)) {
        return nextNormalized;
    }

    return previous + shortestDelta(normalizeDegrees(previous), nextNormalized);
}

function normalizeMode(value) {
    return value === 'arrow' || value === 'camera' ? value : 'compass';
}

class QiblaApp {
    constructor(root) {
        this.root = root;
        this.rose = root.querySelector('[data-rose]');
        this.needle = root.querySelector('[data-needle]');
        this.needleLayer = root.querySelector('[data-needle-layer]');
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
            vibration: storedFlag('qf_vib', root.dataset.defaultVibration !== '0'),
            audio: storedFlag('qf_audio', root.dataset.defaultAudio === '1'),
            mode: normalizeMode(storedValue('qf_mode', root.dataset.defaultMode || 'compass')),
            interval: Math.max(5, Math.min(3600, Number(storedValue('qf_interval', root.dataset.updateInterval || '300')) || 300)),
        };
        this.state = {
            lat: null,
            lng: null,
            label: null,
            qibla: null,
            heading: null,
            aligned: false,
            locked: false,
            sensor: false,
            headingSource: null,
        };
        this.map = null;
        this.userMarker = null;
        this.line = null;
        this.audioCtx = null;
        this.compassStarted = false;
        this.locationTimer = null;
        this.displayNeedle = 0;
        this.displayRose = 0;
        this.smoothedHeading = null;
        this.cameraStream = null;
        this.cameraStarting = false;
        this.cameraVideo = root.querySelector('[data-camera-video]');
        this.cameraView = root.querySelector('[data-camera-view]');
        this.cameraKaaba = root.querySelector('[data-camera-kaaba]');
        this.cameraHeadingEl = root.querySelector('[data-camera-heading]');
        this.cameraQiblaEl = root.querySelector('[data-camera-qibla]');
        this.cameraTurnLeft = root.querySelector('[data-camera-turn="left"]');
        this.cameraTurnRight = root.querySelector('[data-camera-turn="right"]');
        this.bind();
        this.restore();
        this.askLocation();
        this.startCompass();
    }

    bind() {
        this.root.querySelectorAll('[data-locate]').forEach((button) => {
            button.addEventListener('click', () => {
                this.unlockQibla();
                this.startCompass();
                this.askLocation(true);
            });
        });
        this.root.querySelector('[data-calibrate]')?.addEventListener('click', () => {
            this.unlockQibla();
            this.startCompass();
            this.showCalibrate(true);
        });
        this.root.querySelectorAll('[data-close-calibrate]').forEach((el) => {
            el.addEventListener('click', () => this.showCalibrate(false));
        });
        this.root.querySelector('[data-mode-select]')?.addEventListener('change', (e) => {
            this.setMode(e.target.value, true);
        });
        this.root.querySelectorAll('[data-camera-open]').forEach((button) => {
            button.addEventListener('click', () => this.setMode('camera', true));
        });
        document.querySelectorAll('[data-hero-camera]').forEach((button) => {
            button.addEventListener('click', () => {
                this.root.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.setMode('camera', true);
            });
        });
        this.root.querySelector('[data-camera-close]')?.addEventListener('click', () => {
            this.setMode('compass', true);
        });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopCamera();
                return;
            }
            if (this.settings.mode === 'camera') {
                this.startCamera();
            }
        });
        this.root.querySelector('[data-toggle-vib]')?.addEventListener('change', (e) => {
            this.settings.vibration = e.target.checked;
            localStorage.setItem('qf_vib', e.target.checked ? '1' : '0');
        });
        this.root.querySelector('[data-toggle-audio]')?.addEventListener('change', (e) => {
            this.settings.audio = e.target.checked;
            localStorage.setItem('qf_audio', e.target.checked ? '1' : '0');
        });
        this.root.querySelector('[data-interval-input]')?.addEventListener('change', (e) => {
            const seconds = Math.max(5, Math.min(3600, Number(e.target.value) || 300));
            this.settings.interval = seconds;
            e.target.value = String(seconds);
            localStorage.setItem('qf_interval', String(seconds));
            this.scheduleLocationUpdates();
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
                this.unlockQibla();
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
        const interval = this.root.querySelector('[data-interval-input]');
        if (vib) vib.checked = this.settings.vibration;
        if (audio) audio.checked = this.settings.audio;
        if (interval) interval.value = String(this.settings.interval);
        const modeSelect = this.root.querySelector('[data-mode-select]');
        if (modeSelect) {
            modeSelect.value = this.settings.mode === 'camera' ? 'camera' : 'compass';
        }
        this.root.classList.toggle('is-camera', this.settings.mode === 'camera');
        this.cameraView?.classList.toggle('hidden', this.settings.mode !== 'camera');
    }

    showCalibrate(open) {
        this.root.querySelector('[data-calibrate-modal]')?.classList.toggle('hidden', !open);
    }

    showSettings(open) {
        this.root.querySelector('[data-settings-modal]')?.classList.toggle('hidden', !open);
    }

    setMode(mode, fromGesture = false) {
        this.settings.mode = normalizeMode(mode);
        localStorage.setItem('qf_mode', this.settings.mode);
        this.syncToggles();
        if (this.settings.mode === 'camera') {
            this.unlockQibla();
            this.startCompass();
            if (this.state.lat == null) {
                this.askLocation(fromGesture);
            }
            if (fromGesture) {
                this.startCamera();
            }
            this.setStatus(this.i18n.camera_hold || this.i18n.camera_hint || 'Hold the phone upright and turn toward the Kaaba.');
        } else {
            this.stopCamera();
        }
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
                this.scheduleLocationUpdates();
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

    scheduleLocationUpdates() {
        if (this.locationTimer) {
            window.clearInterval(this.locationTimer);
            this.locationTimer = null;
        }
        if (this.state.locked || ! navigator.geolocation) {
            return;
        }
        const ms = this.settings.interval * 1000;
        this.locationTimer = window.setInterval(() => {
            if (this.state.locked) {
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    if (this.state.locked) {
                        return;
                    }
                    this.setLocation(pos.coords.latitude, pos.coords.longitude, this.state.label, true);
                },
                () => {},
                { enableHighAccuracy: true, timeout: 15000, maximumAge: ms },
            );
        }, ms);
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
        if (this.state.locked) {
            return;
        }
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

    async startCamera() {
        if (this.cameraStream || this.cameraStarting) {
            return;
        }
        if (! this.cameraVideo || ! navigator.mediaDevices?.getUserMedia) {
            this.setStatus(this.i18n.camera_unavailable || 'This browser cannot open the camera. Use the compass instead.');
            this.setMode('compass');
            return;
        }

        this.cameraStarting = true;
        try {
            this.cameraStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            });
            this.cameraVideo.srcObject = this.cameraStream;
            this.cameraVideo.setAttribute('playsinline', '');
            this.cameraVideo.muted = true;
            await this.cameraVideo.play();
            this.setStatus(this.i18n.camera_hint || 'Hold the phone upright. Turn until the Kaaba meets the gold mark.');
        } catch {
            this.stopCamera();
            this.setStatus(this.i18n.camera_denied || 'Camera access was denied. Allow the camera, then tap Live camera again.');
            this.setMode('compass');
        } finally {
            this.cameraStarting = false;
        }
    }

    stopCamera() {
        this.cameraStream?.getTracks().forEach((track) => track.stop());
        this.cameraStream = null;
        if (this.cameraVideo) {
            this.cameraVideo.srcObject = null;
        }
    }

    applyCameraOverlay() {
        if (! this.cameraKaaba || this.settings.mode !== 'camera') {
            return;
        }

        const delta = shortestDelta(0, normalizeDegrees(this.displayNeedle));
        const fov = 64;
        const clamped = Math.max(-fov, Math.min(fov, delta));
        const shift = (clamped / fov) * 42;
        this.cameraKaaba.style.transform = `translate(calc(-50% + ${shift}%), -50%)`;

        const off = Math.abs(delta) > fov * 0.92;
        this.cameraTurnLeft?.classList.toggle('hidden', ! (off && delta < 0));
        this.cameraTurnRight?.classList.toggle('hidden', ! (off && delta > 0));
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
        const fromWebkit = typeof event.webkitCompassHeading === 'number';
        const fromAbsolute = event.type === 'deviceorientationabsolute' || event.absolute === true;
        let heading = null;
        let source = this.state.headingSource;

        if (fromWebkit) {
            heading = event.webkitCompassHeading;
            source = 'webkit';
        } else if (fromAbsolute && typeof event.alpha === 'number') {
            if (this.state.headingSource === 'webkit') {
                return;
            }
            heading = (360 - event.alpha) % 360;
            source = 'absolute';
        } else if (typeof event.alpha === 'number' && this.state.headingSource !== 'webkit' && this.state.headingSource !== 'absolute') {
            heading = (360 - event.alpha) % 360;
            source = 'relative';
        }

        if (heading === null || Number.isNaN(heading)) {
            return;
        }

        this.state.sensor = true;
        this.state.headingSource = source;
        const raw = normalizeDegrees(heading + screenHeadingOffset());

        if (this.state.locked) {
            this.state.heading = raw;
            if (this.state.qibla != null && Math.abs(shortestDelta(raw, this.state.qibla)) > 22) {
                this.unlockQibla();
                this.smoothedHeading = raw;
                this.state.heading = raw;
                this.render();
            }

            return;
        }

        if (this.smoothedHeading == null) {
            this.smoothedHeading = raw;
        } else {
            this.smoothedHeading = normalizeDegrees(
                this.smoothedHeading + shortestDelta(this.smoothedHeading, raw) * 0.4,
            );
        }
        this.state.heading = this.smoothedHeading;
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
        if (this.cameraHeadingEl) {
            this.cameraHeadingEl.textContent = heading == null ? '—' : `${heading.toFixed(0)}°`;
        }
        if (this.cameraQiblaEl) {
            this.cameraQiblaEl.textContent = `${this.i18n.qibla_short || 'Qibla'} ${qiblaText}`;
        }

        const device = heading ?? 0;
        const delta = Math.abs(shortestDelta(device, qibla));
        const relative = normalizeDegrees(qibla - device);
        const roseAngle = this.settings.mode === 'compass' ? normalizeDegrees(-device) : 0;

        if (! this.state.locked) {
            this.displayNeedle = unwrapToward(this.displayNeedle, relative);
            this.displayRose = unwrapToward(this.displayRose, roseAngle);
            this.applyPointer();
        }

        const aligned = this.state.sensor && (this.state.locked || delta <= 10);
        this.root.classList.toggle('aligned', aligned);
        this.root.classList.toggle('qibla-locked', this.state.locked);
        this.alignBadge?.classList.toggle('hidden', !aligned);

        if (this.state.sensor && ! this.state.locked && delta <= 10) {
            this.lockQibla();

            return;
        }

        this.state.aligned = aligned;
    }

    applyPointer() {
        if (this.needleLayer) {
            this.needleLayer.style.transform = `rotate(${this.displayNeedle}deg)`;
        } else if (this.needle) {
            this.needle.setAttribute('transform', `rotate(${normalizeDegrees(this.displayNeedle)} 200 200)`);
        }
        if (this.rose) {
            this.rose.style.transform = `rotate(${this.displayRose}deg)`;
        }
        this.applyCameraOverlay();
    }

    lockQibla() {
        if (this.state.locked) {
            return;
        }
        this.state.locked = true;
        this.state.aligned = true;
        this.displayNeedle = unwrapToward(this.displayNeedle, 0);
        this.applyPointer();
        this.root.classList.add('aligned', 'qibla-locked');
        this.alignBadge?.classList.remove('hidden');
        if (this.locationTimer) {
            window.clearInterval(this.locationTimer);
            this.locationTimer = null;
        }
        this.celebrate();
    }

    unlockQibla() {
        const wasLocked = this.state.locked;
        this.state.locked = false;
        this.state.aligned = false;
        this.root.classList.remove('aligned', 'qibla-locked');
        this.alignBadge?.classList.add('hidden');
        if (wasLocked) {
            this.scheduleLocationUpdates();
        }
    }

    celebrate() {
        this.setStatus(this.i18n.qibla_locked || 'Qibla locked. Hold still — your direction is set.');
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
