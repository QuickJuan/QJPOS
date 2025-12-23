<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_product', function (Blueprint $table) {
            $table->string('unit_type')->default('base')->after('unit_measure');
            $table->unsignedBigInteger('unit_reference_id')->nullable()->after('unit_type');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_product', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'unit_reference_id']);
        });
    }
};
