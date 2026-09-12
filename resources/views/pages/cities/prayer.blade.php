@extends('layouts.app')

@section('title', __('ui.city_prayer_title', ['city' => $city['name'], 'country' => $city['country']]))
@section('description', __('ui.city_prayer_desc', ['city' => $city['name'], 'country' => $city['country']]))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-14">
    <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ $city['country'] }}</p>
    <h1 class="font-display text-5xl md:text-6xl text-forest mt-2">{{ __('ui.city_prayer_title', ['city' => $city['name'], 'country' => $city['country']]) }}</h1>
    <p class="mt-4 text-forest/70 text-lg">{{ __('ui.city_prayer_lead', ['city' => $city['name']]) }}</p>
    @if (! empty($times['hijri']))
        <p class="mt-2 text-forest/50">{{ $times['hijri'] }} · {{ $times['date'] }}</p>
    @endif

    <div class="stat-card rounded-3xl overflow-hidden mt-8">
        @foreach ($labels as $key => $label)
            @continue(! isset($times['times'][$key]))
            <div class="flex justify-between px-6 py-3 border-t border-forest/10 first:border-t-0">
                <span class="text-forest/70">{{ $label }}</span>
                <span class="font-display text-2xl tabular-nums">{{ $times['times'][$key] }}</span>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-sm text-forest/50">{{ __('ui.city_prayer_method', ['method' => $times['method'], 'timezone' => $times['timezone']]) }}</p>

    <h2 class="mt-12 font-display text-3xl text-forest">{{ __('ui.Monthly timetable') }} · {{ $monthLabel }}</h2>
    <div class="mt-4 overflow-x-auto gold-border rounded-3xl">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-forest text-cream">
                <tr>
                    <th class="text-start font-medium px-4 py-3">{{ __('ui.Date') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Fajr') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Sunrise') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Dhuhr') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Asr') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Maghrib') }}</th>
                    <th class="text-start font-medium px-3 py-3">{{ __('ui.Isha') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($month as $row)
                    <tr class="border-t border-forest/10 {{ $row['date'] === $times['date'] ? 'bg-gold/15 font-semibold' : '' }}">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $row['date'] }}@if (! empty($row['hijri'])) <span class="block text-xs font-normal text-forest/50">{{ $row['hijri'] }}</span>@endif</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['fajr'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['sunrise'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['dhuhr'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['asr'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['maghrib'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['times']['isha'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a class="bg-forest text-cream rounded-full px-5 py-2.5" href="{{ route('prayer-times') }}">{{ __('ui.Monthly timetable') }}</a>
        <a class="border border-forest/20 rounded-full px-5 py-2.5" href="{{ route('cities.qibla', $city['slug']) }}">{{ __('ui.city_qibla_cta', ['city' => $city['name']]) }}</a>
        @include('partials.share', [
            'shareUrl' => url()->current(),
            'shareTitle' => __('ui.city_prayer_title', ['city' => $city['name'], 'country' => $city['country']]),
            'shareText' => __('ui.city_prayer_share', ['city' => $city['name']]),
            'shareClass' => 'border border-forest/20 rounded-full px-5 py-2.5',
        ])
    </div>

    <p class="mt-8 text-forest/70 leading-relaxed">{{ __('ui.city_prayer_note') }}</p>

    @if ($related->isNotEmpty())
        <h2 class="mt-12 font-display text-3xl text-forest">{{ __('ui.city_more_in', ['country' => $city['country']]) }}</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($related as $item)
                <a class="rounded-full border border-forest/15 px-4 py-2 text-sm" href="{{ route('cities.prayer', $item['slug']) }}">{{ $item['name'] }}</a>
            @endforeach
        </div>
    @endif

    <p class="mt-10"><a class="text-gold" href="{{ route('cities.index') }}">{{ __('ui.cities_all') }}</a></p>
</section>
@endsection
