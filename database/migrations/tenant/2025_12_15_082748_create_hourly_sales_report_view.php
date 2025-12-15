<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS hourly_sales_report_view');

        // Create the view
        DB::statement("CREATE VIEW hourly_sales_report_view AS
        SELECT
            item.id AS id,
            item.created_at AS order_date,
            DATE_FORMAT(ORD.created_at, '%H:%i:%s %p') AS order_time,
            prod.name AS item_name,
            item.quantity AS quantity,
            item.price AS price,
            item.amount AS gross_sales,
            SUM(item.discount_amount + item.less_tax) AS discount,
            item.sub_total AS net_sales,
            prod.category_id AS category_id,
            prod.brand_id AS brand_id,
            ORD.status AS order_status
        FROM
            order_items item
            JOIN products AS prod ON item.product_id = prod.id
            JOIN orders AS ORD ON item.order_id = ORD.id
        WHERE
            item.created_at >= DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-%d %H:00:00')
            AND item.created_at < DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-%d %H:00:00') + INTERVAL 1 HOUR
            AND (
                ORD.status <> 'refund'
                OR ORD.status IS NULL
            )
            AND (item.is_void = false OR item.is_void <> 1)
        GROUP BY
            item.id,
            item.created_at,
            prod.name,
            item.quantity,
            item.price,
            item.amount,
            item.sub_total,
            prod.category_id,
            prod.brand_id;
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_sales_report_view');
    }
};
