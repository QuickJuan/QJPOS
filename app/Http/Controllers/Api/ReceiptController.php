<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReceiptOrdersResource;
use App\Http\Resources\ReceiptOrderItemsResource;

class ReceiptController extends Controller
{

    public function __construct(
        private readonly ReceiptService $receiptService
    ) {
    }

    /**
     * Get receipt information by receipt number
     */
    public function getReceipt(string $receiptNumber): JsonResponse
    {

        $order = $this->receiptService->getReceipt($receiptNumber);

        if (!$order) {
            return response()->json([
                'message' => 'Receipt not found'
            ], 404);
        }

        return response()->json(
           new ReceiptOrdersResource($order)
        ,200);
    }


    /**
     * Get receipt items grouped by order type
     */
    public function getReceiptItems(string $receiptNumber): JsonResponse
    {
        $order = Order::with([
            'orderItems.product',
            'orderItems.subItems.product'
        ])->where('receipt_number', $receiptNumber)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Receipt not found'
            ], 404);
        }

        return response()->json([
            'data' => ReceiptOrderItemsResource::collection($order->orderItems->whereNull('parent_id'))
        ]);
    }

    /**
     * Download receipt as PDF or other format
     */
    public function downloadReceipt(string $receiptNumber): JsonResponse
    {
        // This can be implemented later for PDF generation
        return response()->json([
            'message' => 'Download functionality will be implemented soon',
            'receiptNumber' => $receiptNumber
        ]);
    }
}
