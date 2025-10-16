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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('discount_name');
            $table->integer('amount')->default(0);
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->boolean('remove_tax')->default(false);
            $table->enum('discount_type', ['regular', 'special', 'senior', 'pwd'])->default('regular');
            $table->boolean('require_customer_info')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
