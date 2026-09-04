import './qibla';
import './prayer';
import './ads';

const menu = document.querySelector('[data-mobile-toggle]');
const panel = document.querySelector('[data-mobile-panel]');
menu?.addEventListener('click', () => panel?.classList.toggle('hidden'));

document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        const root = button.closest('[data-share]');
        const url = root?.dataset.shareUrl || window.location.href;
        await navigator.clipboard.writeText(url);
        button.textContent = button.dataset.copied || 'Copied';
        setTimeout(() => {
            button.textContent = button.dataset.label || 'Copy link';
        }, 1600);
    });
});

document.querySelectorAll('[data-share]').forEach((root) => {
    const open = root.querySelector('[data-share-open]');
    const menu = root.querySelector('[data-share-menu]');
    const native = root.querySelector('[data-share-native]');

    const payload = () => ({
        title: root.dataset.shareTitle || document.title,
        text: root.dataset.shareText || '',
        url: root.dataset.shareUrl || window.location.href,
    });

    const closeMenu = () => {
        if (! menu) {
            return;
        }
        menu.hidden = true;
        open?.setAttribute('aria-expanded', 'false');
    };

    const toggleMenu = () => {
        if (! menu) {
            return;
        }
        const next = menu.hidden;
        document.querySelectorAll('[data-share-menu]').forEach((other) => {
            other.hidden = true;
        });
        menu.hidden = ! next;
        open?.setAttribute('aria-expanded', next ? 'true' : 'false');
    };

    open?.addEventListener('click', async (event) => {
        event.stopPropagation();
        const data = payload();
        if (navigator.share) {
            try {
                await navigator.share(data);
                return;
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }
            }
        }
        toggleMenu();
    });

    native?.addEventListener('click', async () => {
        const data = payload();
        if (navigator.share) {
            try {
                await navigator.share(data);
                closeMenu();
                return;
            } catch (error) {
                if (error?.name === 'AbortError') {
                    closeMenu();
                    return;
                }
            }
        }
        try {
            await navigator.clipboard.writeText(data.url);
            native.textContent = native.dataset.copied || 'Link copied';
            setTimeout(() => {
                native.textContent = native.dataset.label || native.textContent;
            }, 1600);
        } catch {
            // Clipboard can fail without permission.
        }
    });

    document.addEventListener('click', (event) => {
        if (! root.contains(event.target)) {
            closeMenu();
        }
    });
});

function currentTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function applyTheme(theme) {
    const dark = theme === 'dark';
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('qf_theme', theme);
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', dark ? '#08140f' : '#0d3b2e');
    }
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', dark ? 'true' : 'false');
    });
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.setAttribute('aria-pressed', currentTheme() === 'dark' ? 'true' : 'false');
    button.addEventListener('click', () => {
        applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    });
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if (localStorage.getItem('qf_theme')) {
        return;
    }
    const dark = event.matches;
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', dark ? '#08140f' : '#0d3b2e');
    }
});
