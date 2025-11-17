<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['cashier', 'tableRoom', 'cashierSession', 'orderItems.product']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('cashier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tableRoom', function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%");
                    });
            });
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Cashier filter
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function refund(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'notes'           => 'required|string',
            'supervisor_name' => 'required|string',
        ]);

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
