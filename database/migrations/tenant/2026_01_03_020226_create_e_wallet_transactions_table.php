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
        Schema::create('e_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_e_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type'); // load, payment, refund, adjustment
            $table->string('source'); // change_loading, manual, payment, refund
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_e_wallet_id', 'created_at']);
            $table->index('transaction_type');
        });

        // Points transactions table for logging points usage
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_e_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type'); // earn, redeem, adjustment, expire
            $table->decimal('points', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_e_wallet_id', 'created_at']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
        Schema::dropIfExists('e_wallet_transactions');
    }
};
