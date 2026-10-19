<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Cities;
use App\Support\LocalizedPaths;
use App\Support\PublicUrl;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Throwable;

class SitemapController extends Controller
{
    /**
     * Indexable URL budget only: home, cities hub, curated city pages, legal pages.
     */
    public function xml(): Response
    {
        PublicUrl::apply();

        $urls = [];

        $this->addLocalizedEntries($urls, '/', 'daily', '1.0', 'home', now());
        $this->addLocalizedEntries($urls, '/cities', 'weekly', '0.8', null, now());

        foreach (Cities::indexable() as $city) {
            $this->addLocalizedEntries($urls, '/qibla/'.$city['slug'], 'weekly', '0.7', null, now());
        }

        $this->addLocalizedEntries($urls, '/contact', 'monthly', '0.5', null, now());

        try {
            $legalSlugs = ['about', 'privacy', 'terms'];
            $pages = Page::query()
                ->published()
                ->whereIn('slug', $legalSlugs)
                ->orderBy('updated_at')
                ->get();

            $pagesBySlug = $pages->groupBy('slug');

            foreach ($pages as $page) {
                $alternates = $this->localeUrlsForRows($pagesBySlug->get($page->slug, collect()));
                $urls[] = $this->entry(
                    $page->publicUrl(),
                    $page->updated_at,
                    'monthly',
                    '0.5',
                    $alternates,
                );
            }
        } catch (Throwable) {
            //
        }

        $unique = $this->uniqueUrls($urls);

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($unique as $url) {
            $body .= '  <url>'."\n";
            $body .= '    <loc>'.$this->escape($url['loc']).'</loc>'."\n";
            if ($url['lastmod'] !== null) {
                $body .= '    <lastmod>'.$this->escape($url['lastmod']).'</lastmod>'."\n";
            }
            $body .= '    <changefreq>'.$this->escape($url['changefreq']).'</changefreq>'."\n";
            $body .= '    <priority>'.$this->escape($url['priority']).'</priority>'."\n";
            foreach ($url['alternates'] as $code => $href) {
                $body .= '    <xhtml:link rel="alternate" hreflang="'.$this->escape($code).'" href="'.$this->escape($href).'"/>'."\n";
            }
            $body .= '  </url>'."\n";
        }

        $body .= '</urlset>'."\n";

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        PublicUrl::apply();

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /qibla.json',
            'Disallow: /places/search',
            'Disallow: /prayer-times/calculate',
            'Disallow: /prayer-times/timezone',
            'Disallow: /*?*utm_',
            'Disallow: /*?page=',
            'Disallow: /*?q=',
            'Disallow: /*?lang=',
            'Disallow: /*?*lang=',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $urls
     */
    protected function addLocalizedEntries(
        array &$urls,
        string $path,
        string $changefreq,
        string $priority,
        ?string $localizedPage = null,
        mixed $lastmod = null,
    ): void {
        $path = LocalizedPaths::normalize($path);
        $alternates = LocalizedPaths::alternates($path);

        foreach (array_keys(config('qibla.locales', [])) as $locale) {
            $loc = $localizedPage !== null
                ? LocalizedPaths::url($localizedPage, $locale)
                : LocalizedPaths::queryUrl($path, $locale);

            $urls[] = $this->entry($loc, $lastmod ?? now(), $changefreq, $priority, $alternates);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $urls
     * @return array<int, array<string, mixed>>
     */
    protected function uniqueUrls(array $urls): array
    {
        $seen = [];
        $unique = [];

        foreach ($urls as $url) {
            if (isset($seen[$url['loc']])) {
                continue;
            }
            $seen[$url['loc']] = true;
            $unique[] = $url;
        }

        return $unique;
    }

    /**
     * @param  iterable<int, Page>  $rows
     * @return array<string, string>
     */
    protected function localeUrlsForRows(iterable $rows): array
    {
        $urls = [];

        foreach ($rows as $row) {
            $urls[$row->locale] = $row->publicUrl();
        }

        return $urls;
    }

    /**
     * @param  array<string, string>  $alternates
     * @return array{loc: string, lastmod: string|null, changefreq: string, priority: string, alternates: array<string, string>}
     */
    protected function entry(string $loc, mixed $lastmod, string $changefreq, string $priority, array $alternates = []): array
    {
        $stamp = null;
        if ($lastmod instanceof CarbonInterface) {
            $stamp = $lastmod->toAtomString();
        }

        return [
            'loc' => $loc,
            'lastmod' => $stamp,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'alternates' => $alternates,
        ];
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
