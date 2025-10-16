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
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashier_id');
            $table->datetime('business_date');
            $table->datetime('started_time')->nullable();
            $table->datetime('closing_time')->nullable();
            $table->integer('beginning_cash')->nullable();
            $table->integer('closing_cash')->nullable();
            $table->integer('total_sales')->nullable();
            $table->unsignedBigInteger('check_by')->nullable();
            $table->json('cash_denomination')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_sessions');
    }
};
