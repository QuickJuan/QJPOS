<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Groups classify income/deduction types for reporting
        // e.g. "Government Mandated", "De Minimis Benefits", "Taxable Allowances"
        Schema::create('compensation_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                             // e.g. "Government Mandated"
            $table->enum('applies_to', ['income', 'deduction', 'both'])->default('both');
            $table->string('color', 7)->default('#6366f1');    // hex color for UI badges
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Specific compensation/deduction types within a group
        Schema::create('compensation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compensation_group_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();                   // SSS, PHIC, PAGIBIG, WHT, RICE_ALLOW …
            $table->string('name');                             // "SSS Contribution", "Rice Allowance"
            $table->enum('type', ['income', 'deduction']);      // which side of the payslip
            $table->boolean('is_taxable')->default(false);      // subject to withholding tax?
            $table->boolean('is_mandatory')->default(false);    // govt-required (SSS, PhilHealth, etc.)
            $table->boolean('is_employer_shared')->default(false); // employer also contributes?
            // computation_type: fixed = flat amount | percentage = % of basic | table = govt contribution table
            $table->enum('computation_type', ['fixed', 'percentage', 'table'])->default('fixed');
            $table->decimal('default_amount', 12, 2)->nullable();   // used when computation_type = fixed
            $table->decimal('default_rate', 8, 4)->nullable();      // used when computation_type = percentage (e.g. 0.0200 = 2%)
            // which employment types this applies to by default
            $table->boolean('applies_to_regular')->default(true);
            $table->boolean('applies_to_part_time')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensation_types');
        Schema::dropIfExists('compensation_groups');
    }
};
