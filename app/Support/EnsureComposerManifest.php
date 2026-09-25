<?php

namespace App\Support;

class EnsureComposerManifest
{
    /**
     * Laravel reads composer.json at runtime; it is not in Git (Hostinger Composer build).
     */
    public static function apply(): void
    {
        $root = base_path();

        if (! is_file($root.'/composer.json') && is_file($root.'/composer.json.dist')) {
            @copy($root.'/composer.json.dist', $root.'/composer.json');
        }

        if (! is_file($root.'/composer.lock') && is_file($root.'/composer.lock.dist')) {
            @copy($root.'/composer.lock.dist', $root.'/composer.lock');
        }
    }
}
