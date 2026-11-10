<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages') || Schema::hasColumn('pages', 'show_page_title')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_page_title')->default(true)->after('show_in_footer');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'show_page_title')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('show_page_title');
        });
    }
};
