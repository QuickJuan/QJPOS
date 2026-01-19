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
            // Add SEO title separate from page title
            $table->string('seo_title')->nullable()->after('title');

            // Add featured video URL
            $table->string('featured_video')->nullable()->after('featured_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'featured_video']);
        });
    }
};
