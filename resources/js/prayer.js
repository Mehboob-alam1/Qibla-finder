import { bindPlaceSearch, timezoneFromLng } from './places';

const NAMES = ['imsak', 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha', 'midnight'];

function formatClock(date, timeZone) {
    const locale = document.documentElement.lang || 'en';
    return new Intl.DateTimeFormat(locale, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: timeZone || undefined,
    }).format(date);
}

function formatRemain(seconds, template = ':h h :m m :s s') {
    const safe = Math.max(0, seconds);
    const h = Math.floor(safe / 3600);
    const m = Math.floor((safe % 3600) / 60);
    const s = Math.floor(safe % 60);
    return template.replace(':h', h).replace(':m', m).replace(':s', s);
}

function format12(time24) {
    if (! time24 || ! String(time24).includes(':')) {
        return time24 || '—';
    }
    const [h, m] = String(time24).split(':').map(Number);
    if (Number.isNaN(h) || Number.isNaN(m)) {
        return time24;
    }
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;

    return `${h12}:${String(m).padStart(2, '0')} ${ampm}`;
}

function formatDisplayDate(date, timeZone) {
    const locale = document.documentElement.lang || 'en';

    return new Intl.DateTimeFormat(locale, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        timeZone: timeZone || undefined,
    }).format(date);
}

class PrayerApp {
    constructor(root) {
        this.root = root;
        this.clock = root.querySelector('[data-clock]');
        this.next = root.querySelector('[data-next]');
        this.rows = root.querySelector('[data-rows]');
        this.meta = root.querySelector('[data-meta]');
        this.hijri = root.querySelector('[data-hijri]');
        this.prayerDate = root.querySelector('[data-prayer-date]');
        this.methodLine = root.querySelector('[data-method-line]');
        this.timezoneLine = root.querySelector('[data-timezone-line]');
        this.method = root.querySelector('[data-method]');
        this.asr = root.querySelector('[data-asr]');
        this.highLatitude = root.querySelector('[data-high-latitude]');
        this.midnightMode = root.querySelector('[data-midnight-mode]');
        this.useLocationTz = root.querySelector('[data-use-location-tz]');
        this.latInput = root.querySelector('[data-lat-input]');
        this.lngInput = root.querySelector('[data-lng-input]');
        this.cityInput = root.querySelector('[data-city-search]');
        this.cityList = root.querySelector('[data-city-results]');
        this.settingsModal = root.querySelector('[data-settings-modal]');
        this.mapEl = document.getElementById('prayer-map');
        this.cities = JSON.parse(root.dataset.cities || '[]');
        this.labels = JSON.parse(root.dataset.labels || '{}');
        this.arabicNames = JSON.parse(root.dataset.arabicNames || '{}');
        this.nextTemplate = root.dataset.nextTemplate || 'Next prayer: :name in :time';
        this.remainTemplate = root.dataset.remainTemplate || ':h h :m m :s s';
        this.methodPrefix = root.dataset.methodPrefix || 'Method:';
        this.timezonePrefix = root.dataset.timezonePrefix || 'Timezone:';
        this.deviceTzLabel = root.dataset.deviceTzLabel || 'Device timezone';
        this.locationTzLabel = root.dataset.locationTzLabel || 'Location timezone';
        this.i18n = JSON.parse(root.dataset.i18n || '{}');
        this.placesUrl = root.dataset.placesUrl || '/places/search';
        this.timezoneEndpoint = root.dataset.timezoneEndpoint || '/prayer-times/timezone';
        this.csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        this.deviceTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
        this.state = {
            lat: null,
            lng: null,
            label: null,
            payload: null,
            timezone: null,
            resolvedTimezone: null,
        };
        this.fetchedAt = Date.now();
        this.map = null;
        this.marker = null;
        this.mapReady = false;
        this.bind();
        this.loadSettings();
        this.initMap();
        this.tick();
        setInterval(() => this.tick(), 1000);
        this.restore();
        if (this.state.lat == null) {
            this.setLocation(33.690277, 73.073802, 'Islamabad', 'Asia/Karachi', true);
        }
        this.locate();
    }

    bind() {
        this.root.querySelector('[data-locate]')?.addEventListener('click', () => this.locate());
        this.root.querySelector('[data-apply-coords]')?.addEventListener('click', () => this.applyCoordinates());
        this.root.querySelector('[data-settings-open]')?.addEventListener('click', () => this.showSettings(true));
        this.root.querySelectorAll('[data-settings-close]').forEach((el) => {
            el.addEventListener('click', () => this.showSettings(false));
        });
        this.method?.addEventListener('change', () => {
            this.saveSettings();
            this.refresh();
        });
        this.asr?.addEventListener('change', () => {
            this.saveSettings();
            this.refresh();
        });
        this.highLatitude?.addEventListener('change', () => {
            this.saveSettings();
            this.refresh();
        });
        this.midnightMode?.addEventListener('change', () => {
            this.saveSettings();
            this.refresh();
        });
        this.useLocationTz?.addEventListener('change', () => {
            this.saveSettings();
            this.refresh();
        });
        this.root.querySelector('[data-month]')?.addEventListener('click', () => this.loadMonth());
        bindPlaceSearch({
            input: this.cityInput,
            list: this.cityList,
            cities: this.cities,
            endpoint: this.placesUrl,
            searchingLabel: this.i18n.searching_places || 'Searching…',
            emptyLabel: this.i18n.no_places || 'No places found.',
            onPick: (place) => {
                this.setLocation(place.lat, place.lng, place.label, place.timezone || null, true);
            },
        });
    }

    loadSettings() {
        const saved = localStorage.getItem('qf_prayer_settings');
        if (! saved) {
            if (this.method && this.root.dataset.defaultMethod) {
                this.method.value = this.root.dataset.defaultMethod;
            }

            return;
        }
        try {
            const parsed = JSON.parse(saved);
            if (this.method && parsed.method) {
                this.method.value = parsed.method;
            }
            if (this.asr && parsed.asr) {
                this.asr.value = parsed.asr;
            }
            if (this.highLatitude && parsed.highLatitude) {
                this.highLatitude.value = parsed.highLatitude;
            }
            if (this.midnightMode && parsed.midnightMode) {
                this.midnightMode.value = parsed.midnightMode;
            }
            if (this.useLocationTz) {
                this.useLocationTz.checked = parsed.useLocationTz !== false;
            }
        } catch {
            // Ignore invalid saved settings.
        }
    }

    saveSettings() {
        localStorage.setItem(
            'qf_prayer_settings',
            JSON.stringify({
                method: this.method?.value,
                asr: this.asr?.value,
                highLatitude: this.highLatitude?.value,
                midnightMode: this.midnightMode?.value,
                useLocationTz: this.useLocationTz?.checked !== false,
            }),
        );
    }

    showSettings(open) {
        this.settingsModal?.classList.toggle('hidden', ! open);
    }

    initMap() {
        if (! this.mapEl || typeof window.L === 'undefined') {
            return;
        }
        this.map = window.L.map(this.mapEl, { zoomControl: true }).setView([33.6844, 73.0479], 11);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap',
        }).addTo(this.map);
        this.marker = window.L.marker([33.6844, 73.0479], { draggable: true }).addTo(this.map);
        this.marker.on('dragend', () => {
            const { lat, lng } = this.marker.getLatLng();
            this.setLocation(lat, lng, this.state.label, null, true);
        });
        this.mapReady = true;
    }

    updateMap(lat, lng, zoom = null) {
        if (! this.mapReady || ! this.marker) {
            return;
        }
        this.marker.setLatLng([lat, lng]);
        const targetZoom = zoom ?? Math.max(this.map.getZoom(), 11);
        this.map.setView([lat, lng], targetZoom, { animate: true });
    }

    restore() {
        const saved = localStorage.getItem('qf_last_location');
        if (saved) {
            const parsed = JSON.parse(saved);
            this.setLocation(parsed.lat, parsed.lng, parsed.label, parsed.timezone || null, false);
        }
    }

    applyCoordinates() {
        const lat = Number(this.latInput?.value);
        const lng = Number(this.lngInput?.value);
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }
        this.setLocation(lat, lng, this.state.label, null, true);
    }

    async locate() {
        if (! navigator.geolocation) {
            this.setMeta(this.i18n.geo_unavailable || 'Geolocation is not available on this device.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => this.setLocation(pos.coords.latitude, pos.coords.longitude, null, this.deviceTimezone, true),
            () => {
                if (this.state.lat == null) {
                    this.setMeta(this.i18n.location_denied || 'Location denied. Search a city instead.');
                }
            },
            { enableHighAccuracy: true },
        );
    }

    effectiveTimezone() {
        if (this.useLocationTz?.checked === false) {
            return this.deviceTimezone;
        }

        return this.state.resolvedTimezone || this.state.timezone || timezoneFromLng(this.state.lng ?? 0);
    }

    async resolveTimezone(lat, lng) {
        try {
            const body = new FormData();
            body.set('lat', lat);
            body.set('lng', lng);
            const res = await fetch(this.timezoneEndpoint, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
                body,
            });
            if (res.ok) {
                const json = await res.json();
                if (json.timezone) {
                    this.state.resolvedTimezone = json.timezone;

                    return json.timezone;
                }
            }
        } catch {
            // Optional lookup failed.
        }
        this.state.resolvedTimezone = timezoneFromLng(lng);

        return this.state.resolvedTimezone;
    }

    async setLocation(lat, lng, label = null, timezone = null, moveMap = true) {
        this.state.lat = lat;
        this.state.lng = lng;
        this.state.label = label;
        this.state.timezone = timezone;
        if (this.latInput) {
            this.latInput.value = Number(lat).toFixed(6);
        }
        if (this.lngInput) {
            this.lngInput.value = Number(lng).toFixed(6);
        }
        if (this.cityInput && label) {
            this.cityInput.value = label;
        }
        if (moveMap) {
            this.updateMap(lat, lng);
        }
        if (this.useLocationTz?.checked !== false) {
            await this.resolveTimezone(lat, lng);
        }
        localStorage.setItem(
            'qf_last_location',
            JSON.stringify({ lat, lng, label, timezone: this.effectiveTimezone() }),
        );
        this.refresh();
    }

    formBody(extra = {}) {
        const body = new FormData();
        body.set('lat', this.state.lat);
        body.set('lng', this.state.lng);
        body.set('timezone', this.effectiveTimezone());
        body.set('method', this.method?.value || 'MWL');
        body.set('asr', this.asr?.value || 'Standard');
        body.set('high_latitude', this.highLatitude?.value || 'None');
        body.set('midnight_mode', this.midnightMode?.value || 'standard');
        Object.entries(extra).forEach(([key, value]) => body.set(key, value));

        return body;
    }

    async refresh() {
        if (this.state.lat == null) {
            return;
        }
        const res = await fetch(this.root.dataset.endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
            body: this.formBody(),
        });
        this.state.payload = await res.json();
        this.fetchedAt = Date.now();
        this.render();
    }

    async loadMonth() {
        if (this.state.lat == null) {
            return;
        }
        const res = await fetch(this.root.dataset.endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
            body: this.formBody({ month: '1' }),
        });
        const json = await res.json();
        const table = this.root.querySelector('[data-month-table]');
        if (! table || ! json.month) {
            return;
        }
        table.innerHTML = `
            <div class="overflow-x-auto mt-2">
                <table class="w-full text-sm">
                    <thead><tr class="text-forest/60">${['date', 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'].map((h) => `<th class="p-2 text-start">${this.labels[h] || h}</th>`).join('')}</tr></thead>
                    <tbody>
                        ${json.month
                            .map(
                                (row) => `<tr class="border-t border-forest/10">
                                <td class="p-2">${row.date}</td>
                                <td class="p-2">${format12(row.times.fajr)}</td>
                                <td class="p-2">${format12(row.times.sunrise)}</td>
                                <td class="p-2">${format12(row.times.dhuhr)}</td>
                                <td class="p-2">${format12(row.times.asr)}</td>
                                <td class="p-2">${format12(row.times.maghrib)}</td>
                                <td class="p-2">${format12(row.times.isha)}</td>
                            </tr>`,
                            )
                            .join('')}
                    </tbody>
                </table>
            </div>`;
    }

    methodLabel(key) {
        const option = this.method?.querySelector(`option[value="${key}"]`);

        return option?.textContent?.trim() || key;
    }

    render() {
        const payload = this.state.payload;
        if (! payload?.times || ! this.rows) {
            return;
        }
        const nextName = payload.next?.name;
        const tz = this.effectiveTimezone();
        this.rows.innerHTML = NAMES.map((name) => {
            const current = name === nextName;
            const ar = this.arabicNames[name] || '';

            return `<div class="prayer-row grid grid-cols-[1fr_auto_auto] items-center gap-3 px-4 py-3 ${current ? 'is-current' : ''}">
                <span class="font-medium">${this.labels[name] || name}</span>
                <span class="text-forest/45 text-sm text-end hidden sm:inline" dir="rtl">${ar}</span>
                <span class="tabular-nums text-base font-semibold text-end">${format12(payload.times[name])}</span>
            </div>`;
        }).join('');

        if (this.prayerDate) {
            this.prayerDate.textContent = formatDisplayDate(new Date(), tz);
        }
        if (this.hijri) {
            this.hijri.textContent = payload.hijri || '';
        }
        if (this.methodLine) {
            this.methodLine.textContent = `${this.methodPrefix} ${this.methodLabel(payload.method)}`;
        }
        if (this.timezoneLine) {
            const mode = this.useLocationTz?.checked === false ? this.deviceTzLabel : this.locationTzLabel;
            this.timezoneLine.textContent = `${this.timezonePrefix} ${mode} · ${payload.timezone || tz}`;
        }
        this.setMeta(
            this.state.label
                ? `${this.state.label} · ${Number(this.state.lat).toFixed(5)}, ${Number(this.state.lng).toFixed(5)}`
                : `${Number(this.state.lat).toFixed(5)}, ${Number(this.state.lng).toFixed(5)}`,
        );
        if (! this.mapReady && this.state.lat != null) {
            this.updateMap(this.state.lat, this.state.lng, 11);
        }
    }

    tick() {
        const tz = this.effectiveTimezone();
        if (this.clock) {
            this.clock.textContent = formatClock(new Date(), tz);
        }
        const payload = this.state.payload;
        if (this.next && payload?.next) {
            const name = this.labels[payload.next.name] || payload.next.name;
            const elapsed = Math.floor((Date.now() - this.fetchedAt) / 1000);
            const seconds = Math.max(0, (payload.next.in_seconds || 0) - elapsed);
            this.next.textContent = this.nextTemplate
                .replace(':name', name)
                .replace(':time', formatRemain(seconds, this.remainTemplate));
        }
    }

    setMeta(text) {
        if (this.meta) {
            this.meta.textContent = text;
        }
    }
}

const prayerRoot = document.querySelector('[data-prayer-app]');
if (prayerRoot) {
    window.prayerApp = new PrayerApp(prayerRoot);
}
