<?php
namespace App\Http\Controllers;

use App\Enums\Receipt\Type;
use App\Http\Requests\RefundRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\CashierSessionService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CashierSessionService $cashierSessionService
    ) {
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->getOrders(
            filters: $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
            perPage: 5
        );

        $cashiers      = User::select('id', 'name')->orderBy('name')->get();
        $receiptFooter = $this->cashierSessionService->getReceiptFooter(Type::RECEIPT->value);

        return Inertia::render('Transactions/Index', [
            'orders'        => $orders,
            'cashiers'      => $cashiers,
            'receiptFooter' => $receiptFooter,
            'filters'       => $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
        ]);
    }
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
