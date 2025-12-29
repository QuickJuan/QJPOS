<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS best_seller_report_view');
        DB::statement('DROP VIEW IF EXISTS daily_sales_report_per_invoice_view');
        DB::statement('DROP VIEW IF EXISTS daily_sales_report_per_item_view');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Views will be restored by the original create migrations if needed
    }
};
