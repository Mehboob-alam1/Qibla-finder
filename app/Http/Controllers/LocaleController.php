<?php

namespace App\Http\Controllers;

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

        $fallback = url()->previous();
        if (! $fallback || $fallback === $request->fullUrl() || str_contains($fallback, '/locale/')) {
            $fallback = route('home');
        }

        return redirect()->to($fallback)->withCookie(cookie()->forever('locale', $locale));
    }
}
