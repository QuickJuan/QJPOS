<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS best_seller_report_view');

        // Create the view
        DB::statement("CREATE VIEW best_seller_report_view AS
        SELECT
            o.id,
            o.created_at AS order_date,
            p.name AS product,
            SUM(oi.quantity) AS qty,
            SUM(oi.sub_total) AS net_sales
        FROM
            orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
        WHERE
            o.status <> 'refund'
            AND o.created_at >= DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-01 00:00:00')
            AND o.created_at < DATE_ADD(DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-01 00:00:00'), INTERVAL 1 MONTH)
        GROUP BY
            o.id,
            o.created_at,
            p.id,
            p.name
        ORDER BY
            qty DESC;
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('best_seller_report_view');
    }
};
