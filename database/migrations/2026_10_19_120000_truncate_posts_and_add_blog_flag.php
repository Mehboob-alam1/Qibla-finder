<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            // Clear public blog content; keep table + model for dormant admin feature.
            \Illuminate\Support\Facades\DB::table('posts')->truncate();
        }

        if (Schema::hasTable('settings') && blank(Setting::getValue('blog_public_enabled'))) {
            Setting::setValue('blog_public_enabled', '0');
        }
    }

    public function down(): void
    {
        // Content truncation is not reversible.
    }
};
