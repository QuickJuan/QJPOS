<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('terms')->default(1); // number of deduction periods
            $table->decimal('amount_per_term', 12, 2)->default(0); // computed on approval
            $table->date('start_deduction_date')->nullable();      // first payroll period to deduct
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_advance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('term_number');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);         // scheduled deduction amount
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'partial', 'waived'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['cash_advance_id', 'term_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_schedules');
        Schema::dropIfExists('cash_advances');
    }
};
