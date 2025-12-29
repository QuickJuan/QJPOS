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
        DB::statement("
            CREATE OR REPLACE VIEW daily_summary_report_view AS
            SELECT
                ROW_NUMBER() OVER (ORDER BY o.branch_id, DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), cat.id, br.id, p.id) as id,
                o.branch_id,
                DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as sale_date,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as year_no,
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as month_no,
                DAY(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as day_no,
                b.name as branch_name,
                cat.id as category_id,
                cat.name as category_name,
                br.id as brand_id,
                br.name as brand_name,
                p.id as product_id,
                p.name as product_name,
                SUM(oi.quantity) as sold,
                SUM(oi.price * oi.quantity) as gross,
                SUM(oi.discount_amount + oi.less_tax) as discount_amount,
                SUM(oi.sub_total) as sub_total
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN products p ON oi.product_id = p.id
            INNER JOIN categories cat ON p.category_id = cat.id
            LEFT JOIN brands br ON p.brand_id = br.id
            INNER JOIN branches b ON o.branch_id = b.id
            WHERE o.status != 'refund'
                AND oi.is_void = 0
            GROUP BY
                o.branch_id,
                DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                DAY(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                b.name,
                cat.id,
                cat.name,
                br.id,
                br.name,
                p.id,
                p.name
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS daily_summary_report_view');
    }
};
