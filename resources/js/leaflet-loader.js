let leafletPromise;

export function loadLeaflet() {
    if (typeof window !== 'undefined' && window.L) {
        return Promise.resolve(window.L);
    }

    if (! leafletPromise) {
        leafletPromise = new Promise((resolve, reject) => {
            if (! document.querySelector('link[data-leaflet-css]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                link.setAttribute('data-leaflet-css', '1');
                document.head.appendChild(link);
            }

            const existing = document.querySelector('script[data-leaflet-js]');
            if (existing) {
                existing.addEventListener('load', () => resolve(window.L));
                existing.addEventListener('error', reject);

                return;
            }

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.defer = true;
            script.setAttribute('data-leaflet-js', '1');
            script.onload = () => resolve(window.L);
            script.onerror = reject;
            document.body.appendChild(script);
        });
    }

    return leafletPromise;
}
