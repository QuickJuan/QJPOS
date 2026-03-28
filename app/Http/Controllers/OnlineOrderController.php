<?php

namespace App\Http\Controllers;

use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\TableRoom;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class OnlineOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $this->pendingOnlineOrdersQuery($request)
            ->with([
                'cartItems.product',
                'tableRoom.tableRoomLocation',
            ])
            ->latest('created_at')
            ->get()
            ->map(fn (Cart $cart) => $this->transformOrder($cart))
            ->values();

        $tableRooms = TableRoom::query()
            ->with('tableRoomLocation')
            ->activeBranch()
            ->whereNull('merge_to')
            ->where('status', '!=', TableRoomStatusType::OCCUPIED->value)
            ->orderBy('table_room_location_id')
            ->orderBy('name')
            ->get()
            ->map(function (TableRoom $tableRoom) {
                return [
                    'id' => $tableRoom->id,
                    'name' => $tableRoom->name,
                    'status' => $tableRoom->status,
                    'location_name' => $tableRoom->tableRoomLocation?->name,
                    'location_type' => $tableRoom->tableRoomLocation?->location_type,
                ];
            })
            ->values();

        return Inertia::render('Resto/OnlineOrders/Index', [
            'orders' => $orders,
            'tableRooms' => $tableRooms,
            'currentUser' => $request->user(),
        ]);
    }

    public function process(Request $request, int $cartId, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'table_room_id' => 'required|integer|exists:table_rooms,id',
        ]);

        $cashierSession = $request->user()?->cashierSession;

        if (! $cashierSession) {
            return back()->with('error', 'An active cashier session is required before processing online orders.');
        }

        $cart = $this->pendingOnlineOrdersQuery($request)
            ->with('cartItems')
            ->findOrFail($cartId);

        $tableRoom = TableRoom::query()
            ->with('tableRoomLocation')
            ->activeBranch()
            ->findOrFail($validated['table_room_id']);

        if ($tableRoom->status === TableRoomStatusType::OCCUPIED->value) {
            return back()->with('error', 'The selected table or room is already occupied.');
        }

        $guestCheckout = data_get($cart->meta_data, 'guest_checkout', []);
        $metaData = $cart->meta_data ?? [];
        $metaData['guest_checkout'] = array_merge($guestCheckout, [
            'status' => 'preparing', // immediately sent to kitchen in this same request
            'processed_at' => now()->toIso8601String(),
            'processed_by' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
            ],
            'assigned_table_room' => [
                'id' => $tableRoom->id,
                'name' => $tableRoom->name,
                'location' => $tableRoom->tableRoomLocation?->name,
                'location_type' => $tableRoom->tableRoomLocation?->location_type,
            ],
        ]);

        $cart->update(array_filter([
            'cashier_id' => $request->user()->id,
            'cashier_session_id' => $cashierSession->id,
            'table_room_id' => $tableRoom->id,
            'processed_at' => $this->cartColumnExists('processed_at') ? now() : null,
            'meta_data' => $metaData,
        ], fn ($value, $key) => $key === 'processed_at' ? $this->cartColumnExists('processed_at') : true, ARRAY_FILTER_USE_BOTH));

        $tableRoom->update([
            'status' => TableRoomStatusType::OCCUPIED->value,
            'time_in' => now(),
            'dining_start' => now(),
            'customer_name' => data_get($metaData, 'guest_checkout.name', 'Online customer'),
            'number_of_pax' => max(1, (int) $cart->cartItems->sum('quantity')),
        ]);

        $cartService->placeOrder([
            'cart_id' => $cart->id,
            'table_id' => $tableRoom->id,
            'served_by' => $request->user()->id,
            'serving_number' => $this->getCartReferenceNo($cart),
        ]);

        return redirect()
            ->route('resto.online-orders.index')
            ->with('success', "Online order {$this->getCartReferenceNo($cart)} was assigned to {$tableRoom->name} and sent to the kitchen.");
    }

    private function pendingOnlineOrdersQuery(Request $request)
    {
        return Cart::query()
            ->where('branch_id', $request->user()?->branch_id)
            ->where(function ($query) {
                if ($this->cartColumnExists('source')) {
                    $query->where('source', 'customer');
                }

                $query->orWhereNotNull('meta_data->guest_checkout->reference_no');
            })
            ->when(
                $this->cartColumnExists('processed_at'),
                fn ($query) => $query->whereNull('processed_at'),
                fn ($query) => $query->whereNull('meta_data->guest_checkout->processed_at')
            );
    }

    private function transformOrder(Cart $cart): array
    {
        $cart->loadMissing('cartItems.product', 'tableRoom.tableRoomLocation');

        $subtotal = (float) $cart->cartItems->sum('amount');
        $discount = (float) ($cart->total_discount ?? 0);

        return [
            'id' => $cart->id,
            'reference_no' => $this->getCartReferenceNo($cart),
            'submitted_at' => $cart->submitted_at?->toIso8601String() ?? data_get($cart->meta_data, 'guest_checkout.submitted_at') ?? $cart->created_at?->toIso8601String(),
            'status' => data_get($cart->meta_data, 'guest_checkout.status', 'pending'),
            'customer' => [
                'name' => data_get($cart->meta_data, 'guest_checkout.name'),
                'phone' => data_get($cart->meta_data, 'guest_checkout.phone'),
                'email' => data_get($cart->meta_data, 'guest_checkout.email'),
                'address' => data_get($cart->meta_data, 'guest_checkout.address'),
                'order_type' => data_get($cart->meta_data, 'guest_checkout.order_type'),
                'notes' => data_get($cart->meta_data, 'guest_checkout.notes'),
            ],
            'coupon' => data_get($cart->meta_data, 'guest_checkout.coupon'),
            'totals' => [
                'items' => (int) $cart->cartItems->sum('quantity'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max(0, $subtotal - $discount),
            ],
            'items' => $cart->cartItems->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? $item->description,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->amount,
            ])->values(),
            'assigned_table_room' => $cart->tableRoom ? [
                'id' => $cart->tableRoom->id,
                'name' => $cart->tableRoom->name,
                'location' => $cart->tableRoom->tableRoomLocation?->name,
            ] : null,
        ];
    }

    private function getCartReferenceNo(Cart $cart): ?string
    {
        return $this->cartColumnExists('reference_no')
            ? $cart->reference_no
            : data_get($cart->meta_data, 'guest_checkout.reference_no');
    }

    private function cartColumnExists(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= Schema::hasColumn('carts', $column);
    }
}
