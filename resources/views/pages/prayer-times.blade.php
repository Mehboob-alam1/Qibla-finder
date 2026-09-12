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
@endphp
<div data-prayer-app
     data-cities="{{ json_encode($cities, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-endpoint="{{ route('prayer-times.calculate') }}"
     data-next-template="{{ __('ui.next_prayer_in') }}"
     data-remain-template="{{ __('ui.remain_hms') }}"
     data-i18n="{{ json_encode($prayerI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-labels="{{ json_encode($prayerLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) }}"
     data-places-url="{{ route('places.search') }}"
     class="mx-auto max-w-3xl px-4 py-12">
    <p class="text-gold uppercase tracking-[0.25em] text-xs text-center">{{ __('ui.Salah') }} · {{ now()->translatedFormat('l') }}</p>
    <h1 class="font-display text-6xl text-center text-forest mt-2">{{ __('ui.prayer_times_title') }}</h1>
    <p class="mt-4 text-center text-forest/70 max-w-2xl mx-auto">{{ __('ui.prayer_times_lead') }}</p>
    <p data-clock class="text-center font-display text-5xl text-moss mt-4 tabular-nums">00:00:00</p>
    <p data-next class="text-center text-forest mt-2">{{ __('ui.Next prayer') }}</p>
    <p data-hijri class="text-center text-forest/50 mt-1"></p>
    <p data-meta class="text-center text-sm text-forest/50 mt-1">{{ __('ui.enable_location_hint') }}</p>

    <div class="stat-card rounded-3xl p-6 mt-8 space-y-3">
        <p class="text-forest/70">{{ __('ui.pick_city_hint') }}</p>
        <button data-locate type="button" class="w-full bg-forest text-cream rounded-full py-3 font-semibold">{{ __('ui.Enable Location Access') }}</button>
        <div class="relative">
            <input data-city-search class="w-full rounded-full border border-forest/15 px-5 py-3 bg-white" placeholder="{{ __('ui.Search any city') }}" autocomplete="off">
            <div data-city-results class="absolute inset-x-0 top-full mt-2 bg-card rounded-2xl shadow-xl z-20"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-3">
            <label class="text-sm">{{ __('ui.Calculation method') }}
                <select data-method class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2 bg-white">
                    @foreach ($methods as $key => $method)
                        <option value="{{ $key }}" @selected($key === $defaultMethod)>{{ $method['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm">{{ __('ui.Asr method') }}
                <select data-asr class="mt-1 w-full rounded-2xl border border-forest/15 px-3 py-2 bg-white">
                    <option value="Standard">{{ __('ui.Standard') }}</option>
                    <option value="Hanafi">{{ __('ui.Hanafi') }}</option>
                </select>
            </label>
        </div>
    </div>

    <div data-rows class="stat-card rounded-3xl mt-6 overflow-hidden divide-y divide-forest/5"></div>

    <button data-month type="button" class="mt-6 w-full border border-forest/20 rounded-full py-3">{{ __('ui.Monthly timetable') }}</button>
    <div data-month-table class="stat-card rounded-3xl mt-4 p-4"></div>

    <article class="mt-16 prose-content">
        <h2>{{ __('ui.prayer_explained_title') }}</h2>
        <p>{{ __('ui.prayer_explained_p1') }}</p>
        <p>{{ __('ui.prayer_explained_p2') }}</p>
    </article>
    @include('partials.ad', ['type' => 'banner'])
</div>
@endsection
