<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedInteger('low_stock_threshold')->default(0)->after('default_location')
                ->comment('Alert when total stock falls at or below this value (0 = disabled)');
            $table->unsignedInteger('overstock_threshold')->default(0)->after('low_stock_threshold')
                ->comment('Alert when total stock rises at or above this value (0 = disabled)');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['low_stock_threshold', 'overstock_threshold']);
        });
    }
};
