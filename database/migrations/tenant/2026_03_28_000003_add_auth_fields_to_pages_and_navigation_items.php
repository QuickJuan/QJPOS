<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('requires_auth')->default(false)->after('hide_title');
        });

        Schema::table('navigation_items', function (Blueprint $table) {
            $table->boolean('auth_only')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('requires_auth');
        });

        Schema::table('navigation_items', function (Blueprint $table) {
            $table->dropColumn('auth_only');
        });
    }
};
