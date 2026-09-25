<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EnsureDatabase
{
    private static bool $running = false;

    public static function bootstrap(): void
    {
        if (self::$running || app()->runningInConsole() || app()->environment('testing')) {
            return;
        }

        self::$running = true;

        try {
            self::connectOrFallbackToSqlite();

            if (! Schema::hasTable('users') || (Schema::hasTable('pages') && (
                ! Schema::hasColumn('pages', 'url_style') || ! Schema::hasColumn('pages', 'show_in_header')
            ))) {
                Artisan::call('migrate', ['--force' => true]);
            }

            if (! User::query()->where('email', 'admin@qiblafinder.test')->exists()) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            if (Schema::hasTable('settings') && blank(SiteSettings::get('bing_site_verification'))) {
                SiteSettings::put(['bing_site_verification' => '167843586563944F086753F2A9641BFE']);
            }

            if (Schema::hasTable('settings')) {
                $socialDefaults = [];
                if (blank(SiteSettings::get('social_show_header'))) {
                    $socialDefaults['social_show_header'] = '1';
                }
                if (blank(SiteSettings::get('social_show_footer'))) {
                    $socialDefaults['social_show_footer'] = '1';
                }
                if ($socialDefaults !== []) {
                    SiteSettings::put($socialDefaults);
                }
            }

            @touch(storage_path('framework/installed'));
        } catch (Throwable $e) {
            report($e);
        } finally {
            self::$running = false;
        }
    }

    private static function connectOrFallbackToSqlite(): void
    {
        try {
            DB::connection()->getPdo();

            return;
        } catch (Throwable $e) {
            if (app()->environment('production')) {
                report($e);

                throw $e;
            }
        }

        $path = database_path('database.sqlite');

        if (! is_file($path)) {
            @touch($path);
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $path);

        DB::purge();
        DB::reconnect();
        DB::connection()->getPdo();
    }
}
