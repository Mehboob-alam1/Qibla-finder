<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSettings
{
    public static function all(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return Cache::remember('site_settings', 60, function () {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function put(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            Setting::setValue($key, $value, $group);
        }

        Cache::forget('site_settings');
    }

    public static function name(): string
    {
        return (string) static::get('site_name', config('app.name', 'Qibla Finder'));
    }

    public static function tagline(): string
    {
        return (string) static::get('tagline', 'Face the Kaaba with certainty.');
    }
}
