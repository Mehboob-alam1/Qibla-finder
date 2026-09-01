<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Services\QiblaService;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, QiblaService $qibla): View
    {
        $faqs = Faq::query()->published()->forLocale()->orderBy('sort_order')->limit(6)->get();
        $posts = Post::query()->published()->forLocale()->latest('published_at')->limit(3)->get();

        return view('pages.home', [
            'faqs' => $faqs,
            'posts' => $posts,
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
            return response()->json(['error' => 'Coordinates required'], 422);
        }

        return response()->json($qibla->snapshot($lat, $lng));
    }
}
