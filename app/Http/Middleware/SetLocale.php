<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('qibla.locales', ['en' => []]));
        $fromQuery = $request->query('hl');
        $locale = in_array($fromQuery, $supported, true)
            ? $fromQuery
            : $request->session()->get('locale', $request->cookie('locale', config('app.locale', 'en')));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
