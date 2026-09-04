<?php

if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    exit(0);
}

$artisan = dirname(__DIR__).'/artisan';

if (! is_file($artisan)) {
    exit(0);
}

passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg($artisan).' package:discover --ansi', $code);
exit($code);
