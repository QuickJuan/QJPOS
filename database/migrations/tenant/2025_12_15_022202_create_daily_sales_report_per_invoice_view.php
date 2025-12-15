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
        // Drop the view if it already exists
        DB::statement('DROP VIEW IF EXISTS daily_sales_report_per_invoice_view');

        // Create the view
        DB::statement("CREATE VIEW daily_sales_report_per_invoice_view AS
            SELECT
                ORD.id AS id,
                ORD.created_at AS order_date,
                CONCAT(cashier.name, ' - ', ORD.cashier_session_id) AS cashier_shift_number,
                cust.customer_name AS customer,
                ORD.invoice_no AS invoice_number,
                ORD.total_amount AS gross_sales,
                SUM(ORD.item_discount + ORD.less_tax) AS discount,
                ORD.total_due AS net_sales,
                ORD.status
            FROM
                orders ORD
                LEFT JOIN users AS cashier ON ORD.cashier_id = cashier.id
                LEFT JOIN customers AS cust ON ord.customer_id = cust.id
            WHERE
                DATE(ORD.created_at) = CURDATE()
                AND (item.is_void = false OR item.is_void <> 1)
                AND (
                    ORD.status <> 'refund'
                    OR ORD.status IS NULL
                )
            GROUP BY
                ORD.id,
                ORD.created_at,
                cashier.name,
                ORD.invoice_no,
                ORD.total_amount,
                ORD.total_due,
                ORD.status;
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS daily_sales_report_per_invoice_view');
    }
};
