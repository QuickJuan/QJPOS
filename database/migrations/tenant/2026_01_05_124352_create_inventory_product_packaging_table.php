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
        Schema::create('inventory_product_packaging', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_packaging_id')
                ->constrained('product_packagings')
                ->cascadeOnDelete();
            $table->foreignId('inventory_id')
                ->constrained('inventories')
                ->cascadeOnDelete();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->string('unit_measure', 50)->nullable();
            $table->string('unit_type')->default('base');
            $table->unsignedBigInteger('unit_reference_id')->nullable();
            $table->timestamps();

            $table->unique(['product_packaging_id', 'inventory_id'], 'inv_prod_pkg_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_product_packaging');
    }
};
