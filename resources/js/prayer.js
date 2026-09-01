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

class PrayerApp {
    constructor(root) {
        this.root = root;
        this.clock = root.querySelector('[data-clock]');
        this.next = root.querySelector('[data-next]');
        this.rows = root.querySelector('[data-rows]');
        this.meta = root.querySelector('[data-meta]');
        this.hijri = root.querySelector('[data-hijri]');
        this.method = root.querySelector('[data-method]');
        this.asr = root.querySelector('[data-asr]');
        this.cityInput = root.querySelector('[data-city-search]');
        this.cityList = root.querySelector('[data-city-results]');
        this.cities = JSON.parse(root.dataset.cities || '[]');
        this.labels = JSON.parse(root.dataset.labels || '{}');
        this.nextTemplate = root.dataset.nextTemplate || 'Next prayer: :name in :time';
        this.remainTemplate = root.dataset.remainTemplate || ':h h :m m :s s';
        this.i18n = JSON.parse(root.dataset.i18n || '{}');
        this.placesUrl = root.dataset.placesUrl || '/places/search';
        this.csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        this.state = { lat: null, lng: null, label: null, payload: null, timezone: null };
        this.fetchedAt = Date.now();
        this.bind();
        this.tick();
        setInterval(() => this.tick(), 1000);
        this.restore();
        this.locate();
    }

    bind() {
        this.root.querySelector('[data-locate]')?.addEventListener('click', () => this.locate());
        this.method?.addEventListener('change', () => this.refresh());
        this.asr?.addEventListener('change', () => this.refresh());
        this.root.querySelector('[data-month]')?.addEventListener('click', () => this.loadMonth());
        bindPlaceSearch({
            input: this.cityInput,
            list: this.cityList,
            cities: this.cities,
            endpoint: this.placesUrl,
            searchingLabel: this.i18n.searching_places || 'Searching…',
            emptyLabel: this.i18n.no_places || 'No places found.',
            onPick: (place) => {
                this.setLocation(place.lat, place.lng, place.label, place.timezone || timezoneFromLng(place.lng));
            },
        });
    }

    restore() {
        const saved = localStorage.getItem('qf_last_location');
        if (saved) {
            const parsed = JSON.parse(saved);
            this.setLocation(parsed.lat, parsed.lng, parsed.label, parsed.timezone || null);
        }
    }

    async locate() {
        if (!navigator.geolocation) {
            this.setMeta(this.i18n.geo_unavailable || 'Geolocation is not available on this device.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => this.setLocation(pos.coords.latitude, pos.coords.longitude, null, Intl.DateTimeFormat().resolvedOptions().timeZone),
            () => this.setMeta(this.i18n.location_denied || 'Location denied. Search a city instead.'),
            { enableHighAccuracy: true },
        );
    }

    setLocation(lat, lng, label = null, timezone = null) {
        this.state.lat = lat;
        this.state.lng = lng;
        this.state.label = label;
        this.state.timezone = timezone;
        localStorage.setItem('qf_last_location', JSON.stringify({ lat, lng, label, timezone }));
        this.refresh();
    }

    formBody(extra = {}) {
        const body = new FormData();
        body.set('lat', this.state.lat);
        body.set('lng', this.state.lng);
        if (this.state.timezone) {
            body.set('timezone', this.state.timezone);
        }
        body.set('method', this.method?.value || 'MWL');
        body.set('asr', this.asr?.value || 'Standard');
        Object.entries(extra).forEach(([key, value]) => body.set(key, value));
        return body;
    }

    async refresh() {
        if (this.state.lat == null) return;
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
        if (this.state.lat == null) return;
        const res = await fetch(this.root.dataset.endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
            body: this.formBody({ month: '1' }),
        });
        const json = await res.json();
        const table = this.root.querySelector('[data-month-table]');
        if (!table || !json.month) return;
        table.innerHTML = `
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-forest/60">${['date', 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'].map((h) => `<th class="p-2 text-start">${this.labels[h] || h}</th>`).join('')}</tr></thead>
                    <tbody>
                        ${json.month
                            .map(
                                (row) => `<tr class="border-t border-forest/10">
                                <td class="p-2">${row.date}</td>
                                <td class="p-2">${row.times.fajr}</td>
                                <td class="p-2">${row.times.sunrise}</td>
                                <td class="p-2">${row.times.dhuhr}</td>
                                <td class="p-2">${row.times.asr}</td>
                                <td class="p-2">${row.times.maghrib}</td>
                                <td class="p-2">${row.times.isha}</td>
                            </tr>`,
                            )
                            .join('')}
                    </tbody>
                </table>
            </div>`;
    }

    render() {
        const payload = this.state.payload;
        if (!payload?.times || !this.rows) return;
        const nextName = payload.next?.name;
        this.rows.innerHTML = NAMES.map((name) => {
            const current = name === nextName;
            return `<div class="prayer-row flex items-center justify-between px-5 py-4 ${current ? 'is-current rounded-2xl' : ''}">
                <span class="font-medium">${this.labels[name] || name}</span>
                <span class="tabular-nums text-lg">${payload.times[name]}</span>
            </div>`;
        }).join('');
        if (this.hijri) this.hijri.textContent = payload.hijri || '';
        this.setMeta(
            this.state.label
                ? `${this.state.label} · ${payload.timezone}`
                : `${this.state.lat.toFixed(4)}, ${this.state.lng.toFixed(4)} · ${payload.timezone}`,
        );
    }

    tick() {
        if (this.clock) this.clock.textContent = formatClock(new Date(), this.state.timezone);
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
        if (this.meta) this.meta.textContent = text;
    }
}

const prayerRoot = document.querySelector('[data-prayer-app]');
if (prayerRoot) {
    window.prayerApp = new PrayerApp(prayerRoot);
}
