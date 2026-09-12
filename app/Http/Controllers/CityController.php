<?php

namespace App\Http\Controllers;

use App\Services\PrayerTimeService;
use App\Services\QiblaService;
use App\Support\Cities;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        return view('pages.cities.index', [
            'groups' => Cities::groupedByCountry(),
            'popular' => Cities::popular(),
        ]);
    }

    public function qibla(string $slug, QiblaService $qibla): View
    {
        $city = Cities::find($slug) ?? abort(404);
        $snapshot = $qibla->snapshot($city['lat'], $city['lng']);

        return view('pages.cities.qibla', [
            'city' => $city,
            'snapshot' => $snapshot,
            'related' => Cities::inCountry($city['country'], $city['slug']),
            'popular' => Cities::popular()->where('slug', '!=', $city['slug'])->take(8)->values(),
        ]);
    }

    public function prayer(string $slug, PrayerTimeService $prayer, QiblaService $qibla): View
    {
        $city = Cities::find($slug) ?? abort(404);
        $times = $prayer->times($city['lat'], $city['lng']);
        $monthDate = new DateTimeImmutable('now', new DateTimeZone($times['timezone']));
        $month = $prayer->month(
            $city['lat'],
            $city['lng'],
            (int) $monthDate->format('Y'),
            (int) $monthDate->format('n'),
            $times['timezone'],
        );

        return view('pages.cities.prayer', [
            'city' => $city,
            'times' => $times,
            'month' => $month,
            'monthLabel' => $monthDate->format('F Y'),
            'snapshot' => $qibla->snapshot($city['lat'], $city['lng']),
            'related' => Cities::inCountry($city['country'], $city['slug']),
            'labels' => [
                'imsak' => __('ui.Imsak'),
                'fajr' => __('ui.Fajr'),
                'sunrise' => __('ui.Sunrise'),
                'dhuhr' => __('ui.Dhuhr'),
                'asr' => __('ui.Asr'),
                'maghrib' => __('ui.Maghrib'),
                'isha' => __('ui.Isha'),
                'midnight' => __('ui.Midnight'),
            ],
        ]);
    }
}
