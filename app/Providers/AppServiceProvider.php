<?php

namespace App\Providers;

use App\Models\Page;
use App\Support\EnsureDatabase;
use App\Support\PublicUrl;
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
        PublicUrl::apply();
        EnsureDatabase::bootstrap();

        if (! app()->runningInConsole() && request()->has('ads_preview')) {
            cookie()->queue(
                request()->boolean('ads_preview')
                    ? cookie('qf_ads_preview', '1', 120)
                    : cookie()->forget('qf_ads_preview'),
            );
        }

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $locales = config('qibla.locales');
            $footerPages = collect();

            try {
                if (Schema::hasTable('pages')) {
                    $columns = ['title', 'slug'];
                    if (Schema::hasColumn('pages', 'url_style')) {
                        $columns[] = 'url_style';
                    }

                    $footerPages = Page::query()->published()->forLocale()->orderBy('sort_order')->get($columns);
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
