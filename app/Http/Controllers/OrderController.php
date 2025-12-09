<?php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\RefundRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralSettingsService;
use App\Http\Resources\ReceiptOrdersResource;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        $generalSettings = app(GeneralSettingsService::class)->getCompanySettings();
        $orders = $this->orderService->getOrders(
            filters: $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
            perPage: 10
        );

        $cashiers = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Transactions/Index', [
            'orders'   => ReceiptOrdersResource::collection($orders),
            'cashiers' => $cashiers,
            'filters'  => $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
            'generalSettings' => $generalSettings,
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
