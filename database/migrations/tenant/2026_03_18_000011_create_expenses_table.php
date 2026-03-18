<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank', 'purchase'])->default('cash')
                ->comment('cash = paid in cash, bank = bank transfer, purchase = payable/installments');
            $table->string('payee')->nullable()->comment('Vendor or supplier name');
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
