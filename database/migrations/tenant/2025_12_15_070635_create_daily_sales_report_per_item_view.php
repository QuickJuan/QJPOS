<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS daily_sales_report_per_item_view');

        // Create the view
        DB::statement("CREATE VIEW daily_sales_report_per_item_view AS
        SELECT
            item.id AS id,
            item.created_at AS order_date,
            prod.name AS item_name,
            item.quantity AS quantity,
            item.price AS price,
            item.amount AS gross_sales,
            SUM(item.discount_amount + item.less_tax) AS discount,
            item.sub_total AS net_sales,
            prod.category_id AS category_id,
            prod.brand_id AS brand_id,
            ord.status AS order_status
        FROM
            order_items item
            JOIN products AS prod ON item.product_id = prod.id
            JOIN orders AS ord ON item.order_id = ord.id
        WHERE
            AND (item.is_void = false OR item.is_void <> 1)
            AND (
                ORD.status <> 'refund'
                OR ORD.status IS NULL
            )
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
        Schema::dropIfExists('daily_sales_report_per_item_view');
    }
};
