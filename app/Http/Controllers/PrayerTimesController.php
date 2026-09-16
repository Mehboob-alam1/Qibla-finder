<?php

namespace App\Http\Controllers;

use App\Services\PrayerTimeService;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrayerTimesController extends Controller
{
    public function index(): View
    {
        return view('pages.prayer-times', [
            'methods' => config('qibla.calculation_methods'),
            'cities' => config('qibla.cities'),
            'defaultMethod' => SiteSettings::get('default_calculation_method', 'MWL'),
        ]);
    }

    public function calculate(Request $request, PrayerTimeService $prayer)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'method' => ['nullable', 'string', 'max:32'],
            'asr' => ['nullable', 'in:Standard,Hanafi'],
            'high_latitude' => ['nullable', 'in:None,MiddleOfNight,OneSeventh'],
            'midnight_mode' => ['nullable', 'in:standard,jafari'],
            'month' => ['nullable', 'boolean'],
        ]);

        $method = $data['method'] ?? SiteSettings::get('default_calculation_method', 'MWL');
        $asr = $data['asr'] ?? 'Standard';
        $timezone = $data['timezone'] ?? null;
        $highLatitude = $data['high_latitude'] ?? 'None';
        $midnightMode = $data['midnight_mode'] ?? 'standard';

        if ($request->boolean('month')) {
            $now = now($timezone ?: 'UTC');

            return response()->json([
                'month' => $prayer->month(
                    (float) $data['lat'],
                    (float) $data['lng'],
                    (int) $now->year,
                    (int) $now->month,
                    $timezone ?: 'UTC',
                    $method,
                    $asr,
                    $highLatitude,
                    $midnightMode,
                ),
            ]);
        }

        return response()->json(
            $prayer->times(
                (float) $data['lat'],
                (float) $data['lng'],
                $timezone,
                $method,
                $asr,
                null,
                $highLatitude,
                $midnightMode,
            ),
        );
    }

    public function timezone(Request $request, PrayerTimeService $prayer): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'timezone' => $prayer->resolveTimezone((float) $data['lat'], (float) $data['lng']),
        ]);
    }
}
