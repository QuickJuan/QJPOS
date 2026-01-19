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
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('block_type_id')->constrained('page_block_types')->restrictOnDelete();
            $table->integer('order');
            $table->json('settings')->nullable();
            $table->json('content')->nullable();
            $table->json('visibility_settings')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
