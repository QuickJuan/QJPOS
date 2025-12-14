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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete(); // Currency used for payment (mainly for cash)
            $table->decimal('amount', 10, 2); // Amount in order currency (default currency)
            $table->decimal('amount_in_payment_currency', 10, 2)->nullable(); // Amount in the currency used for payment
            $table->decimal('exchange_rate', 10, 4)->default(1.0000); // Exchange rate at time of payment
            $table->decimal('change_amount', 10, 2)->default(0); // Change given (mainly for cash)
            $table->string('reference_number')->nullable(); // For card, e-wallet transactions
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
