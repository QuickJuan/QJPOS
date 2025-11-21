<?php
namespace App\Services;

use App\Enums\Receipt\Type;
use App\Enums\TableRoomStatusType;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\ReceiptFooter;
use App\Models\TableRoom;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionService
{
    public function __construct(public CashierSession $model)
    {
        $this->model = $model;
    }

    public function startSession(Request $request): CashierSession
    {
        // Check if user already has an open session
        $existingSession = $this->model
            ->openSession()
            ->first();

        if ($existingSession) {
            throw new Exception('You already have an open session. Please continue or close it first before starting new one.');
        }

        $session = $this->model->create([
            'business_date'  => now()->toDateString(),
            'cashier_id'     => Auth::id(),
            'started_time'   => now(),
            'beginning_cash' => $request['beginning_cash'],
            'total_sales'    => 0,
            'closing_cash'   => 0,
        ]);

        $branch = Branch::find($request['branch_id']);

        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        $branch->order_number += 1;
        $branch->save();

        return $session;
    }

    public function closeSession(Request $request)
    {
        $session = $this->model
            ->openSession()
            ->first();

        if (! $session) {
            throw new Exception('No open session found');
        }

        $closeSession = $session->update([
            'closing_time'      => now(),
            'closing_cash'      => $request->closing_cash,
            'cash_denomination' => $request->cash_denomination,
        ]);

        return $closeSession;
    }

    public function createOrder(Request $request)
    {
        $table = TableRoom::findOrFail($request->table_id);
        $table->update([
            'status'        => TableRoomStatusType::OCCUPIED->value,
            'time_in'       => Carbon::now(),
            'customer_name' => $request->guest_name,
            'number_of_pax' => $request->pax,
        ]);

        $cashierSession = $this->model->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::firstOrCreate([
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
            'table_room_id'      => $table->id,
        ]);

        if (! $cart) {
            throw new Exception('There was an error in creating cart.');
        }

        return [$table, $cart];
    }

    public function prepareViewData(
        $categories,
        $pendingCashiering,
        $cart,
        array $cartItems,
        $discounts,
        $modifiers,
        $currentTable,
        $taxRate,
        array $totals,
        $billFooter,
        $receiptFooter,
        $billNumber,
        $receiptNumber
    ): array {
        return [
            'categories'         => $categories,
            'pendingCashiering'  => $pendingCashiering,
            'currentUser'        => Auth::user(),
            'cart'               => $cart,
            'cartItems'          => $cartItems,
            'availableDiscounts' => $discounts,
            'availableModifiers' => $modifiers,
            'currentTable'       => $currentTable ?? [],
            'subTotal'           => $totals['subAmount'],
            'total'              => $totals['total'],
            'lessTaxTotal'       => $totals['lessTaxTotal'],
            'lessDiscountTotal'  => $totals['lessDiscountTotal'],
            'taxRate'            => $taxRate,
            'billFooter'         => $billFooter,
            'receiptFooter'      => $receiptFooter,
            'billNumber'         => $billNumber,
            'receiptNumber'      => $receiptNumber,
        ];
    }

    public function getCartData(Request $request, $pendingCashiering): array
    {
        $cartItems    = [];
        $cart         = null;
        $currentTable = null;

        if (! $pendingCashiering) {
            return compact('cart', 'cartItems', 'currentTable');
        }

        $cartQuery = Cart::query();

        if ($request->has('tableId')) {
            $cartQuery->where('table_room_id', $request->input('tableId'));
            $currentTable = TableRoom::find($request->input('tableId'));
        }

        $cart = $cartQuery->where('cashier_id', Auth::id())
            ->where('cashier_session_id', $pendingCashiering->id)
            ->with(['cartItems.product.productPackagings', 'cartItems.children.product'])
            ->first();

        if ($cart) {
            $cartItems = $this->processCartItems($cart->cartItems);
        }

        return compact('cart', 'cartItems', 'currentTable');
    }

    private function processCartItems($cartItems): array
    {
        return $cartItems->map(fn($item) => [
            'id'                => $item->id,
            'parent_id'         => $item->parent_id,
            'product_id'        => $item->product_id,
            'name'              => $item->product->name ?? 'Unknown Product',
            'quantity'          => $item->quantity,
            'price'             => $item->price,
            'amount'            => $item->amount,
            'sub_total'         => $item->sub_total,
            'placed_order'      => (bool) $item->placed_order,
            'order_type'        => $item->order_type,
            'selected_options'  => $item->selected_options ?? [],
            'meta_data'         => $item->meta_data ?? [],
            'discount'          => $item->discount_amount,
            'less_tax'          => $item->less_tax,
            'product_packaging' => $item->product_packaging_id ? $item->product->productPackagings->firstWhere('id', $item->product_packaging_id) : null,
            'product'           => $item->product,
            'checked'           => false,
            'children'          => $item->children,
        ])->toArray();
    }

    public function calculateTotals($cart, array $cartItems): array
    {
        $subAmount         = collect($cartItems)->sum('amount');
        $lessTaxTotal      = collect($cartItems)->sum('less_tax');
        $lessDiscountTotal = collect($cartItems)->sum('discount');

        $total = null;
        if ($cart && $cart->cartItems->isNotEmpty()) {
            $total = $cart->cartItems->sum(function ($item) {
                return ($item->sub_total ?? 0) - ($item->discount ?? 0);
            });
        }

        return compact('subAmount', 'lessTaxTotal', 'lessDiscountTotal', 'total');
    }

    public function getReceiptFooter(string $type = Type::RECEIPT->value)
    {
        return ReceiptFooter::where('type', $type)->first();
    }

    public function getBillNo(int $branchId): mixed
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        return $branch->bill_no;
    }

    public function getReceiptNo(int $branchId): mixed
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        return $branch->or_number;
    }

    public function updateBillNo(int $branchId)
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        $branch->bill_no += 1;
        $branch->save();
    }

    public function getSessionSummary(?CashierSession $session = null): array
    {
        // Get all orders for this session with relationships
        $orders = $session->orders()->with(['orderItems', 'tableRoom'])->get();

        $orderSummaries = [];
        $totalSales     = 0;
        $itemsSettled   = 0;
        $guestsServed   = 0;

        foreach ($orders as $order) {
            // Calculate order total from orderItems
            $orderTotal = $order->orderItems->sum(function ($item) {
                return ($item->price * $item->quantity) - $item->discount;
            });

            $orderSummaries[] = [
                'invoice_number' => str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'amount'         => $orderTotal,
            ];

            $totalSales += $orderTotal;
            $itemsSettled += $order->orderItems->count();

            // Count guests - each order has a table with number_of_pax
            if ($order->tableRoom) {
                $guestsServed += $order->tableRoom->number_of_pax ?? 0;
            }
        }

        // If no orders yet, check carts for pending data (for development/testing)
        if (empty($orderSummaries)) {
            $carts = $session->carts()->with(['cartItems', 'tableRoom'])->get();

            foreach ($carts as $cart) {
                // Count items that were placed in carts
                $cartItemsCount = $cart->cartItems->where('placed_order', true)->count();
                if ($cartItemsCount > 0) {
                    // Calculate cart total
                    $cartTotal = $cart->cartItems->where('placed_order', true)->sum(function ($item) {
                        return ($item->price * $item->quantity) - $item->discount;
                    });

                    $orderSummaries[] = [
                        'invoice_number' => 'CART-' . $cart->id,
                        'amount'         => $cartTotal,
                    ];

                    $totalSales += $cartTotal;
                    $itemsSettled += $cartItemsCount;

                    // Count guests from cart tables
                    if ($cart->tableRoom) {
                        $guestsServed += $cart->tableRoom->number_of_pax ?? 0;
                    }
                }
            }
        }

        return [
            'orders'        => $orderSummaries,
            'total_sales'   => $totalSales,
            'items_settled' => $itemsSettled,
            'guests_served' => $guestsServed,
        ];
    }
}
