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
        Schema::create('customer_e_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_loaded', 12, 2)->default(0); // Total ever loaded
            $table->decimal('total_spent', 12, 2)->default(0); // Total ever spent
            $table->decimal('earned_points', 10, 2)->default(0); // Moved from customers table
            $table->decimal('redeemed_points', 10, 2)->default(0); // Moved from customers table
            $table->decimal('points_balance', 10, 2)->default(0); // Moved from customers table
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_e_wallets');
    }
};
