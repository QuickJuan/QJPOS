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
        Schema::create('page_seos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keywords')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_card')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('schema_type')->nullable();
            $table->json('schema_json')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();

            $table->unique('page_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_seos');
    }
};
