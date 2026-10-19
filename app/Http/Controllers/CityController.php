<?php

namespace App\Http\Controllers;

use App\Services\PrayerTimeService;
use App\Services\QiblaService;
use App\Support\Cities;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
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

    public function qibla(string $slug, QiblaService $qibla, PrayerTimeService $prayer): View|RedirectResponse
    {
        $city = Cities::find($slug);
        if ($city === null) {
            abort(404);
        }

        if (! $city['is_indexable']) {
            $nearest = Cities::nearestIndexable($city['lat'], $city['lng']);

            return redirect()->to(
                $nearest ? route('cities.qibla', $nearest['slug']) : route('home'),
                301,
            );
        }

        $snapshot = $qibla->snapshot($city['lat'], $city['lng']);
        $times = $prayer->times($city['lat'], $city['lng']);
        $monthDate = new DateTimeImmutable('now', new DateTimeZone($times['timezone']));

        return view('pages.cities.qibla', [
            'city' => $city,
            'snapshot' => $snapshot,
            'times' => $times,
            'monthLabel' => Carbon::createFromImmutable($monthDate)->translatedFormat('F Y'),
            'related' => Cities::inCountry($city['country'], $city['slug']),
            'popular' => Cities::popular()->where('slug', '!=', $city['slug'])->take(8)->values(),
            'labels' => [
                'fajr' => __('ui.Fajr'),
                'dhuhr' => __('ui.Dhuhr'),
                'asr' => __('ui.Asr'),
                'maghrib' => __('ui.Maghrib'),
                'isha' => __('ui.Isha'),
            ],
        ]);
    }
}
