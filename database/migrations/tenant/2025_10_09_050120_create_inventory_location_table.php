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
        Schema::create('inventory_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_location_id')
                ->constrained()
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
            $table->foreignId('inventory_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('current_stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_location');
    }
};
