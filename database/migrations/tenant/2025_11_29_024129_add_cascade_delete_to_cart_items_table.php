<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if constraints exist before adding them
        $constraintNames = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'cart_items' 
            AND TABLE_SCHEMA = DATABASE()
        ");
        
        $existingConstraints = collect($constraintNames)->pluck('CONSTRAINT_NAME')->toArray();
        
        Schema::table('cart_items', function (Blueprint $table) use ($existingConstraints) {
            if (!in_array('cart_items_cart_id_foreign', $existingConstraints)) {
                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            }
            
            if (!in_array('cart_items_product_id_foreign', $existingConstraints)) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            }
            
            if (!in_array('cart_items_product_packaging_id_foreign', $existingConstraints)) {
                $table->foreign('product_packaging_id')->references('id')->on('product_packagings')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'cart_items' 
                AND TABLE_SCHEMA = DATABASE()
            ");
            
            $existingConstraints = collect($constraints)->pluck('CONSTRAINT_NAME')->toArray();
            
            if (in_array('cart_items_cart_id_foreign', $existingConstraints)) {
                $table->dropForeign(['cart_id']);
            }
            
            if (in_array('cart_items_product_id_foreign', $existingConstraints)) {
                $table->dropForeign(['product_id']);
            }
            
            if (in_array('cart_items_product_packaging_id_foreign', $existingConstraints)) {
                $table->dropForeign(['product_packaging_id']);
            }
        });
    }
};
