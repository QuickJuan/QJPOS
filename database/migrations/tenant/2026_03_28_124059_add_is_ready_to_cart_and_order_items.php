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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->boolean('is_ready')->default(false)->after('is_served');
            $table->timestamp('ready_time')->nullable()->after('is_ready');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_ready')->default(false)->after('is_served');
            $table->timestamp('ready_time')->nullable()->after('is_ready');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['is_ready', 'ready_time']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_ready', 'ready_time']);
        });
    }
};
