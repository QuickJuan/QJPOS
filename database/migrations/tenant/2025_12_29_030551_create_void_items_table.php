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
        Schema::create('void_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('product_type')->nullable();
            $table->string('description', 45)->nullable();
            $table->unsignedBigInteger('product_packaging_id')->nullable();
            $table->string('quantity');
            $table->string('price');
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vatable_sales', 15, 2)->default(0);
            $table->decimal('vat_exempt_sales', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('non_vat_sales', 15, 2)->default(0);
            $table->decimal('less_tax', 15, 2)->default(0);
            $table->string('amount');
            $table->enum('order_type', ['dine-in', 'takeout', 'delivery'])->nullable()->default('dine-in');
            $table->string('discount')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('sub_total')->nullable();
            $table->longText('void_reason')->nullable();
            $table->longText('notes')->nullable();
            $table->json('meta_data')->nullable();
            $table->unsignedBigInteger('served_by')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->integer('serving_number')->nullable();
            $table->timestamp('placed_order_time')->nullable();
            $table->timestamp('served_time')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('batch_number')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('served_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('voided_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('void_items');
    }
};
