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
        //use DB query to get count and sum of amount of void order items for a specific shift
        $voidItems = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $shiftId)
            ->where('is_void', '=', true)
            ->selectRaw('product_id, description, SUM(quantity) as void_quantity, SUM(amount) as void_amount')
            ->groupBy('product_id', 'description')
            ->orderBy('void_quantity', 'desc')
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
        //use DB query to get count and sum of total_due of refunded orders for a specific shift
        $refundedOrders = \DB::table('orders')
            ->where('cashier_session_id', $shiftId)
            ->where('status', '=', 'refunded')
            ->selectRaw('COUNT(*) as total_refunded_orders,
                SUM(total_due) as total_refunded_amount')
            ->first();
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
        //use DB query to get count and sum of total_due of refunded orders for a specific shift
        $refundAfterShiftOrders = \DB::table('orders')
            ->where('refunded_cashier_id', $cashierId)
            ->where('status', '=', 'refund')
            ->where('refunded_cashier_id', '!=', 'cashier_id')
            ->where('refunded_cashier_id', '!=', null)
            ->selectRaw('COUNT(*) as total_refunded_orders,
                SUM(total_due) as total_refunded_amount')
            ->first();

        return $refundAfterShiftOrders;
    }


    /**
     * get the Discount breadown per discount type for a specific shift
     */
    public function getDiscountBreakdownPerShift(int $shiftId)
    {
        //use DB query to get sum of discount amount per discount type for a specific shift
        $discounts = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('discounts', 'order_items.discount_id', '=', 'discounts.id')
            ->where('orders.cashier_session_id', $shiftId)
            ->where('order_items.discount_amount', '!=', null)
            ->selectRaw('discounts.discount_name, SUM(order_items.discount_amount) as total_discount')
            ->groupBy('discounts.discount_name', 'discounts.sort_order')
            ->orderBy('discounts.sort_order', 'asc')
            ->get();
        return $discounts;
    }

}
