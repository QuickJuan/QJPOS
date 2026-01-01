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
            CREATE VIEW payment_summary_by_session_view AS
            SELECT
                o.cashier_session_id,
                p.payment_method_id,
                pm.name AS payment_method_name,
                pm.payment_type,
                pm.currency_code,
                pm.currency_name,
                pm.symbol,
                pm.is_default_cash,
                COUNT(p.id) AS payment_count,
                SUM(p.amount) AS total_amount,
                SUM(p.amount_in_payment_currency) AS total_amount_in_payment_currency,
                SUM(p.change_amount) AS total_change_amount,
                MIN(p.created_at) AS first_payment_at,
                MAX(p.created_at) AS last_payment_at
            FROM payments p
            INNER JOIN orders o ON p.order_id = o.id
            INNER JOIN payment_methods pm ON p.payment_method_id = pm.id
            WHERE o.cashier_session_id IS NOT NULL AND o.status = 'settled'
            GROUP BY
                o.cashier_session_id,
                p.payment_method_id,
                pm.name,
                pm.payment_type,
                pm.currency_code,
                pm.currency_name,
                pm.symbol,
                pm.is_default_cash
            ORDER BY o.cashier_session_id, pm.name
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS payment_summary_by_session_view');
    }
};
