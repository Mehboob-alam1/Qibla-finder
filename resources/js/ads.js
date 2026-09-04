function pushPendingAds() {
    if (typeof window.adsbygoogle === 'undefined') {
        window.adsbygoogle = [];
    }

    document.querySelectorAll('ins.adsbygoogle').forEach((unit) => {
        if (unit.getAttribute('data-adsbygoogle-status') || unit.closest('[hidden]')) {
            return;
        }

        try {
            window.adsbygoogle.push({});
        } catch {
            // Ad blockers or a missing slot should never break the compass.
        }
    });
}

function interstitialKey() {
    return 'qf_ad_interstitial_at';
}

function shouldShowInterstitial(root) {
    if (! root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return false;
    }

    const hours = Number(root.dataset.hours || 12);
    const last = Number(window.localStorage.getItem(interstitialKey()) || 0);

    return Date.now() - last >= hours * 60 * 60 * 1000;
}

function closeInterstitial(root) {
    root.hidden = true;
    root.classList.add('is-hidden');
    document.body.classList.remove('ad-interstitial-open');
}

function openInterstitial(root) {
    root.hidden = false;
    root.classList.remove('is-hidden');
    document.body.classList.add('ad-interstitial-open');
    window.localStorage.setItem(interstitialKey(), String(Date.now()));
    pushPendingAds();
}

const interstitial = document.querySelector('[data-ad-interstitial]');

if (interstitial && shouldShowInterstitial(interstitial)) {
    window.setTimeout(() => openInterstitial(interstitial), 2800);
}

interstitial?.querySelector('[data-ad-interstitial-close]')?.addEventListener('click', () => {
    closeInterstitial(interstitial);
});

interstitial?.addEventListener('click', (event) => {
    if (event.target === interstitial) {
        closeInterstitial(interstitial);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && interstitial && ! interstitial.hidden) {
        closeInterstitial(interstitial);
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', pushPendingAds);
} else {
    pushPendingAds();
}
