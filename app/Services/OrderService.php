<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class OrderService
{
    public function getOrders(array $filters = [], int $perPage = 5): LengthAwarePaginator
    {
        $query = Order::with(['cashier', 'tableRoom', 'cashierSession', 'orderItems.product']);

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Date range filters - only apply if explicitly provided
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $dateFrom = !empty($filters['date_from']) ? $filters['date_from'] : Carbon::minValue()->toDateString();
            $dateTo = !empty($filters['date_to']) ? $filters['date_to'] : Carbon::now()->toDateString();

            $query->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
        }

        // Cashier filter
        if (!empty($filters['cashier_id'])) {
            $query->cashier($filters['cashier_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        return $query->orderBy('id', 'desc')
            ->paginate(perPage: $perPage)
            ->withQueryString();
    }

    /**
     * Get Total Orders per shift
     * Get total Count of orders for a specific cashier shift
     * Get total sum of total_amount for a specific cashier shift
     * Get total sum of total_due for a specific cashier shift
     * Get total sum of item_discount for a specific cashier shift
     * Get total sum of service_charge for a specific cashier shift
     * Get total sum of less_tax for a specific cashier shift
     * Get total sum of vat_amount for a specific cashier shift
     * Get total sum of vat_exempt_sales for a specific cashier shift
     * Get total sum of zero_rated_sales for a specific cashier shift
     * Get total sum of non_vat_sales for a specific cashier shift
     * Get Min and Max of Invoice_no for a specific cashier shift
     * Get Min and Max of bill_no for a specific cashier shift
     */
    public function getTotalOrdersPerShift(int $shiftId)
    {
        //use DB query to get count and sum of total_amount total orders for a specific shift
        $orders = \DB::table('orders')
            ->where('cashier_session_id', $shiftId)
            ->where('status', '=', 'settled')
            ->selectRaw('COUNT(*) as total_orders,
                SUM(total_amount) as total_amount,
                SUM(total_due) as total_due,
                SUM(item_discount) as item_discount,
                SUM(service_charge) as service_charge,
                SUM(less_tax) as less_tax,
                SUM(vatable_sales) as vatable_sales,
                SUM(vat_amount) as vat_amount,
                SUM(vat_exempt_sales) as vat_exempt_sales,
                SUM(zero_rated_sales) as zero_rated_sales,
                SUM(non_vat) as non_vat_sales,
                MIN(invoice_no) as min_invoice_no,
                MAX(invoice_no) as max_invoice_no,
                MIN(bill_no) as min_bill_no,
                MAX(bill_no) as max_bill_no')
            ->first();
        return $orders;
    }


    /**
     * Get count of product and total qty from order items
     */
    public function getOrderItemsCount(int $shiftId)
    {
        //use DB query to get count and sum of quantity of order items for a specific shift
        $orderItems = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $shiftId)
            ->where('is_void', '=', false)
            ->selectRaw(' count(product_id) total_sku, SUM(quantity) as total_quantity')
            ->orderBy('total_quantity', 'desc')
            ->first();

        return $orderItems ;
    }


    /**
     * Get void order items per shift
     * Get total Count of void order items for a specific cashier shift
     * Get total sum of amount for void order items for a specific cashier shift
     */
    public function getVoidOrderItemsPerShift(int $shiftId)
    {
        // Use view for optimized query
        $voidItems = \DB::table('void_items_per_shift_view')
            ->where('cashier_session_id', $shiftId)
            ->get();
        return $voidItems;
    }


    /**
     * Get list sum of orders with refund status
     * Get count of orders with refund status
     * Get total sum of total_due for orders with refund status
     */
    public function getRefundOrdersPerShift(int $shiftId)
    {
        // Use view for optimized query
        $refundedOrders = \DB::table('refunded_orders_per_shift_view')
            ->where('cashier_session_id', $shiftId)
            ->first();

        // Return default values if no refunds found
        if (!$refundedOrders) {
            return (object) [
                'total_refunded_orders' => 0,
                'total_refunded_amount' => 0
            ];
        }

        return $refundedOrders;
    }

    /**
     * Get list sum of orders with refund status by other cashier using the refunded_cashier_id
     * Get count of orders with refund status
     * Get total sum of total_due for orders with refund status
     */
    public function getRefundAfterShiftOrders(int $shiftId)
    {
        //use DB query to get count and sum of total_due of refunded orders for a specific shift
        $refundAfterShiftOrders = \DB::table('orders')
            ->where('cashier_session_id', $shiftId)
            ->where('status', '=', 'refund')
            ->where('refunded_cashier_id', '!=', 'cashier_id')
            ->where('refunded_cashier_id', '!=', null)
            ->selectRaw('COUNT(*) as total_refunded_orders,
                SUM(total_due) as total_refunded_amount')
            ->first();

        return $refundAfterShiftOrders;
    }


    /**
     * Get list sum of orders with refund status from other cashier using the refunded_cashier_id
     * Get count of orders with refund status
     * Get total sum of total_due for orders with refund status
     */
    public function getRefundAFromOtherShiftOrders(int $cashierId)
    {
        // Use view for optimized query
        $refundAfterShiftOrders = \DB::table('refunds_from_other_shifts_view')
            ->where('cashier_id', $cashierId)
            ->first();

        // Return default values if no refunds found
        if (!$refundAfterShiftOrders) {
            return (object) [
                'total_refunded_orders' => 0,
                'total_refunded_amount' => 0
            ];
        }

        return $refundAfterShiftOrders;
    }


    /**
     * get the Discount breadown per discount type for a specific shift
     */
    public function getDiscountBreakdownPerShift(int $shiftId)
    {
        // Use view for optimized query
        $discounts = \DB::table('discount_breakdown_per_shift_view')
            ->where('cashier_session_id', $shiftId)
            ->get();
        return $discounts;
    }

    /**
     * Get comprehensive shift sales statistics in a single query.
     * Combines order totals and product counts.
     */
    public function getSummarySalesPerShift(int $shiftId)
    {
        info('getting summary sales per shift for shift ID: ' . $shiftId);
        // Use view for optimized query
        $stats = \DB::table('orders_summary_per_shift_view')
            ->where('cashier_session_id', $shiftId)
            ->first();

        // Return default values if no data found
        if (!$stats) {
            return (object) [
                'total_orders' => 0,
                'total_amount' => 0,
                'total_due' => 0,
                'item_discount' => 0,
                'service_charge' => 0,
                'less_tax' => 0,
                'vatable_sales' => 0,
                'vat_amount' => 0,
                'vat_exempt_sales' => 0,
                'zero_rated_sales' => 0,
                'non_vat_sales' => 0,
                'min_invoice_no' => 0,
                'max_invoice_no' => 0,
                'min_bill_no' => 0,
                'max_bill_no' => 0,
                'total_sku' => 0,
                'total_quantity' => 0
            ];
        }

        return $stats;
    }

}
