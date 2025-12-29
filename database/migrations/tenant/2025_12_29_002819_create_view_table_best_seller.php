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
        DB::statement("
            CREATE OR REPLACE VIEW best_seller_report_view AS
            SELECT
                ROW_NUMBER() OVER (ORDER BY o.branch_id, YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), p.id) as id,
                o.branch_id,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as year_no,
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as month_no,
                p.id as product_id,
                b.name as branch_name,
                p.name as product_name,
                SUM(oi.quantity) as qty_sold,
                SUM(oi.sub_total) as net_sales
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN products p ON oi.product_id = p.id
            INNER JOIN branches b ON o.branch_id = b.id
            WHERE o.status != 'refund'
                AND oi.is_void = 0
                AND oi.parent_id IS NULL
            GROUP BY
                o.branch_id,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                p.id,
                b.name,
                p.name
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS best_seller_report_view');
    }
};
