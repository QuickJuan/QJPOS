<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
            $table->dropUnique(['slug']);
            $table->unique('slug', 'pages_slug_unique_not_null')->whereNotNull('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('pages_slug_unique_not_null');
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }
};
