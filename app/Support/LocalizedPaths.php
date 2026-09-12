<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Uri;

class LocalizedPaths
{
    /**
     * Dedicated public paths for the two pages that carry the most search demand.
     *
     * @return array<string, array<string, string>>
     */
    public static function dedicated(): array
    {
        return [
            'home' => [
                'en' => '/',
                'id' => '/kiblat-online',
                'ms' => '/kiblat',
            ],
            'prayer-times' => [
                'en' => '/prayer-times',
                'id' => '/jadwal-sholat',
                'ms' => '/waktu-solat',
            ],
        ];
    }

    /**
     * Exact-phrase aliases that consolidate onto the primary localized URL.
     *
     * @return array<string, string>
     */
    public static function redirects(): array
    {
        return [
            '/find-the-qibla' => '/',
            '/qibla-finder-online' => '/',
            '/cek-kiblat' => '/kiblat-online',
            '/kiblat-arah-mana' => '/kiblat-online',
            '/kompas-arah-kiblat' => '/kiblat-online',
            '/arah-kiblat-sholat' => '/kiblat-online',
            '/cari-kiblat' => '/kiblat',
            '/kompas-kiblat' => '/kiblat',
        ];
    }

    /**
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        $slugs = [];

        foreach (static::dedicated() as $paths) {
            foreach ($paths as $path) {
                $slug = trim($path, '/');
                if ($slug !== '') {
                    $slugs[] = $slug;
                }
            }
        }

        foreach (array_keys(static::redirects()) as $path) {
            $slug = trim($path, '/');
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique($slugs));
    }

    public static function pageForPath(?string $path): ?string
    {
        $path = static::normalize($path ?? request()->path());

        foreach (static::dedicated() as $page => $paths) {
            if (in_array($path, $paths, true)) {
                return $page;
            }
        }

        if (isset(static::redirects()[$path])) {
            return static::pageForPath(static::redirects()[$path]);
        }

        return null;
    }

    public static function localeForPath(?string $path): ?string
    {
        $path = static::normalize($path ?? request()->path());

        foreach (static::dedicated() as $paths) {
            foreach ($paths as $locale => $localePath) {
                if ($localePath === $path && $locale !== 'en') {
                    return $locale;
                }
            }
        }

        return null;
    }

    public static function pathFor(string $page, string $locale): string
    {
        return static::dedicated()[$page][$locale] ?? static::dedicated()[$page]['en'] ?? '/';
    }

    public static function url(string $page, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $path = static::pathFor($page, $locale);

        if (isset(static::dedicated()[$page][$locale])) {
            return url($path);
        }

        return $path === '/'
            ? url('/').'?hl='.$locale
            : url($path).'?hl='.$locale;
    }

    public static function is(string $page): bool
    {
        return static::pageForPath(request()->path()) === $page;
    }

    /**
     * @return array<string, string>
     */
    public static function alternates(?string $path = null): array
    {
        $path = static::normalize($path ?? request()->path());
        $page = static::pageForPath($path);
        $urls = [];

        foreach (array_keys(config('qibla.locales', [])) as $locale) {
            $urls[$locale] = $page
                ? static::url($page, $locale)
                : static::queryUrl($path, $locale);
        }

        $urls['x-default'] = $page ? static::url($page, 'en') : url($path);

        return $urls;
    }

    public static function swap(string $url, string $locale): string
    {
        $uri = Uri::of($url);
        $page = static::pageForPath($uri->path() ?: '/');

        if ($page !== null) {
            return static::url($page, $locale);
        }

        $path = static::normalize($uri->path() ?: '/');

        return static::queryUrl($path, $locale);
    }

    public static function incomingRedirect(Request $request): ?string
    {
        $hl = $request->query('hl');

        if (! in_array($hl, ['id', 'ms'], true)) {
            return null;
        }

        $page = static::pageForPath($request->path());

        if (! in_array($page, ['home', 'prayer-times'], true)) {
            return null;
        }

        $target = static::pathFor($page, $hl);
        $current = static::normalize($request->path());

        if ($target === $current) {
            return null;
        }

        return url($target);
    }

    public static function queryUrl(string $path, string $locale): string
    {
        $path = static::normalize($path);
        $absolute = url($path);

        return $locale === 'en' ? $absolute : $absolute.'?hl='.$locale;
    }

    public static function normalize(?string $path): string
    {
        $path = '/'.trim((string) $path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
