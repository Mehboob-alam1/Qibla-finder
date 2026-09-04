<?php

/**
 * Hostinger Git deploys code without .env, APP_KEY, or migrations.
 * Prepare a bootable runtime before Laravel handles the request.
 */
$root = dirname(__DIR__);

$directories = [
    $root.'/database',
    $root.'/bootstrap/cache',
    $root.'/storage/app/public',
    $root.'/storage/framework/cache/data',
    $root.'/storage/framework/sessions',
    $root.'/storage/framework/views',
    $root.'/storage/logs',
];

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0775, true);
    }
}

$envPath = $root.'/.env';
$createdEnv = false;

if (! is_file($envPath)) {
    $template = is_file($root.'/.env.hostinger.example')
        ? $root.'/.env.hostinger.example'
        : $root.'/.env.example';

    if (is_file($template) && @copy($template, $envPath)) {
        $createdEnv = true;
    }
}

if (! is_file($envPath) || ! is_writable($envPath)) {
    return;
}

$contents = (string) file_get_contents($envPath);
$original = $contents;

$set = static function (string $contents, string $key, string $value): string {
    if (preg_match('/^'.preg_quote($key, '/').'=/m', $contents) === 1) {
        return (string) preg_replace('/^'.preg_quote($key, '/').'=.*/m', $key.'='.$value, $contents, 1);
    }

    return rtrim($contents)."\n{$key}={$value}\n";
};

preg_match('/^APP_KEY=(.*)$/m', $contents, $keyMatch);
$needsKey = ! isset($keyMatch[1]) || trim($keyMatch[1]) === '';

if ($needsKey) {
    $contents = $set($contents, 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
}

$notInstalled = ! is_file($root.'/storage/framework/installed');

if ($createdEnv || $needsKey || $notInstalled) {
    $contents = $set($contents, 'SESSION_DRIVER', 'file');
    $contents = $set($contents, 'CACHE_STORE', 'file');
    $contents = $set($contents, 'QUEUE_CONNECTION', 'sync');

    preg_match('/^DB_DATABASE=(.*)$/m', $contents, $databaseMatch);
    $databaseName = trim($databaseMatch[1] ?? '', " \t\"'");
    $mysqlNotConfigured = in_array($databaseName, ['', 'your_database_name', 'laravel'], true);

    if ($mysqlNotConfigured) {
        $sqlite = $root.'/database/database.sqlite';
        if (! is_file($sqlite)) {
            @touch($sqlite);
        }
        $contents = $set($contents, 'DB_CONNECTION', 'sqlite');
        $contents = $set($contents, 'DB_DATABASE', $sqlite);
    }
}

$host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
$host = preg_replace('/:\d+$/', '', $host) ?? '';
$placeholderUrl = str_contains($contents, 'YOUR-DOMAIN.com')
    || preg_match('/^APP_URL=\s*$/m', $contents) === 1
    || preg_match('/^APP_URL=https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?\/?\s*$/m', $contents) === 1;

if ($host !== '' && ! in_array($host, ['localhost', '127.0.0.1'], true) && $placeholderUrl) {
    $contents = $set($contents, 'APP_URL', 'https://'.$host);
}

if ($contents !== $original) {
    @file_put_contents($envPath, $contents, LOCK_EX);
}
