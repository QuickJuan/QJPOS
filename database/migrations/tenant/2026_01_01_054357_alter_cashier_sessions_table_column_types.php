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
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->decimal('beginning_cash', 15, 2)->default(0)->change();
            $table->decimal('closing_cash', 15, 2)->default(0)->change();
            $table->decimal('total_sales', 15, 2)->default(0)->change();
            $table->json('cash_denomination')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->integer('beginning_cash')->nullable()->change();
            $table->integer('closing_cash')->nullable()->change();
            $table->integer('total_sales')->nullable()->change();
            $table->integer('cash_denomination')->nullable()->change();
        });
    }
};
