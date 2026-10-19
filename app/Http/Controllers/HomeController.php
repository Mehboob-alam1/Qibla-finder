<?php

namespace App\Http\Controllers;

use App\Services\QiblaService;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, QiblaService $qibla): View
    {
        return view('pages.home', [
            'cities' => config('qibla.cities'),
            'kaaba' => config('qibla.kaaba'),
            'siteName' => SiteSettings::name(),
        ]);
    }

    public function qibla(Request $request, QiblaService $qibla)
    {
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');

        if ($lat === 0.0 && $lng === 0.0) {
            return response()->json(['error' => 'Coordinates required'], 422)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()->json($qibla->snapshot($lat, $lng))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
