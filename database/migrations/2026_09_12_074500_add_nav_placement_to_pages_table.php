<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_header')->default(false)->after('is_published');
            $table->boolean('show_in_footer')->default(true)->after('show_in_header');
        });

        DB::table('pages')->where('slug', 'duas-qibla')->update([
            'show_in_header' => true,
            'show_in_footer' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['show_in_header', 'show_in_footer']);
        });
    }
};
