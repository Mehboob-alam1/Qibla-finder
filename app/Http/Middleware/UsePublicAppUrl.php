<?php

namespace App\Http\Middleware;

use App\Support\PublicUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsePublicAppUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        PublicUrl::apply($request);

        return $next($request);
    }
}
