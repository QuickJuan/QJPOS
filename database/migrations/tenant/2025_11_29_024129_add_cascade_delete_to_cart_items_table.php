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
            // Add foreign key constraint for cart_id with cascade delete
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');

            // Add foreign key constraint for product_id (if needed)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Add foreign key constraint for product_packaging_id (if needed)
            $table->foreign('product_packaging_id')->references('id')->on('product_packagings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the foreign key constraints
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_packaging_id']);
        });
    }
};
