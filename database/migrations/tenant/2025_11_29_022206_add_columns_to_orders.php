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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vatable_sales', 15, 2)->default(0)->after('amount_tendered');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('vatable_sales');
            $table->decimal('vat_exempt_sales', 15, 2)->default(0)->after('vat_amount');
            $table->decimal('zero_rated_sales', 15, 2)->default(0)->after('vat_exempt_sales');
            $table->decimal('non_vat', 15, 2)->default(0)->after('zero_rated_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'vatable_sales',
                'vat_amount',
                'vat_exempt_sales',
                'zero_rated_sales',
                'non_vat',
            ]);
        });
    }
};
