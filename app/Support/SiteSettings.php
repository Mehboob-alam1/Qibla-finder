<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteSettings
{
    public static function all(): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Cache::remember('site_settings', 60, function () {
                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (Throwable) {
            return [];
        }
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

    public static function adsensePreview(): bool
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return false;
        }

        try {
            $request = request();
        } catch (Throwable) {
            return false;
        }

        if ($request->has('ads_preview')) {
            return $request->boolean('ads_preview');
        }

        if ($request->cookie('qf_ads_preview') === '1') {
            return true;
        }

        return filter_var(static::get('adsense_preview', '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function adsenseVisible(): bool
    {
        return static::adsensePreview() || static::adsenseEnabled();
    }

    public static function adsenseEnabled(): bool
    {
        if (! filter_var(static::get('adsense_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return static::adsenseClient() !== '';
    }

    public static function adsenseClient(): string
    {
        $raw = trim((string) static::get('adsense_client', ''));

        return preg_match('/ca-pub-\d+/', $raw, $match) === 1 ? $match[0] : '';
    }

    public static function adsenseSlot(string $type): string
    {
        $banner = preg_replace('/\D+/', '', (string) static::get('adsense_banner_slot', '')) ?? '';
        $native = preg_replace('/\D+/', '', (string) static::get('adsense_native_slot', '')) ?? '';

        if ($type === 'native' && $native !== '') {
            return $native;
        }

        return $banner;
    }
}
