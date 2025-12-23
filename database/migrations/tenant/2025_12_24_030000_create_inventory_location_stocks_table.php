<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_location_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_stock', 15, 4)->default(0);
            $table->timestamps();

            $table->unique(['inventory_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_location_stocks');
    }
};
