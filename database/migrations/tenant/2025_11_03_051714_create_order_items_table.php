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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_packaging_id')->nullable()->constrained('product_packagings');
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('order_type')->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->foreignId('discount_id')->nullable()->constrained('discounts');
            $table->string('coupon_code')->nullable();
            $table->decimal('sub_total', 10, 2);
            $table->boolean('is_served')->default(false);
            $table->boolean('is_void')->default(false);
            $table->text('reason')->nullable();
            $table->json('selected_options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
