@extends('layouts.app')

@section('title', __('ui.city_qibla_title', ['city' => $city['name'], 'country' => $city['country']]))
@section('description', __('ui.city_qibla_desc', ['city' => $city['name'], 'country' => $city['country'], 'bearing' => number_format($snapshot['qibla_bearing'], 1), 'cardinal' => $snapshot['qibla_cardinal']]))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-14">
    <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ $city['country'] }}</p>
    <h1 class="font-display text-5xl md:text-6xl text-forest mt-2">{{ __('ui.city_qibla_title', ['city' => $city['name'], 'country' => $city['country']]) }}</h1>
    <p class="mt-4 max-w-2xl text-forest/70 text-lg">{{ __('ui.city_qibla_lead', ['city' => $city['name']]) }}</p>

    <div class="mt-8 grid md:grid-cols-3 gap-4">
        <article class="stat-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Qibla Direction') }}</p>
            <p class="font-display text-5xl mt-2">{{ number_format($snapshot['qibla_bearing'], 1) }}°</p>
            <p class="text-forest/60 mt-1">{{ $snapshot['qibla_cardinal'] }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Distance to Kaaba') }}</p>
            <p class="font-display text-4xl mt-2">{{ number_format($snapshot['distance_km'], 0) }} km</p>
            <p class="text-forest/60 mt-1">{{ number_format($snapshot['distance_mi'], 0) }} mi</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-forest/50">{{ __('ui.Your Location') }}</p>
            <p class="font-display text-2xl mt-2">{{ $city['name'] }}</p>
            <p class="text-forest/60 mt-1 font-mono text-sm">{{ number_format($city['lat'], 4) }}, {{ number_format($city['lng'], 4) }}</p>
        </article>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a class="bg-forest text-cream rounded-full px-5 py-2.5" href="{{ route('home') }}">{{ __('ui.Find Qibla Direction') }}</a>
        <a class="border border-forest/20 rounded-full px-5 py-2.5" href="{{ route('cities.prayer', $city['slug']) }}">{{ __('ui.city_prayer_cta', ['city' => $city['name']]) }}</a>
        @include('partials.share', [
            'shareUrl' => url()->current(),
            'shareTitle' => __('ui.city_qibla_title', ['city' => $city['name'], 'country' => $city['country']]),
            'shareText' => __('ui.city_share_text', ['city' => $city['name'], 'bearing' => number_format($snapshot['qibla_bearing'], 1), 'cardinal' => $snapshot['qibla_cardinal']]),
            'shareClass' => 'border border-forest/20 rounded-full px-5 py-2.5',
        ])
    </div>

    <div id="city-map" class="gold-border mt-10 h-[360px] w-full rounded-3xl overflow-hidden" data-city-map data-lat="{{ $city['lat'] }}" data-lng="{{ $city['lng'] }}" data-kaaba-lat="{{ $snapshot['kaaba']['latitude'] }}" data-kaaba-lng="{{ $snapshot['kaaba']['longitude'] }}"></div>

    <div class="mt-10 max-w-3xl space-y-4 text-forest/75 leading-relaxed">
        <p>{{ __('ui.city_qibla_body', ['city' => $city['name'], 'country' => $city['country'], 'bearing' => number_format($snapshot['qibla_bearing'], 1), 'cardinal' => $snapshot['qibla_cardinal']]) }}</p>
        <p>{{ __('ui.city_qibla_note') }}</p>
    </div>

    @if ($related->isNotEmpty())
        <h2 class="mt-12 font-display text-3xl text-forest">{{ __('ui.city_more_in', ['country' => $city['country']]) }}</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($related as $item)
                <a class="rounded-full border border-forest/15 px-4 py-2 text-sm" href="{{ route('cities.qibla', $item['slug']) }}">{{ $item['name'] }}</a>
            @endforeach
        </div>
    @endif

    <p class="mt-10"><a class="text-gold" href="{{ route('cities.index') }}">{{ __('ui.cities_all') }}</a></p>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const el = document.querySelector('[data-city-map]');
        if (! el || typeof window.L === 'undefined') return;
        const lat = Number(el.dataset.lat);
        const lng = Number(el.dataset.lng);
        const kLat = Number(el.dataset.kaabaLat);
        const kLng = Number(el.dataset.kaabaLng);
        const map = window.L.map(el).setView([lat, lng], 3);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
        window.L.marker([lat, lng]).addTo(map);
        window.L.circleMarker([kLat, kLng], { radius: 8, color: '#c9a227', fillColor: '#c9a227', fillOpacity: 1 }).addTo(map);
        window.L.polyline([[lat, lng], [kLat, kLng]], { color: '#0d3b2e', weight: 2, dashArray: '6 8' }).addTo(map);
        map.fitBounds([[lat, lng], [kLat, kLng]], { padding: [40, 40] });
    })();
</script>
@endpush
