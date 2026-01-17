<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_addon_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_packaging_id')
                ->nullable()
                ->constrained('product_packagings')
                ->nullOnDelete();

            $table->decimal('add_on_price', 12, 2)->default(0);

            $table->timestamps();

            $table->index('product_id');
            $table->index('product_addon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_add_ons');
    }
};
