<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->is('admin*') && ! $request->ajax()) {
            try {
                PageView::query()->create([
                    'path' => '/'.ltrim($request->path(), '/'),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'referrer' => substr((string) $request->headers->get('referer'), 0, 255),
                ]);
            } catch (\Throwable) {
                // Analytics must never break the site.
            }
        }

        return $response;
    }
}
