<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Support\Cities;
use App\Support\LocalizedPaths;
use App\Support\PublicUrl;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Throwable;

class SitemapController extends Controller
{
    public function xml(): Response
    {
        PublicUrl::apply();

        $urls = [];

        foreach (LocalizedPaths::dedicated() as $page => $paths) {
            $priority = $page === 'home' ? '1.0' : '0.9';
            foreach ($paths as $path) {
                $urls[] = $this->entry(url($path), now(), 'daily', $priority, LocalizedPaths::alternates($path));
            }

            foreach (array_keys(config('qibla.locales', [])) as $locale) {
                if (isset($paths[$locale])) {
                    continue;
                }

                $urls[] = $this->entry(
                    LocalizedPaths::url($page, $locale),
                    now(),
                    'daily',
                    $page === 'home' ? '0.8' : '0.75',
                    LocalizedPaths::alternates($paths['en'] ?? '/'),
                );
            }
        }

        foreach ($this->sectionPaths() as $path => $meta) {
            foreach (array_keys(config('qibla.locales', [])) as $locale) {
                $urls[] = $this->entry(
                    LocalizedPaths::queryUrl($path, $locale),
                    now(),
                    $meta['changefreq'],
                    $meta['priority'],
                    LocalizedPaths::alternates($path),
                );
            }
        }

        foreach (Cities::all() as $city) {
            foreach ([
                route('cities.qibla', $city['slug']),
                route('cities.prayer', $city['slug']),
            ] as $loc) {
                $path = (string) parse_url($loc, PHP_URL_PATH);
                $urls[] = $this->entry($loc, now(), 'weekly', '0.7', LocalizedPaths::alternates($path));
            }
        }

        try {
            $pages = Page::query()->published()->orderBy('updated_at')->get();
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

            $posts = Post::query()->published()->orderByDesc('published_at')->get();
            $postsBySlug = $posts->groupBy('slug');

            foreach ($posts as $post) {
                $alternates = $this->localeUrlsForRows($postsBySlug->get($post->slug, collect()));
                $urls[] = $this->entry(
                    $post->publicUrl(),
                    $post->updated_at ?? $post->published_at,
                    'weekly',
                    '0.7',
                    $alternates,
                );
            }
        } catch (Throwable) {
            // CMS tables are optional until migrations have been run.
        }

        $seen = [];
        $unique = [];
        foreach ($urls as $url) {
            if (isset($seen[$url['loc']])) {
                continue;
            }
            $seen[$url['loc']] = true;
            $unique[] = $url;
        }

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
     * @param  iterable<int, Page|Post>  $rows
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
     * @return array<string, array{changefreq: string, priority: string}>
     */
    protected function sectionPaths(): array
    {
        return [
            '/faq' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/guides' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/contact' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            '/cities' => ['changefreq' => 'weekly', 'priority' => '0.8'],
        ];
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
