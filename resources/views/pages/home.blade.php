@extends('layouts.app')

@section('title', ($siteSettings['meta_title'] ?? 'Qibla Finder — Accurate Qibla Direction'))
@section('description', $siteSettings['meta_description'] ?? $siteTagline)

@section('content')
@php
    $qiblaI18n = [
        'requesting_location' => __('ui.requesting_location'),
        'geo_unavailable_city' => __('ui.geo_unavailable_city'),
        'location_denied_qibla' => __('ui.location_denied_qibla'),
        'location_locked' => __('ui.location_locked'),
        'motion_permission' => __('ui.motion_permission'),
        'true_north' => __('ui.True north'),
        'facing_qibla' => __('ui.Facing Qibla'),
        'qibla_locked' => __('ui.qibla_locked'),
        'you' => __('ui.You'),
        'kaaba' => __('ui.Kaaba'),
        'searching_places' => __('ui.searching_places'),
        'no_places' => __('ui.no_places'),
        'permission_title' => __('ui.permission_title'),
        'qibla_short' => __('ui.qibla_short'),
        'camera_unavailable' => __('ui.camera_unavailable'),
        'camera_denied' => __('ui.camera_denied'),
        'camera_hint' => __('ui.camera_hint'),
        'camera_hold' => __('ui.camera_hold'),
    ];
@endphp
<section class="pattern-bg text-cream">
    <div class="mx-auto max-w-6xl px-4 py-12 lg:py-16 grid lg:grid-cols-[1.05fr_0.95fr] gap-12 items-center">
        <div>
            <p class="text-gold tracking-[0.28em] uppercase text-xs">{{ __('ui.hero_kicker') }}</p>
            <h1 class="font-display text-5xl md:text-7xl leading-[0.95] mt-4">{{ __('ui.hero_title') }}</h1>
            <p class="mt-5 text-cream/75 text-lg max-w-xl">{{ __('ui.hero_lead') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <button class="bg-gold text-ink font-semibold px-6 py-3 rounded-full" type="button" onclick="document.querySelector('[data-qibla-app] [data-locate]')?.click()">{{ __('ui.Find Qibla Direction') }}</button>
                <button class="px-6 py-3 rounded-full border border-gold/40 text-gold lg:hidden" type="button" data-hero-camera>{{ __('ui.Camera') }}</button>
                <a href="{{ route('prayer-times') }}" class="px-6 py-3 rounded-full border border-gold/40 text-gold">{{ __('ui.Prayer Times') }}</a>
            </div>
        </div>

        <div data-qibla-app
             data-cities="{{ json_encode($cities, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
             data-i18n="{{ json_encode($qiblaI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
             data-places-url="{{ route('places.search') }}"
             data-default-vibration="{{ ($siteSettings['qibla_vibration'] ?? '1') === '1' ? '1' : '0' }}"
             data-default-audio="{{ ($siteSettings['qibla_audio'] ?? '0') === '1' ? '1' : '0' }}"
             data-default-mode="{{ \App\Support\QiblaDisplay::mode($siteSettings['qibla_display_mode'] ?? 'compass') }}"
             data-update-interval="{{ max(5, min(3600, (int) ($siteSettings['qibla_update_interval'] ?? 300))) }}"
             class="relative">
            <div class="absolute -top-3 inset-x-0 flex justify-center z-10">
                <span data-aligned class="hidden bg-gold text-ink text-sm font-semibold px-4 py-1.5 rounded-full shadow-lg">{{ __('ui.Facing Qibla') }}</span>
            </div>

            <div class="compass-shell">
                <svg class="compass-bezel" viewBox="0 0 400 400" aria-hidden="true">
                    <defs>
                        <radialGradient id="dial" cx="50%" cy="42%">
                            <stop offset="0%" stop-color="#245c45"/>
                            <stop offset="55%" stop-color="#0f3d30"/>
                            <stop offset="100%" stop-color="#06140f"/>
                        </radialGradient>
                        <radialGradient id="glass" cx="35%" cy="28%">
                            <stop offset="0%" stop-color="rgba(255,255,255,0.18)"/>
                            <stop offset="45%" stop-color="rgba(255,255,255,0)"/>
                        </radialGradient>
                    </defs>
                    <circle cx="200" cy="200" r="199" fill="#c9a227"/>
                    <circle cx="200" cy="200" r="191" fill="#5c4a14"/>
                    <circle cx="200" cy="200" r="186" fill="#ead78a"/>
                    <circle cx="200" cy="200" r="180" fill="url(#dial)"/>
                    <circle cx="200" cy="200" r="178" fill="none" stroke="rgba(232,212,139,0.35)" stroke-width="2"/>
                    <circle cx="200" cy="200" r="58" fill="#07140f" stroke="#c9a227" stroke-width="2"/>
                    <circle cx="200" cy="200" r="180" fill="url(#glass)"/>
                    <polygon points="200,6 214,34 186,34" fill="#e8d48b"/>
                    <polygon points="200,14 208,32 192,32" fill="#0d3b2e"/>
                </svg>

                <svg data-rose class="compass-rose" viewBox="0 0 400 400">
                    @for ($i = 0; $i < 360; $i++)
                        @php
                            $major = $i % 30 === 0;
                            $medium = $i % 10 === 0;
                            $len = $major ? 24 : ($medium ? 14 : 7);
                            $width = $major ? 2.4 : ($medium ? 1.4 : 0.7);
                            $color = $major ? '#e8d48b' : ($medium ? 'rgba(232,212,139,0.7)' : 'rgba(232,212,139,0.32)');
                        @endphp
                        <line x1="200" y1="22" x2="200" y2="{{ 22 + $len }}" stroke="{{ $color }}" stroke-width="{{ $width }}" transform="rotate({{ $i }} 200 200)"/>
                    @endfor
                    @foreach ([30 => '30', 60 => '60', 120 => '120', 150 => '150', 210 => '210', 240 => '240', 300 => '300', 330 => '330'] as $deg => $label)
                        <text x="200" y="64" text-anchor="middle" fill="#e8d48b" font-size="13" font-family="Plus Jakarta Sans" transform="rotate({{ $deg }} 200 200)">{{ $label }}</text>
                    @endforeach
                    <text x="200" y="72" text-anchor="middle" fill="#e8d48b" font-size="32" font-family="Cormorant Garamond">N</text>
                    <text x="328" y="210" text-anchor="middle" fill="#e8d48b" font-size="26" font-family="Cormorant Garamond">E</text>
                    <text x="200" y="344" text-anchor="middle" fill="#e8d48b" font-size="26" font-family="Cormorant Garamond">S</text>
                    <text x="72" y="210" text-anchor="middle" fill="#e8d48b" font-size="26" font-family="Cormorant Garamond">W</text>
                    <text x="286" y="118" text-anchor="middle" fill="rgba(232,212,139,0.8)" font-size="14">NE</text>
                    <text x="286" y="298" text-anchor="middle" fill="rgba(232,212,139,0.8)" font-size="14">SE</text>
                    <text x="114" y="298" text-anchor="middle" fill="rgba(232,212,139,0.8)" font-size="14">SW</text>
                    <text x="114" y="118" text-anchor="middle" fill="rgba(232,212,139,0.8)" font-size="14">NW</text>
                </svg>

                <svg data-needle-layer class="compass-needle" viewBox="0 0 400 400" aria-hidden="true">
                    <g data-needle>
                        <line x1="200" y1="200" x2="200" y2="78" stroke="#c9a227" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="200,46 211,78 189,78" fill="#c9a227"/>
                        <circle cx="200" cy="92" r="16" fill="#07140f" stroke="#e8d48b" stroke-width="2"/>
                        <text x="200" y="97" text-anchor="middle" fill="#e8d48b" font-size="15" font-family="Amiri">ك</text>
                    </g>
                </svg>

                <div class="compass-hub">
                    <p data-compass-place class="compass-hub-place">{{ __('ui.Your Location') }}</p>
                    <p data-compass-heading class="compass-hub-heading">—</p>
                    <p data-compass-qibla class="compass-hub-qibla">{{ __('ui.Qibla Direction') }}</p>
                </div>

                <div data-camera-view class="camera-view hidden">
                    <video data-camera-video playsinline webkit-playsinline muted autoplay></video>
                    <div class="camera-notch" aria-hidden="true"></div>
                    <p data-camera-turn="left" class="camera-turn camera-turn--left hidden">{{ __('ui.camera_turn_left') }}</p>
                    <p data-camera-turn="right" class="camera-turn camera-turn--right hidden">{{ __('ui.camera_turn_right') }}</p>
                    <div data-camera-kaaba class="camera-kaaba" aria-hidden="true">
                        <svg viewBox="0 0 120 140" role="img">
                            <title>{{ __('ui.Kaaba') }}</title>
                            <rect x="18" y="28" width="84" height="96" rx="3" fill="#121212"/>
                            <rect x="18" y="28" width="84" height="18" fill="#3a2a10"/>
                            <rect x="18" y="54" width="84" height="10" fill="#c9a227"/>
                            <rect x="48" y="78" width="16" height="28" fill="#1c1408" stroke="#e8d48b" stroke-width="1.4"/>
                            <path d="M18 28 L36 12 H100 L102 28 Z" fill="#2a2110"/>
                            <text x="60" y="50" text-anchor="middle" fill="#e8d48b" font-size="14" font-family="Amiri">ك</text>
                        </svg>
                    </div>
                    <div class="camera-readout">
                        <span data-camera-heading>—</span>
                        <span data-camera-qibla>{{ __('ui.qibla_short') }}</span>
                    </div>
                    <button data-camera-close type="button" class="camera-close">{{ __('ui.camera_close') }}</button>
                </div>

                <div data-permission-overlay class="compass-permission">
                    <p class="font-display text-2xl text-gold leading-tight">{{ __('ui.permission_title') }}</p>
                    <p class="mt-2 text-sm text-cream/75 max-w-[14rem]">{{ __('ui.permission_body') }}</p>
                    <button data-locate type="button" class="mt-4 bg-gold text-ink px-5 py-2.5 rounded-full font-semibold">{{ __('ui.Enable location') }}</button>
                </div>
            </div>

            <p data-status class="text-center text-cream/70 mt-5 text-sm">{{ __('ui.Turn toward the marker') }}</p>

            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <button data-locate type="button" class="bg-gold text-ink px-5 py-2.5 rounded-full font-semibold">{{ __('ui.Enable location') }}</button>
                <button data-camera-open type="button" class="bg-gold/15 text-gold border border-gold/40 px-5 py-2.5 rounded-full font-semibold">{{ __('ui.Camera') }}</button>
                <button data-calibrate type="button" class="border border-gold/40 text-gold px-5 py-2.5 rounded-full">{{ __('ui.Recalibrate') }}</button>
                <button data-settings-open type="button" class="border border-white/20 px-5 py-2.5 rounded-full">{{ __('ui.Settings') }}</button>
                @include('partials.share', ['shareUrl' => url('/'), 'shareAlign' => 'start-0'])
            </div>

            <div class="relative mt-4">
                <input data-city-search class="w-full rounded-full bg-white/10 border border-white/15 px-5 py-3 text-cream placeholder:text-cream/40" placeholder="{{ __('ui.Search any city') }}" autocomplete="off">
                <div data-city-results class="absolute inset-x-0 top-full mt-2 bg-cream text-ink rounded-2xl shadow-xl overflow-hidden z-20"></div>
            </div>

            <div data-settings-modal class="hidden fixed inset-0 z-50 bg-black/60 grid place-items-center p-4">
                <div class="theme-panel rounded-3xl p-6 w-full max-w-md space-y-4 shadow-xl">
                    <div class="flex justify-between items-center">
                        <h3 class="font-display text-3xl">{{ __('ui.Settings') }}</h3>
                        <button data-settings-close type="button" class="text-2xl">×</button>
                    </div>
                    <label class="flex items-center justify-between gap-4"><span>{{ __('ui.Vibration') }}</span><input data-toggle-vib type="checkbox" class="h-5 w-5 accent-gold"></label>
                    <label class="flex items-center justify-between gap-4"><span>{{ __('ui.Audio') }}</span><input data-toggle-audio type="checkbox" class="h-5 w-5 accent-gold"></label>
                    <label class="block text-sm">{{ __('ui.Update Interval') }}
                        <input data-interval-input type="number" min="5" max="3600" class="field mt-1 w-full rounded-2xl border px-4 py-2.5">
                    </label>
                    <label class="block text-sm">{{ __('ui.Display Mode') }}
                        <select data-mode-select class="field mt-1 w-full rounded-2xl border px-4 py-2.5 cursor-pointer">
                            <option value="compass">{{ __('ui.Compass') }}</option>
                            <option value="arrow">{{ __('ui.Arrow') }}</option>
                            <option value="camera">{{ __('ui.Camera') }}</option>
                        </select>
                    </label>
                    <button data-settings-close type="button" class="w-full bg-forest text-cream rounded-full py-3">{{ __('ui.Close') }}</button>
                </div>
            </div>

            <div data-calibrate-modal class="hidden fixed inset-0 z-50 bg-black/60 grid place-items-center p-4">
                <div class="theme-panel rounded-3xl p-6 w-full max-w-md shadow-xl">
                    <h3 class="font-display text-3xl mb-3">{{ __('ui.calibrate_title') }}</h3>
                    <ol class="list-decimal ps-5 space-y-2 text-forest/80">
                        <li>{{ __('ui.calibrate_1') }}</li>
                        <li>{{ __('ui.calibrate_2') }}</li>
                        <li>{{ __('ui.calibrate_3') }}</li>
                    </ol>
                    <button data-close-calibrate type="button" class="mt-6 w-full bg-forest text-cream rounded-full py-3">{{ __('ui.Done') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 -mt-8 relative z-10 grid md:grid-cols-3 gap-4">
    <article class="stat-card rounded-3xl p-6">
        <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Qibla Direction') }}</p>
        <p data-bearing class="font-display text-4xl mt-2">—</p>
        <p data-heading class="text-sm text-forest/50 mt-1">{{ __('ui.Device heading') }}</p>
    </article>
    <article class="stat-card rounded-3xl p-6">
        <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Your Location') }}</p>
        <p data-location class="font-display text-2xl mt-2 leading-snug">—</p>
    </article>
    <article class="stat-card rounded-3xl p-6">
        <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Distance to Kaaba') }}</p>
        <p data-distance class="font-display text-4xl mt-2">—</p>
    </article>
</section>

<section class="mx-auto max-w-6xl px-4 mt-10">
    <div id="qibla-map" class="gold-border"></div>
</section>

<section class="mx-auto max-w-6xl px-4 mt-20 grid lg:grid-cols-2 gap-12 items-start">
    <div>
        <p class="text-gold uppercase tracking-[0.2em] text-xs">{{ __('ui.How it works') }}</p>
        <h2 class="font-display text-5xl mt-2 text-forest">{{ __('ui.how_title') }}</h2>
        <div class="mt-6 space-y-4 text-forest/75 leading-relaxed">
            <p>{{ __('ui.how_p1') }}</p>
            <p>{{ __('ui.how_p2') }}</p>
        </div>
    </div>
    <div class="space-y-4">
        @foreach ([
            ['01', __('ui.step_1')],
            ['02', __('ui.step_2')],
            ['03', __('ui.step_3')],
        ] as [$n, $text])
            <div class="stat-card rounded-3xl p-5 flex gap-4">
                <span class="font-display text-3xl text-gold">{{ $n }}</span>
                <p class="text-forest/80">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

@include('partials.qibla-help')

@include('partials.ad', ['type' => 'banner'])

@if ($faqs->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 mt-20">
    <div class="flex items-end justify-between mb-6">
        <h2 class="font-display text-5xl text-forest">{{ __('ui.FAQ') }}</h2>
        <a href="{{ route('faq') }}" class="text-gold">{{ __('ui.View all') }}</a>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        @foreach ($faqs as $faq)
            <article class="stat-card rounded-3xl p-6">
                <h3 class="font-semibold text-forest">{{ $faq->question }}</h3>
                <div class="prose-content mt-2 text-forest/70 line-clamp-5">{!! $faq->answer !!}</div>
            </article>
        @endforeach
    </div>
</section>
@endif

@include('partials.ad', ['type' => 'native'])

@if ($posts->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 mt-20 mb-8">
    <div class="flex items-end justify-between mb-6">
        <h2 class="font-display text-5xl text-forest">{{ __('ui.Guides') }}</h2>
        <a href="{{ route('blog.index') }}" class="text-gold">{{ __('ui.All guides') }}</a>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
        @foreach ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="stat-card rounded-3xl p-6 block hover:-translate-y-1 transition">
                <p class="text-xs text-gold uppercase tracking-widest">{{ optional($post->published_at)->format('M j, Y') }}</p>
                <h3 class="font-display text-3xl mt-2 text-forest">{{ $post->title }}</h3>
                <p class="mt-3 text-forest/70">{{ $post->excerpt }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif
@endsection
