<?php

namespace App\Http\Middleware;

use App\Support\LocalizedPaths;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $redirect = LocalizedPaths::incomingRedirect($request);
        if ($redirect !== null) {
            return redirect()->to($redirect, 301);
        }

        $supported = array_keys(config('qibla.locales', ['en' => []]));
        $fromPath = LocalizedPaths::localeForPath($request->path());
        $fromQuery = $request->query('hl');
        $locale = $fromPath
            ?: (in_array($fromQuery, $supported, true)
                ? $fromQuery
                : $request->session()->get('locale', $request->cookie('locale', config('app.locale', 'en'))));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        if ($fromPath) {
            $request->session()->put('locale', $fromPath);
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
