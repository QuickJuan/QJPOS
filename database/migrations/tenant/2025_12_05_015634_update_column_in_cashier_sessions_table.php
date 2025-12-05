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
            $table->renameColumn('cash_denomination', 'cash_denomination_details');
            $table->integer('cash_denomination')->after('closing_cash')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->dropColumn('cash_denomination');
            $table->renameColumn('cash_denomination_details', 'cash_denomination');
        });
    }
};
