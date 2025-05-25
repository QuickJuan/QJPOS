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
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); 
            $table->string('branch_code', 50)->unique()->nullable();
            $table->string('name', 150);
            $table->string('address', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->string('long_lat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('tin', 50)->nullable()->comment('VAT number for the branch');
            $table->string('registration_number', 100)->nullable()->comment('BIR POS Registration Number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
