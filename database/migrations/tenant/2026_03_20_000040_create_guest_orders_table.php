<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('order_type', ['advance', 'delivery'])->default('advance');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('guest_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_order_items');
        Schema::dropIfExists('guest_orders');
    }
};
