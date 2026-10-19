<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        DB::table('pages')
            ->whereNotIn('slug', ['about', 'privacy', 'terms'])
            ->update([
                'is_published' => false,
                'show_in_header' => false,
                'show_in_footer' => false,
            ]);
    }

    public function down(): void
    {
        // Intentionally empty — content visibility decisions are not reversible.
    }
};
