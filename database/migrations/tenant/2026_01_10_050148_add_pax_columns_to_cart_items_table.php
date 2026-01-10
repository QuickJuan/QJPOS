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
            $table->integer('pax_count')->nullable()->after('discount_amount')->comment('Number of people sharing this item');
            $table->integer('discounted_pax')->nullable()->after('pax_count')->comment('Number of people entitled to discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['pax_count', 'discounted_pax']);
        });
    }
};
