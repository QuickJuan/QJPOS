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
            $table->unsignedBigInteger('refunded_cashier_id')->nullable()->after('status');
            $table->foreign('refunded_cashier_id')->references('id')->on('users')->onDelete('set null');
            $table->datetime('refunded_at')->nullable()->after('refunded_cashier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['refunded_cashier_id']);
            $table->dropColumn('refunded_cashier_id');
            $table->dropColumn('refunded_at');
        });
    }
};
