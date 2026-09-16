<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'locale']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique(['slug']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique(['slug']);
        });
    }
};
