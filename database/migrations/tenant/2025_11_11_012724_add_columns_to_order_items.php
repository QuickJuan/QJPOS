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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->default(0)->after('price');
            $table->decimal('vatable_sales', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('vat_exempt_sales', 15, 2)->default(0)->after('vatable_sales');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('vat_exempt_sales');
            $table->decimal('non_vat_sales', 15, 2)->default(0)->after('vat_amount');
            $table->dropColumn('selected_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'vatable_sales',
                'vat_exempt_sales',
                'vat_amount',
                'non_vat_sales',
                'discount_amount',
            ]);
            $table->json('selected_options')->nullable()->after('meta_data');
        });
    }
};
