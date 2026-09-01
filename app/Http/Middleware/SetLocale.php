<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('qibla.locales', ['en' => []]));
        $locale = $request->session()->get('locale', $request->cookie('locale', config('app.locale', 'en')));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);
        \Illuminate\Support\Carbon::setLocale($locale);

        return $next($request);
    }
}
