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
            CREATE OR REPLACE VIEW hourly_sales_by_day_view AS
            SELECT
                CONCAT(o.branch_id, '-', YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), '-', MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')), '-', HOUR(CONVERT_TZ(o.created_at, '+00:00', '+08:00'))) as id,
                o.branch_id,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as year_no,
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as month_no,
                HOUR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) as sale_hour,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 2 THEN oi.sub_total END), 0) AS monday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 3 THEN oi.sub_total END), 0) AS tuesday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 4 THEN oi.sub_total END), 0) AS wednesday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 5 THEN oi.sub_total END), 0) AS thursday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 6 THEN oi.sub_total END), 0) AS friday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 7 THEN oi.sub_total END), 0) AS saturday_sales,
                COALESCE(SUM(CASE WHEN DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', '+08:00')) = 1 THEN oi.sub_total END), 0) AS sunday_sales,
                COALESCE(SUM(oi.sub_total), 0) AS total_sales
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status != 'refund'
                AND oi.is_void = 0
                AND oi.parent_id IS NULL
            GROUP BY
                o.branch_id,
                YEAR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                MONTH(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                HOUR(CONVERT_TZ(o.created_at, '+00:00', '+08:00')),
                id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS hourly_sales_by_day_view');
    }
};
