@extends('layouts.app')

@section('title', __('ui.cities_index_title'))
@section('description', __('ui.cities_index_desc'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-14">
    <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ __('ui.cities_kicker') }}</p>
    <h1 class="font-display text-5xl md:text-6xl text-forest mt-2">{{ __('ui.cities_index_title') }}</h1>
    <p class="mt-4 max-w-2xl text-forest/70 text-lg">{{ __('ui.cities_index_lead') }}</p>

    <div class="mt-8 flex flex-wrap gap-2">
        @foreach ($popular as $city)
            <a class="rounded-full bg-forest text-cream px-4 py-2 text-sm" href="{{ route('cities.qibla', $city['slug']) }}">{{ $city['name'] }}</a>
        @endforeach
    </div>

    @foreach ($groups as $country => $cities)
        <h2 class="mt-12 font-display text-3xl text-gold">{{ $country }}</h2>
        <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($cities as $city)
                <article class="stat-card rounded-3xl p-5">
                    <h3 class="font-semibold text-forest">{{ $city['name'] }}</h3>
                    <p class="text-sm text-forest/50 mt-1">{{ $city['country'] }}</p>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <a class="text-gold" href="{{ route('cities.qibla', $city['slug']) }}">{{ __('ui.Find Qibla') }}</a>
                        <a class="text-forest/70" href="{{ route('cities.prayer', $city['slug']) }}">{{ __('ui.Prayer Times') }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endforeach
</section>
@endsection
