<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('url_prefix')->nullable()->after('slug');
            $table->string('page_type')->nullable()->after('url_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['url_prefix', 'page_type']);
        });
    }
};
