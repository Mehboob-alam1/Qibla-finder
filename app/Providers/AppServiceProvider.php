<?php

namespace App\Providers;

use App\Models\Page;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $locales = config('qibla.locales');
            $footerPages = collect();

            if (Schema::hasTable('pages')) {
                $footerPages = Page::query()->published()->forLocale()->orderBy('sort_order')->get(['title', 'slug']);
            }

            $view->with([
                'siteName' => SiteSettings::name(),
                'siteTagline' => SiteSettings::tagline(),
                'siteSettings' => SiteSettings::all(),
                'locales' => $locales,
                'currentLocale' => $locale,
                'documentDir' => $locales[$locale]['dir'] ?? 'ltr',
                'footerPages' => $footerPages,
            ]);
        });
    }
}
