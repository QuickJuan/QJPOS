<?php
namespace App\Services;

use App\Models\Order;

class ReceiptService
{

    public function __construct(private Order $order)
    {
    }

    public function getReceipt(int $receiptNumber, int $cashierSessionId): ?Order
    {
        $order = $this->order
            ->with(['orderItems', 'orderItems.product', 'cashierSession.branch', 'customer'])
            ->where('invoice_no', $receiptNumber)
            ->where('cashier_session_id', $cashierSessionId)
            ->first();

        return $order;
    }
}
