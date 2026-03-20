<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            // Identification
            $table->string('employee_no')->nullable()->unique();  // company-assigned number
            $table->string('position')->nullable();               // Job title / designation
            $table->string('department')->nullable();

            // Employment classification
            $table->enum('employment_type', [
                'regular',
                'probationary',
                'casual',
                'contractual',
                'part_time',
                'seasonal',
            ])->default('regular');

            // Key dates
            $table->date('date_hired')->nullable();
            $table->date('date_regularized')->nullable();
            $table->date('date_separated')->nullable();

            // Compensation basis
            $table->decimal('basic_salary', 12, 2)->default(0);  // monthly rate
            $table->decimal('daily_rate', 12, 2)->default(0);    // stored for quick payroll reference
            $table->decimal('hourly_rate', 12, 2)->default(0);

            // Pay frequency
            $table->enum('pay_frequency', [
                'monthly',
                'semi_monthly',   // twice a month (most common PH)
                'weekly',
                'bi_weekly',
            ])->default('semi_monthly');

            // BIR / Tax
            // Civil status codes used in BIR withholding tax tables:
            // S  = Single/Separated  | S1/S2/S3 = Single + dependents
            // M  = Married           | M1/M2/M3  = Married + dependents
            // ME = Married, exempt   | ME1/ME2/ME3 = same + dependents
            $table->enum('tax_status', [
                'S', 'S1', 'S2', 'S3',
                'M', 'M1', 'M2', 'M3',
                'ME', 'ME1', 'ME2', 'ME3',
            ])->default('S');

            // Government numbers
            $table->string('sss_no', 20)->nullable();
            $table->string('philhealth_no', 20)->nullable();
            $table->string('pagibig_no', 20)->nullable();
            $table->string('tin_no', 20)->nullable();

            // Banking (for payroll disbursement)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();

            $table->enum('status', ['active', 'inactive', 'separated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Compensation assignments per employee (overrides or additional items)
        Schema::create('employee_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('compensation_type_id')->constrained()->cascadeOnDelete();

            // Employee-specific override; if null, the compensation_type default is used
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('rate', 8, 4)->nullable();          // override % rate

            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // One active record per employee per type (use effective_date for history)
            $table->index(['employee_id', 'compensation_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_compensations');
        Schema::dropIfExists('employees');
    }
};
