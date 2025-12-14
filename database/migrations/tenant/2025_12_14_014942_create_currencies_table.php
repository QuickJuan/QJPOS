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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, PHP, EUR
            $table->string('name'); // US Dollar, Philippine Peso
            $table->string('symbol'); // $, ₱, €
            $table->decimal('exchange_rate', 10, 4)->default(1.0000); // Exchange rate relative to default currency
            $table->boolean('is_default')->default(false); // One currency should be default
            $table->boolean('is_active')->default(true); // Can be disabled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
