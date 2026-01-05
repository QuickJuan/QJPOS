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
            CREATE OR REPLACE VIEW sales_by_cashier_server_view AS
            SELECT
                ROW_NUMBER() OVER (ORDER BY o.branch_id, DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), server.id, cashier.id) as id,
                o.branch_id,
                DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as sale_date,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as year_no,
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as month_no,
                DAY(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as day_no,
                b.name as branch_name,
                server.id as server_id,
                server.name as server_name,
                cashier.id as cashier_id,
                cashier.name as cashier_name,
                SUM(oi.price * oi.quantity) as gross,
                SUM(oi.discount_amount + oi.less_tax) as discount_amount,
                SUM(oi.sub_total) as sub_total
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN branches b ON o.branch_id = b.id
            LEFT JOIN users server ON oi.served_by = server.id
            LEFT JOIN users cashier ON o.cashier_id = cashier.id
            WHERE o.status != 'refund'
                AND oi.is_void = 0
                AND oi.parent_id IS NULL
            GROUP BY
                o.branch_id,
                DATE(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                DAY(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                b.name,
                server.id,
                server.name,
                cashier.id,
                cashier.name
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS sales_by_cashier_server_view');
    }
};
