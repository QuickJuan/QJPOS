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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('multiple_packaging')->nullable()->default(false)->after('is_active');
            $table->float('price')->default(0)->after('multiple_packaging');
            $table->string('unit_measure')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'multiple_packaging',
                'price',
                'unit_measure',
            ]);
        });
    }
};
