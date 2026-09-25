<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ImportSqliteToMysql extends Command
{
    protected $signature = 'site:import-sqlite
                            {--path= : Path to database.sqlite on the server}
                            {--dry-run : Show counts only, do not write to MySQL}';

    protected $description = 'Copy CMS data from SQLite (database/database.sqlite) into the current MySQL connection';

    /** @var list<string> */
    private array $tables = [
        'settings',
        'users',
        'pages',
        'posts',
        'faqs',
        'contact_messages',
        'page_views',
    ];

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Set DB_CONNECTION=mysql in .env before importing.');

            return self::FAILURE;
        }

        $path = $this->option('path') ?: database_path('database.sqlite');

        if (! is_file($path)) {
            $this->error("SQLite file not found: {$path}");
            $this->line('If content only lives on the live server, run this command over SSH on Hostinger.');

            return self::FAILURE;
        }

        Config::set('database.connections.sqlite_import', [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        try {
            DB::connection('sqlite_import')->getPdo();
        } catch (Throwable $e) {
            $this->error('Cannot open SQLite: '.$e->getMessage());

            return self::FAILURE;
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->error('Cannot connect to MySQL: '.$e->getMessage());

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('no-interaction') && ! $this->confirm('Copy data from SQLite into MySQL? Existing rows with the same primary key may be skipped.', true)) {
            return self::SUCCESS;
        }

        $this->info('MySQL migrations must be up to date. Running migrate…');
        if (! $dryRun) {
            $this->call('migrate', ['--force' => true]);
        }

        $total = 0;

        foreach ($this->tables as $table) {
            if (! Schema::connection('sqlite_import')->hasTable($table)) {
                $this->warn("Skip {$table} (not in SQLite)");

                continue;
            }

            if (! Schema::hasTable($table)) {
                $this->warn("Skip {$table} (not in MySQL — run migrate)");

                continue;
            }

            $rows = DB::connection('sqlite_import')->table($table)->get();

            if ($rows->isEmpty()) {
                $this->line("{$table}: 0 rows");

                continue;
            }

            $this->line("{$table}: {$rows->count()} rows");

            if ($dryRun) {
                $total += $rows->count();

                continue;
            }

            DB::connection()->transaction(function () use ($table, $rows, &$total) {
                foreach ($rows as $row) {
                    $data = (array) $row;

                    if ($table === 'settings') {
                        DB::connection()->table($table)->updateOrInsert(
                            ['key' => $data['key']],
                            $data,
                        );
                    } elseif (isset($data['id'])) {
                        DB::connection()->table($table)->updateOrInsert(
                            ['id' => $data['id']],
                            $data,
                        );
                    } else {
                        DB::connection()->table($table)->insert($data);
                    }

                    $total++;
                }
            });
        }

        if ($dryRun) {
            $this->info("Dry run: {$total} rows would be processed.");
        } else {
            $this->info("Import finished. Processed approximately {$total} row copies.");
            $this->line('Rename or remove database/database.sqlite after you verify the site.');
        }

        return self::SUCCESS;
    }
}
