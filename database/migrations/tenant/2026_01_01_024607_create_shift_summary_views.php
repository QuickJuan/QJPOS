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
        // 1. Orders Summary Per Shift View
        DB::statement("
            CREATE VIEW orders_summary_per_shift_view AS
            SELECT
                o.cashier_session_id,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(o.total_amount) as total_amount,
                SUM(o.total_due) as total_due,
                SUM(o.item_discount) as item_discount,
                SUM(o.service_charge) as service_charge,
                SUM(o.less_tax) as less_tax,
                SUM(o.vatable_sales) as vatable_sales,
                SUM(o.vat_amount) as vat_amount,
                SUM(o.vat_exempt_sales) as vat_exempt_sales,
                SUM(o.zero_rated_sales) as zero_rated_sales,
                SUM(o.non_vat) as non_vat_sales,
                MIN(o.invoice_no) as min_invoice_no,
                MAX(o.invoice_no) as max_invoice_no,
                MIN(o.bill_no) as min_bill_no,
                MAX(o.bill_no) as max_bill_no,
                COUNT(DISTINCT oi.product_id) as total_sku,
                SUM(oi.quantity) as total_quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status = 'settled'
            GROUP BY o.cashier_session_id
        ");

        // 2. Void Items Per Shift View
        DB::statement("
            CREATE VIEW void_items_per_shift_view AS
            SELECT
                cashier_session_id,
                product_id,
                description,
                SUM(quantity) as void_quantity,
                SUM(amount) as void_amount
            FROM void_items
            WHERE cashier_session_id IS NOT NULL
            GROUP BY cashier_session_id, product_id, description
            ORDER BY void_quantity DESC
        ");

        // 3. Refunded Orders Per Shift View
        DB::statement("
            CREATE VIEW refunded_orders_per_shift_view AS
            SELECT
                cashier_session_id,
                COUNT(*) as total_refunded_orders,
                SUM(total_due) as total_refunded_amount
            FROM orders
            WHERE status = 'refunded'
            GROUP BY cashier_session_id
        ");

        // 4. Discount Breakdown Per Shift View
        DB::statement("
            CREATE VIEW discount_breakdown_per_shift_view AS
            SELECT
                o.cashier_session_id,
                d.discount_name,
                d.sort_order,
                SUM(oi.discount_amount) as total_discount
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.id
            INNER JOIN discounts d ON oi.discount_id = d.id
            WHERE oi.discount_amount IS NOT NULL
            GROUP BY o.cashier_session_id, d.discount_name, d.sort_order
            ORDER BY d.sort_order ASC
        ");

        // 5. Refunds From Other Shifts By Cashier View
        DB::statement("
            CREATE VIEW refunds_from_other_shifts_view AS
            SELECT
                refunded_cashier_id as cashier_id,
                COUNT(*) as total_refunded_orders,
                SUM(total_due) as total_refunded_amount
            FROM orders
            WHERE status = 'refund'
                AND refunded_cashier_id IS NOT NULL
                AND refunded_cashier_id != cashier_id
            GROUP BY refunded_cashier_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS refunds_from_other_shifts_view');
        DB::statement('DROP VIEW IF EXISTS discount_breakdown_per_shift_view');
        DB::statement('DROP VIEW IF EXISTS refunded_orders_per_shift_view');
        DB::statement('DROP VIEW IF EXISTS void_items_per_shift_view');
        DB::statement('DROP VIEW IF EXISTS orders_summary_per_shift_view');
    }
};
