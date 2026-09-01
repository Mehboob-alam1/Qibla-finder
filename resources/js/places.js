export function timezoneFromLng(lng) {
    const offset = Math.round(Number(lng) / 15);

    if (! offset) {
        return 'UTC';
    }

    return `Etc/GMT${offset <= 0 ? '+' : '-'}${Math.abs(offset)}`;
}

export function bindPlaceSearch({ input, list, cities = [], endpoint, onPick, searchingLabel = 'Searching…', emptyLabel = 'No places found.' }) {
    if (! input || ! list) {
        return;
    }

    let timer = 0;
    let requestId = 0;

    const pick = (place) => {
        onPick(place);
        list.replaceChildren();
        input.value = place.label || `${place.name}${place.country ? `, ${place.country}` : ''}`;
    };

    const localMatches = (query) =>
        cities
            .filter((city) => `${city.name} ${city.country}`.toLowerCase().includes(query))
            .slice(0, 8)
            .map((city) => ({
                name: city.name,
                country: city.country,
                label: `${city.name}, ${city.country}`,
                lat: city.lat,
                lng: city.lng,
                timezone: city.timezone || '',
            }));

    const render = (places, message = '') => {
        list.replaceChildren();

        if (message) {
            const note = document.createElement('p');
            note.className = 'px-4 py-3 text-sm text-forest/60';
            note.textContent = message;
            list.append(note);
        }

        places.forEach((place) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-start px-4 py-2.5 hover:bg-sand rounded-xl';
            button.append(document.createTextNode(place.name));

            if (place.country) {
                const meta = document.createElement('span');
                meta.className = 'text-forest/50 text-sm';
                meta.textContent = ` · ${place.country}`;
                button.append(meta);
            }

            button.addEventListener('click', () => pick(place));
            list.append(button);
        });
    };

    const search = async (raw) => {
        const query = raw.trim();

        if (query.length < 2) {
            render(query ? localMatches(query.toLowerCase()) : []);
            return;
        }

        const local = localMatches(query.toLowerCase());
        render(local, local.length ? '' : searchingLabel);
        const current = ++requestId;

        try {
            const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });
            if (! response.ok || current !== requestId) {
                return;
            }
            const remote = await response.json();
            const merged = [...local, ...(Array.isArray(remote) ? remote : [])].filter(
                (place, index, all) =>
                    all.findIndex((item) => Math.abs(item.lat - place.lat) < 0.02 && Math.abs(item.lng - place.lng) < 0.02) === index,
            );
            render(merged.slice(0, 10), merged.length ? '' : emptyLabel);
        } catch {
            if (current === requestId && ! local.length) {
                render([], emptyLabel);
            }
        }
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => search(input.value), 280);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2) {
            search(input.value);
        }
    });
}
