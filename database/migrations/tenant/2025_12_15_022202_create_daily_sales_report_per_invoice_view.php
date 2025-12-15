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
                CONCAT(cashier.name, ' - ', cashier_session.id) AS cashier_shift_number,
                table_room.customer_name AS customer,
                ORD.invoice_no AS invoice_number,
                ORD.total_amount AS gross_sales,
                SUM(ORD.total_discount + ORD.less_tax) AS discount,
                ORD.total_due AS net_sales,
                ORD.status
            FROM
                orders ORD
                JOIN users AS cashier ON ORD.cashier_id = cashier.id
                JOIN cashier_sessions AS cashier_session ON ORD.cashier_session_id = cashier_session.id
                JOIN table_rooms AS table_room ON ORD.table_room_id = table_room.id
            WHERE
                DATE(ord.created_at) = CURDATE()
            GROUP BY
                ORD.id,
                ORD.created_at,
                cashier.name,
                cashier_session.id,
                table_room.customer_name,
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
