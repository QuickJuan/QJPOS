<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('movement_type', ['in', 'out'])->default('in');
            $table->decimal('quantity', 15, 4);
            $table->enum('unit_type', ['base', 'packaging'])->default('base');
            $table->unsignedBigInteger('unit_reference_id')->nullable();
            $table->decimal('resulting_stock', 15, 4);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('unit_reference_id')
                ->references('id')
                ->on('inventory_packagings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_movements');
    }
};
