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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('change_loaded_to_ewallet', 12, 2)->default(0)->after('amount_tendered');
            $table->boolean('is_change_loaded_to_ewallet')->default(false)->after('change_loaded_to_ewallet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['change_loaded_to_ewallet', 'is_change_loaded_to_ewallet']);
        });
    }
};
