<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectsAndGone
{
    /**
     * Central 301 / 410 rules for removed public URLs (SEO cleanup).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = '/'.trim(strtolower($request->getPathInfo() ?: '/'), '/');
        if ($path === '/') {
            $path = '/';
        } else {
            $path = rtrim($path, '/') ?: '/';
        }

        foreach ($this->gonePatterns() as $pattern) {
            if ($this->matches($path, $pattern)) {
                return response(view('errors.gone'), 410);
            }
        }

        foreach ($this->redirects() as $from => $to) {
            if ($this->matches($path, $from)) {
                $target = $this->resolveTarget($path, $from, $to);

                return redirect()->to(url($target), 301);
            }
        }

        return $next($request);
    }

    /**
     * @return list<string>
     */
    protected function gonePatterns(): array
    {
        return [
            '/guides',
            '/guides/*',
            '/faq',
            '/faq/*',
            '/duas-qibla',
            '/duas-qibla/*',
            '/blog',
            '/blog/*',
            '/rss',
            '/rss/*',
            '/feed',
            '/feed/*',
            '/search',
            '/search/*',
            '/tag',
            '/tag/*',
            '/tags',
            '/tags/*',
            '/author',
            '/author/*',
            '/category',
            '/category/*',
            '/archive',
            '/archive/*',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function redirects(): array
    {
        return [
            '/prayer-times' => '/',
            '/jadwal-sholat' => '/kiblat-online',
            '/waktu-solat' => '/kiblat',
            '/prayer-times/*' => '/qibla/{slug}',
            '/about' => '/p/about',
            '/privacy' => '/p/privacy',
            '/terms' => '/p/terms',
        ];
    }

    protected function matches(string $path, string $pattern): bool
    {
        if (! str_contains($pattern, '*')) {
            return $path === $pattern;
        }

        $regex = '#^'.str_replace('\*', '(.+)', preg_quote($pattern, '#')).'$#';

        return (bool) preg_match($regex, $path);
    }

    protected function resolveTarget(string $path, string $pattern, string $to): string
    {
        if (! str_contains($pattern, '*') || ! str_contains($to, '{slug}')) {
            return $to;
        }

        $regex = '#^'.str_replace('\*', '(.+)', preg_quote($pattern, '#')).'$#';
        if (preg_match($regex, $path, $m) !== 1) {
            return '/';
        }

        $slug = trim($m[1], '/');

        return str_replace('{slug}', $slug, $to);
    }
}
