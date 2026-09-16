@extends('layouts.app')

@section('title', __('ui.prayer_times_title'))
@section('description', __('ui.prayer_times_desc'))

@section('content')
@php
    $prayerI18n = [
        'geo_unavailable' => __('ui.geo_unavailable'),
        'location_denied' => __('ui.location_denied'),
        'searching_places' => __('ui.searching_places'),
        'no_places' => __('ui.no_places'),
    ];
    $prayerLabels = [
        'imsak' => __('ui.Imsak'),
        'fajr' => __('ui.Fajr'),
        'sunrise' => __('ui.Sunrise'),
        'dhuhr' => __('ui.Dhuhr'),
        'asr' => __('ui.Asr'),
        'maghrib' => __('ui.Maghrib'),
        'isha' => __('ui.Isha'),
        'midnight' => __('ui.Midnight'),
        'date' => __('ui.Date'),
    ];
    $arabicNames = __('ui.prayer_names_ar');
@endphp
<div data-prayer-app
     data-cities="{{ json_encode($cities, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-endpoint="{{ route('prayer-times.calculate') }}"
     data-timezone-endpoint="{{ route('prayer-times.timezone') }}"
     data-default-method="{{ $defaultMethod }}"
     data-next-template="{{ __('ui.next_prayer_in') }}"
     data-remain-template="{{ __('ui.remain_hms') }}"
     data-i18n="{{ json_encode($prayerI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-labels="{{ json_encode($prayerLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-arabic-names="{{ json_encode(is_array($arabicNames) ? $arabicNames : [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-places-url="{{ route('places.search') }}"
     data-method-prefix="{{ __('ui.method_prefix') }}"
     data-timezone-prefix="{{ __('ui.timezone_prefix') }}"
     data-device-tz-label="{{ __('ui.device_timezone') }}"
     data-location-tz-label="{{ __('ui.location_timezone') }}"
     class="mx-auto max-w-7xl px-4 py-10 lg:py-12">
    <div class="text-center lg:text-start max-w-3xl mx-auto lg:mx-0">
        <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ __('ui.Salah') }}</p>
        <h1 class="font-display text-5xl lg:text-6xl text-forest mt-2">{{ __('ui.prayer_times_title') }}</h1>
        <p class="mt-3 text-forest/70">{{ __('ui.prayer_times_lead') }}</p>
    </div>

    <div class="mt-8 grid lg:grid-cols-[minmax(300px,380px)_1fr] gap-6 items-start">
        <aside class="stat-card rounded-3xl p-5 lg:p-6 lg:sticky lg:top-24 space-y-4 order-2 lg:order-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-display text-3xl text-forest">{{ __('ui.Prayer Times') }}</p>
                    <p data-prayer-date class="text-sm text-forest/60 mt-1">—</p>
                </div>
                <button data-settings-open type="button" class="shrink-0 rounded-full border border-forest/15 p-2.5 text-forest/70 hover:border-gold hover:text-gold" aria-label="{{ __('ui.Settings') }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                </button>
            </div>
            <p data-method-line class="text-xs text-forest/55 leading-relaxed">—</p>
            <p data-timezone-line class="text-xs text-forest/55">—</p>
            <p data-clock class="text-center font-display text-4xl text-moss tabular-nums">00:00:00</p>
            <p data-next class="text-center text-sm text-forest/80">{{ __('ui.Next prayer') }}</p>
            <p data-hijri class="text-center text-xs text-forest/50"></p>
            <div data-rows class="divide-y divide-forest/8 border border-forest/8 rounded-2xl overflow-hidden mt-2"></div>
            <button data-month type="button" class="w-full border border-forest/15 rounded-full py-2.5 text-sm">{{ __('ui.Monthly timetable') }}</button>
            <div data-month-table class="text-sm"></div>
        </aside>

        <div class="space-y-4 order-1 lg:order-2">
            <div class="stat-card rounded-3xl p-5 space-y-4">
                <h2 class="font-display text-2xl text-forest">{{ __('ui.prayer_location_settings') }}</h2>
                <label class="block text-sm">{{ __('ui.location_address') }}
                    <div class="relative mt-1">
                        <input data-city-search class="w-full rounded-2xl border border-forest/15 px-4 py-2.5 bg-white" placeholder="{{ __('ui.Search any city') }}" autocomplete="off">
                        <div data-city-results class="absolute inset-x-0 top-full mt-2 bg-card rounded-2xl shadow-xl z-30 max-h-64 overflow-auto"></div>
                    </div>
                </label>
                <button data-locate type="button" class="w-full bg-forest text-cream rounded-full py-2.5 font-semibold text-sm">{{ __('ui.Enable Location Access') }}</button>
                <p class="text-xs text-forest/55">{{ __('ui.or_enter_coordinates') }}</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    <label class="text-sm">{{ __('ui.latitude') }}
                        <input data-lat-input type="number" step="any" class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2 bg-white tabular-nums">
                    </label>
                    <label class="text-sm">{{ __('ui.longitude') }}
                        <input data-lng-input type="number" step="any" class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2 bg-white tabular-nums">
                    </label>
                </div>
                <button data-apply-coords type="button" class="w-full border border-forest/20 rounded-full py-2.5 text-sm">{{ __('ui.apply_coordinates') }}</button>
                <p data-meta class="text-sm text-forest/60">{{ __('ui.enable_location_hint') }}</p>
                <p class="text-xs text-forest/50">{{ __('ui.drag_marker_hint') }}</p>
            </div>
            <div id="prayer-map" class="prayer-map gold-border rounded-3xl overflow-hidden" aria-label="{{ __('ui.Prayer Times') }}"></div>
        </div>
    </div>

    <div data-settings-modal class="hidden fixed inset-0 z-50 bg-black/50 grid place-items-center p-4">
        <div class="theme-panel rounded-3xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto space-y-4 shadow-xl">
            <div class="flex justify-between items-center gap-3">
                <h3 class="font-display text-3xl">{{ __('ui.prayer_calc_settings') }}</h3>
                <button data-settings-close type="button" class="text-2xl leading-none" aria-label="{{ __('ui.Close') }}">×</button>
            </div>
            <label class="block text-sm">{{ __('ui.Calculation method') }}
                <select data-method class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2.5 bg-white">
                    @foreach ($methods as $key => $method)
                        <option value="{{ $key }}" @selected($key === $defaultMethod)>{{ $method['name'] }}</option>
                    @endforeach
                </select>
                <span class="mt-1 block text-xs text-forest/55">{{ __('ui.calc_method_help') }}</span>
            </label>
            <label class="block text-sm">{{ __('ui.Asr method') }}
                <select data-asr class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2.5 bg-white">
                    <option value="Standard">{{ __('ui.Standard') }}</option>
                    <option value="Hanafi">{{ __('ui.Hanafi') }}</option>
                </select>
                <span class="mt-1 block text-xs text-forest/55">{{ __('ui.asr_school_help') }}</span>
            </label>
            <label class="block text-sm">{{ __('ui.high_latitude_adjustment') }}
                <select data-high-latitude class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2.5 bg-white">
                    <option value="None">{{ __('ui.high_lat_none') }}</option>
                    <option value="MiddleOfNight">{{ __('ui.high_lat_middle_night') }}</option>
                    <option value="OneSeventh">{{ __('ui.high_lat_one_seventh') }}</option>
                </select>
                <span class="mt-1 block text-xs text-forest/55">{{ __('ui.high_lat_help') }}</span>
            </label>
            <label class="block text-sm">{{ __('ui.midnight_calculation') }}
                <select data-midnight-mode class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2.5 bg-white">
                    <option value="standard">{{ __('ui.midnight_standard') }}</option>
                    <option value="jafari">{{ __('ui.midnight_jafari') }}</option>
                </select>
            </label>
            <label class="flex items-start gap-3 text-sm">
                <input data-use-location-tz type="checkbox" class="mt-1 h-4 w-4 accent-gold" checked>
                <span>{{ __('ui.use_location_timezone') }}<span class="block text-xs text-forest/55 mt-1 font-normal">{{ __('ui.use_location_timezone_help') }}</span></span>
            </label>
            <button data-settings-close type="button" class="w-full bg-forest text-cream rounded-full py-3">{{ __('ui.Close') }}</button>
        </div>
    </div>

    <article class="mt-16 prose-content max-w-3xl">
        <h2>{{ __('ui.prayer_explained_title') }}</h2>
        <p>{{ __('ui.prayer_explained_p1') }}</p>
        <p>{{ __('ui.prayer_explained_p2') }}</p>
    </article>
    @include('partials.ad', ['type' => 'banner'])
</div>
@endsection
