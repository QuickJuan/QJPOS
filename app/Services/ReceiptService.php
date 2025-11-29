<?php
namespace App\Services;

use App\Models\Order;


class ReceiptService
{

    public function __construct(private Order $order)
    {
    }

    public function getReceipt(int $receiptNumber): ?Order
    {
        $order =  $this->order
            ->with(['orderItems', 'orderItems.product', 'cashierSession.branch', 'customer'])
            ->where('invoice_no', $receiptNumber)
            ->first();

        return $order;
    }
}
