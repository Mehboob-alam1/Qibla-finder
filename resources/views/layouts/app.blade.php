<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ $documentDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSettings['meta_title'] ?? $siteName)</title>
    <meta name="description" content="@yield('description', $siteSettings['meta_description'] ?? $siteTagline)">
    <meta name="theme-color" content="#0d3b2e">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="@yield('title', $siteSettings['meta_title'] ?? $siteName)">
    <meta property="og:description" content="@yield('description', $siteSettings['meta_description'] ?? $siteTagline)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', $siteSettings['meta_title'] ?? $siteName)">
    <meta name="twitter:description" content="@yield('description', $siteSettings['meta_description'] ?? $siteTagline)">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('qf_theme');
                var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.dataset.theme = theme;
                var meta = document.querySelector('meta[name="theme-color"]');
                if (meta) meta.setAttribute('content', theme === 'dark' ? '#08140f' : '#0d3b2e');
            } catch (e) {}
        })();
    </script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (!empty($siteSettings['ga_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings['ga_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', @json($siteSettings['ga_id']));
        </script>
    @endif
    @if (\App\Support\SiteSettings::adsenseEnabled() && ! \App\Support\SiteSettings::adsensePreview())
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ \App\Support\SiteSettings::adsenseClient() }}" crossorigin="anonymous"></script>
    @endif
    @if (\App\Support\SiteSettings::googleSiteVerification() !== '')
        <meta name="google-site-verification" content="{{ \App\Support\SiteSettings::googleSiteVerification() }}">
    @endif
    @if (\App\Support\SiteSettings::bingSiteVerification() !== '')
        <meta name="msvalidate.01" content="{{ \App\Support\SiteSettings::bingSiteVerification() }}">
    @endif
    {!! $siteSettings['head_html'] ?? '' !!}
</head>
<body class="min-h-screen antialiased bg-cream text-ink">
    @if (!empty($siteSettings['announcement']))
        <div class="bg-gold text-ink text-center text-sm py-2 px-4">{{ $siteSettings['announcement'] }}</div>
    @endif

    <header class="sticky top-0 z-40 bg-cream/90 backdrop-blur border-b border-forest/10">
        <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0">
                <span class="h-10 w-10 shrink-0 rounded-2xl bg-forest text-gold grid place-items-center font-display text-xl">ق</span>
                <span class="min-w-0">
                    <span class="block font-display text-2xl leading-none text-forest truncate">{{ $siteName }}</span>
                    <span class="hidden lg:block text-[11px] uppercase tracking-[0.18em] text-forest/50 truncate">{{ $siteTagline }}</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-forest">
                <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">{{ __('ui.Find Qibla') }}</a>
                <a class="nav-link {{ request()->routeIs('prayer-times') ? 'is-active' : '' }}" href="{{ route('prayer-times') }}">{{ __('ui.Prayer Times') }}</a>
                <a class="nav-link {{ request()->routeIs('blog.*') ? 'is-active' : '' }}" href="{{ route('blog.index') }}">{{ __('ui.Guides') }}</a>
                <a class="nav-link {{ request()->routeIs('faq') ? 'is-active' : '' }}" href="{{ route('faq') }}">{{ __('ui.FAQ') }}</a>
                @foreach ($headerPages as $page)
                    <a class="nav-link {{ $page->isCurrent() ? 'is-active' : '' }}" href="{{ $page->publicPath() }}">{{ $page->title }}</a>
                @endforeach
                <a class="nav-link {{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">{{ __('ui.Contact') }}</a>
            </nav>

            <div class="flex items-center gap-2">
                <div class="hidden md:block">
                    @include('partials.share', [
                        'shareUrl' => url('/'),
                        'shareClass' => 'h-10 px-3 rounded-full border border-forest/15 bg-card text-sm text-forest',
                    ])
                </div>
                <button type="button" data-theme-toggle class="h-10 w-10 rounded-full border border-forest/15 bg-card grid place-items-center text-forest" aria-label="{{ __('ui.Theme') }}" title="{{ __('ui.Theme') }}">
                    <span class="hidden dark:inline" aria-hidden="true">☀</span>
                    <span class="inline dark:hidden" aria-hidden="true">☾</span>
                </button>
                <details class="relative">
                    <summary class="list-none cursor-pointer h-10 px-3 rounded-full border border-forest/15 bg-card text-sm text-forest flex items-center gap-2">
                        <span>{{ $locales[$currentLocale]['native'] ?? 'EN' }}</span>
                    </summary>
                    <div class="absolute end-0 mt-2 w-64 max-h-80 overflow-y-auto rounded-2xl bg-card shadow-xl border border-forest/10 p-2 z-50">
                        <p class="px-3 py-2 text-[11px] uppercase tracking-widest text-forest/50">{{ __('ui.Language') }}</p>
                        @foreach ($locales as $code => $meta)
                            <a class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-sand text-sm {{ $code === $currentLocale ? 'text-gold font-semibold' : 'text-forest' }}" href="{{ route('locale', $code) }}">
                                <span>{{ $meta['native'] }}</span>
                                <span class="text-forest/40 text-xs uppercase">{{ $code }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
                <button data-mobile-toggle class="lg:hidden h-10 w-10 rounded-xl bg-forest text-cream" aria-label="{{ __('ui.Menu') }}">☰</button>
            </div>
        </div>
        <div data-mobile-panel class="hidden lg:hidden border-t border-forest/10 bg-cream px-4 py-4 space-y-3 text-forest">
            <a class="block" href="{{ route('home') }}">{{ __('ui.Find Qibla') }}</a>
            <a class="block" href="{{ route('prayer-times') }}">{{ __('ui.Prayer Times') }}</a>
            <a class="block" href="{{ route('blog.index') }}">{{ __('ui.Guides') }}</a>
            <a class="block" href="{{ route('faq') }}">{{ __('ui.FAQ') }}</a>
            @foreach ($headerPages as $page)
                <a class="block" href="{{ $page->publicPath() }}">{{ $page->title }}</a>
            @endforeach
            <a class="block" href="{{ route('contact') }}">{{ __('ui.Contact') }}</a>
            <div>
                @include('partials.share', [
                    'shareUrl' => url('/'),
                    'shareClass' => 'h-10 px-3 rounded-full border border-forest/15 bg-card text-sm text-forest',
                ])
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer mt-20 bg-ink text-cream">
        <div class="mx-auto max-w-6xl px-4 py-14 grid md:grid-cols-4 gap-10">
            <div class="md:col-span-2">
                <p class="font-display text-4xl text-gold">{{ $siteName }}</p>
                <p class="mt-3 max-w-md text-cream/70">{{ $siteSettings['footer_text'] ?? $siteTagline }}</p>
                <div class="mt-5">
                    @include('partials.share', [
                        'shareUrl' => url('/'),
                        'shareClass' => 'border border-gold/40 text-gold px-5 py-2.5 rounded-full',
                        'shareMenuClass' => 'start-0 bottom-full mb-2',
                    ])
                </div>
            </div>
            <div>
                <p class="text-xs uppercase tracking-widest text-gold mb-3">{{ __('ui.Explore') }}</p>
                <div class="space-y-2 text-cream/80">
                    <a class="block hover:text-gold" href="{{ route('home') }}">{{ __('ui.Find Qibla') }}</a>
                    <a class="block hover:text-gold" href="{{ route('prayer-times') }}">{{ __('ui.Prayer Times') }}</a>
                    <a class="block hover:text-gold" href="{{ route('blog.index') }}">{{ __('ui.Guides') }}</a>
                    <a class="block hover:text-gold" href="{{ route('faq') }}">{{ __('ui.FAQ') }}</a>
                    <a class="block hover:text-gold" href="{{ url('/#setup') }}">{{ __('ui.help_kicker') }}</a>
                </div>
            </div>
            <div>
                <p class="text-xs uppercase tracking-widest text-gold mb-3">{{ __('ui.Legal') }}</p>
                <div class="space-y-2 text-cream/80">
                    @foreach ($footerPages as $page)
                        <a class="block hover:text-gold" href="{{ $page->publicPath() }}">{{ $page->title }}</a>
                    @endforeach
                    <a class="block hover:text-gold" href="{{ route('contact') }}">{{ __('ui.Contact') }}</a>
                </div>
            </div>
        </div>
        <div class="border-t border-white/10 py-5 text-center text-sm text-cream/50">
            © {{ date('Y') }} {{ $siteName }}. {{ __('ui.copyright') }}
        </div>
    </footer>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if (\App\Support\SiteSettings::adsenseVisible() && (\App\Support\SiteSettings::adsensePreview() || \App\Support\SiteSettings::adsenseSlot('banner') !== '') && ! request()->routeIs('home'))
        <div class="ad-interstitial is-hidden" data-ad-interstitial data-hours="12" @if (\App\Support\SiteSettings::adsensePreview()) data-preview="1" @endif hidden>
            <div class="ad-interstitial__panel" role="dialog" aria-modal="true" aria-label="{{ __('ui.Advertisement') }}">
                <p class="ad-unit__label">{{ __('ui.Advertisement') }}</p>
                @include('partials.ad', ['type' => 'interstitial'])
                <button type="button" class="ad-interstitial__close" data-ad-interstitial-close>{{ __('ui.Continue to site') }}</button>
            </div>
        </div>
    @endif
    {!! $siteSettings['footer_html'] ?? '' !!}
</body>
</html>
