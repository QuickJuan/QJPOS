<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('inventory_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->string('unit_measure', 50)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_product');
    }
};
