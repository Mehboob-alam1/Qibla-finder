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

        $urls = [
            $this->entry(LocalizedPaths::url('home', 'en'), now(), 'daily', '1.0', LocalizedPaths::alternates('/')),
            $this->entry(LocalizedPaths::url('home', 'id'), now(), 'daily', '0.95', LocalizedPaths::alternates('/kiblat-online')),
            $this->entry(LocalizedPaths::url('home', 'ms'), now(), 'daily', '0.95', LocalizedPaths::alternates('/kiblat')),
            $this->entry(LocalizedPaths::url('prayer-times', 'en'), now(), 'daily', '0.9', LocalizedPaths::alternates('/prayer-times')),
            $this->entry(LocalizedPaths::url('prayer-times', 'id'), now(), 'daily', '0.85', LocalizedPaths::alternates('/jadwal-sholat')),
            $this->entry(LocalizedPaths::url('prayer-times', 'ms'), now(), 'daily', '0.85', LocalizedPaths::alternates('/waktu-solat')),
            $this->entry(route('faq'), now(), 'weekly', '0.8'),
            $this->entry(route('blog.index'), now(), 'weekly', '0.8'),
            $this->entry(route('contact'), now(), 'monthly', '0.5'),
            $this->entry(route('cities.index'), now(), 'weekly', '0.8'),
        ];

        foreach (Cities::all() as $city) {
            $urls[] = $this->entry(route('cities.qibla', $city['slug']), now(), 'weekly', '0.7');
            $urls[] = $this->entry(route('cities.prayer', $city['slug']), now(), 'weekly', '0.7');
        }

        try {
            Page::query()->published()->orderBy('updated_at')->get()->unique('slug')->each(function (Page $page) use (&$urls): void {
                $urls[] = $this->entry($page->publicUrl(), $page->updated_at, 'monthly', '0.4');
            });

            Post::query()->published()->orderByDesc('published_at')->get()->unique('slug')->each(function (Post $post) use (&$urls): void {
                $urls[] = $this->entry(
                    route('blog.show', $post->slug),
                    $post->updated_at ?? $post->published_at,
                    'weekly',
                    '0.7',
                );
            });
        } catch (Throwable) {
            // CMS tables are optional until migrations have been run.
        }

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $url) {
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
