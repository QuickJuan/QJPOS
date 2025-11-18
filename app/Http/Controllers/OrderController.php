<?php
namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function refund(RefundRequest $request, Order $order): JsonResponse
    {
        if ($order->status !== 'settled') {
            return response()->json(['message' => 'Only settled orders can be refunded'], 400);
        }

        $order->update([
            'status'    => 'refund',
            'meta_data' => array_merge($order->meta_data ?? [], [
                'refund' => [
                    'requested_by' => Auth::user()->name,
                    'supervisor'   => $request->supervisor_name,
                    'notes'        => $request->notes,
                    'refunded_at'  => now(),
                ],
            ])
        ]);

        return response()->json(['message' => 'Order refunded successfully', 'order' => $order]);
    }
}
