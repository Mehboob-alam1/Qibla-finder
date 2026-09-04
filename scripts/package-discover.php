<?php

/**
 * Hostinger Git runs composer install with no .env, and shared hosts often
 * disable passthru/exec. Never fail Composer; discovery can run again after setup.
 */
$root = dirname(__DIR__);
chdir($root);
@mkdir($root.'/bootstrap/cache', 0775, true);

if (version_compare(PHP_VERSION, '8.3.0', '<') || ! is_file($root.'/vendor/autoload.php') || ! is_file($root.'/.env')) {
    exit(0);
}

try {
    require $root.'/vendor/autoload.php';

    $app = require $root.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $kernel->call('package:discover');
} catch (Throwable $e) {
    fwrite(STDERR, 'package:discover skipped: '.$e->getMessage().PHP_EOL);
}

exit(0);
