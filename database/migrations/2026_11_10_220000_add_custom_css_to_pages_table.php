<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages') || Schema::hasColumn('pages', 'custom_css')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->longText('custom_css')->nullable()->after('content');
            $table->string('body_class')->nullable()->after('custom_css');
            $table->text('body_style')->nullable()->after('body_class');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            if (Schema::hasColumn('pages', 'custom_css')) {
                $table->dropColumn('custom_css');
            }
            if (Schema::hasColumn('pages', 'body_class')) {
                $table->dropColumn('body_class');
            }
            if (Schema::hasColumn('pages', 'body_style')) {
                $table->dropColumn('body_style');
            }
        });
    }
};
