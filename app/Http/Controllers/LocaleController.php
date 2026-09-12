<?php

namespace App\Http\Controllers;

use App\Support\LocalizedPaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('qibla.locales', []));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        $request->session()->put('locale', $locale);

        $previous = url()->previous();
        if (! $previous || $previous === $request->fullUrl() || str_contains($previous, '/locale/')) {
            return redirect()
                ->to(LocalizedPaths::url('home', $locale))
                ->withCookie(cookie()->forever('locale', $locale));
        }

        return redirect()
            ->to(LocalizedPaths::swap($previous, $locale))
            ->withCookie(cookie()->forever('locale', $locale));
    }
}
