<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PublicUrl
{
    public static function incomingRequest(string $path): Request
    {
        $incoming = Request::capture();
        $server = $incoming->server->all();
        $server['REQUEST_URI'] = $path;

        $host = $incoming->getHttpHost() ?: (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $https = ! self::isLocalHost($host)
            || $incoming->isSecure()
            || str_contains(strtolower((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '')), 'https')
            || (! empty($server['HTTPS']) && $server['HTTPS'] !== 'off');

        return Request::create(
            ($https ? 'https' : 'http').'://'.$host.$path,
            'GET',
            $incoming->query->all(),
            $incoming->cookies->all(),
            [],
            $server,
        );
    }

    public static function apply(?Request $request = null): void
    {
        $request ??= request();
        $root = self::root($request);

        if ($root === null) {
            return;
        }

        URL::forceRootUrl($root);
        URL::forceScheme((string) parse_url($root, PHP_URL_SCHEME) ?: 'https');
        config(['app.url' => $root]);
    }

    public static function root(Request $request): ?string
    {
        $requestHost = $request->getHost();
        if (! self::isLocalHost($requestHost)) {
            return 'https://'.$request->getHttpHost();
        }

        $configured = rtrim((string) config('app.url'), '/');
        $configuredHost = (string) parse_url($configured, PHP_URL_HOST);
        if (! self::isLocalHost($configuredHost) && ! app()->environment('local', 'testing')) {
            return 'https://'.$configuredHost;
        }

        return null;
    }

    public static function isLocalHost(string $host): bool
    {
        $host = strtolower(explode(':', $host)[0]);

        return $host === '' || in_array($host, ['localhost', '127.0.0.1'], true);
    }
}
