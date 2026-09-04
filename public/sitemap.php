<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../bootstrap/ensure-runtime.php';

if (! is_file(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    echo 'Laravel dependencies are missing. In hPanel SSH run: bash scripts/hostinger-setup.sh';
    exit;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::create('/sitemap.xml', 'GET'));
