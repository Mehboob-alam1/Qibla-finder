<?php

namespace App\Providers;

use App\Models\Page;
use App\Support\EnsureDatabase;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        EnsureDatabase::bootstrap();

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $locales = config('qibla.locales');
            $footerPages = collect();

            try {
                if (Schema::hasTable('pages')) {
                    $footerPages = Page::query()->published()->forLocale()->orderBy('sort_order')->get(['title', 'slug']);
                }
            } catch (Throwable) {
                $footerPages = collect();
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
