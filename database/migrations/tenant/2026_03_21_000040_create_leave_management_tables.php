<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();         // SL, VL, ML, PL, SPL, VAWC
            $table->string('name');
            $table->boolean('is_paid')->default(true);   // paid leave vs LWOP type
            $table->decimal('default_days_per_year', 6, 2)->default(15);
            $table->boolean('requires_document')->default(false); // e.g. medical cert for SL
            $table->boolean('applies_to_regular')->default(true);
            $table->boolean('applies_to_part_time')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_leave_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('total_days', 6, 2)->default(0);  // allocated for the year
            $table->decimal('used_days', 6, 2)->default(0);   // approved paid leaves taken
            $table->timestamps();
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_requested', 6, 2)->default(1); // working days (0.5 for half-day)
            $table->decimal('days_with_pay', 6, 2)->default(0);    // computed on approval
            $table->decimal('days_without_pay', 6, 2)->default(0); // LWOP days (salary deduction)
            $table->boolean('is_half_day')->default(false);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('employee_leave_credits');
        Schema::dropIfExists('leave_types');
    }
};
