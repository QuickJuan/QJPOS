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
            $table->enum('order_type', ['dine-in', 'takeout', 'delivery'])
                ->default('dine-in')
                ->nullable()
                ->after('amount');
            $table->boolean('is_served')->nullable()->default(false)->after('sub_total');
            $table->boolean('is_void')->nullable()->default(false)->after('is_served');
            $table->longText('reason')->nullable()->after('is_void');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'is_served', 'is_void', 'reason']);
        });
    }
};
