import './qibla';
import './prayer';
import './ads';

const menu = document.querySelector('[data-mobile-toggle]');
const panel = document.querySelector('[data-mobile-panel]');
menu?.addEventListener('click', () => panel?.classList.toggle('hidden'));

document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(window.location.href);
        button.textContent = button.dataset.copied || 'Copied';
        setTimeout(() => {
            button.textContent = button.dataset.label || 'Copy link';
        }, 1600);
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
