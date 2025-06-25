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
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade')->on('coupon_codes');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('order_number')->nullable(); // Track which order used this coupon
            $table->integer('discount_amount'); // Amount discounted in cents
            $table->string('session_id')->nullable(); // For guest users
            $table->string('ip_address')->nullable(); // Additional tracking
            $table->json('cart_data')->nullable(); // Store cart snapshot when coupon was used
            $table->timestamps();

            // Index for performance
            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
