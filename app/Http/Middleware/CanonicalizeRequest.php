<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalizeRequest
{
    /**
     * Force lowercase paths, strip tracking/pagination query noise, drop trailing slashes.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($request->is(
            'admin',
            'admin/*',
            'up',
            'storage/*',
            'places/search',
            'qibla.json',
            'prayer-times/calculate',
            'prayer-times/timezone',
        )) {
            return $next($request);
        }

        $path = $request->getPathInfo() ?: '/';
        $canonicalPath = $this->canonicalPath($path);
        $query = $request->query();
        $keep = [];

        foreach ($query as $key => $value) {
            $lower = strtolower((string) $key);
            if (in_array($lower, ['hl', 'ads_preview'], true)) {
                $keep[$lower === 'hl' ? 'hl' : $key] = $value;
            }
        }

        $needsRedirect = $path !== $canonicalPath
            || array_keys($query) !== array_keys($keep)
            || $query != $keep;

        if ($needsRedirect) {
            $target = $canonicalPath === '/' ? url('/') : url($canonicalPath);
            if ($keep !== []) {
                $target .= '?'.http_build_query($keep);
            }

            return redirect()->to($target, 301);
        }

        return $next($request);
    }

    protected function canonicalPath(string $path): string
    {
        $path = '/'.trim($path, '/');
        if ($path === '/') {
            return '/';
        }

        $lower = strtolower($path);
        $trimmed = rtrim($lower, '/');

        return $trimmed === '' ? '/' : $trimmed;
    }
}
