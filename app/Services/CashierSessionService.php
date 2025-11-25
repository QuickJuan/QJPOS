<?php
namespace App\Services;

use App\Enums\Discount\DiscountType;
use App\Enums\Receipt\Type;
use App\Enums\TableRoomStatusType;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\Discount;
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
            'branch_id'      => session('active_branch')->id,
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
        $orders         = $session->orders()->with(['orderItems', 'tableRoom'])->get();
        $seniorDiscount = Discount::where('discount_type', DiscountType::SENIOR->value)->first();

        $orderSummaries    = [];
        $totalSales        = 0;
        $itemsSettled      = 0;
        $guestsServed      = 0;
        $netSales          = 0;
        $totalLessTax      = 0;
        $transactionsCount = count($orderSummaries);
        $totalQuantity     = 0;
        $vatableSales      = 0;
        $nonVatableSales   = 0;
        $vatAmount         = 0;
        $regularDiscount   = 0;
        $seniorDiscount    = 0;
        $pwdDiscount       = 0;

        foreach ($orders as $order) {
            // Calculate order total from orderItems
            $orderTotal = $order->orderItems->sum(fn($item) => ($item->price * $item->quantity) - $item->discount);
            $totalQuantity += $order->orderItems->sum('quantity');
            $totalLessTax += $order->orderItems->sum('less_tax');
            $vatableSales += $order->orderItems->sum('vatable_sales');
            $nonVatableSales += $order->orderItems->sum('non_vat_sales');
            $vatAmount += $order->orderItems->sum('vat_amount');
            $netSales += $order->orderItems->sum('amount');
            $seniorDiscount += $order->orderItems
                ->filter(fn($item) =>
                    optional($item->discount)->discount_type === DiscountType::SENIOR->value
                )
                ->sum('discount_amount'); //TODO: Need to show the total amount of senior discount given as well

            $orderSummaries[] = [
                'id'             => $order->id,
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

        // Calculate total quantity and discounts from orders
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $discountAmount = $item->discount_amount ?? $item->discount ?? 0;
                if ($discountAmount > 0) {
                    // Assuming discount based on discount_id indicates senior discount
                    if ($item->discount_id) {
                        $seniorDiscount += $discountAmount;
                    } else {
                        $regularDiscount += $discountAmount;
                    }
                }
            }
        }

        return [
            'orders'             => $orderSummaries,
            'total_sales'        => $totalSales,
            'gross_sales'        => $totalSales,
            'net_sales'          => $netSales,
            'items_settled'      => $itemsSettled,
            'guests_served'      => $guestsServed,
            'transactions_count' => $transactionsCount,
            'sku_count'          => $itemsSettled,
            'total_quantity'     => $totalQuantity,
            'cancelled_count'    => 0,
            'cancelled_amount'   => 0,
            'regular_discount'   => $regularDiscount,
            'senior_discount'    => $seniorDiscount,
            'pwd_discount'       => $pwdDiscount,
            'non_vat_sales'      => $nonVatableSales,
            'vat_sales'          => $vatableSales,
            'vat_amount'         => $vatAmount,
            'less_tax'           => $totalLessTax,
            'previous_reading'   => 0,
            'running_total'      => $totalSales,
            'session_number'     => str_pad($session->id, 4, '0', STR_PAD_LEFT),
            'expected_cash'      => $session->beginning_cash ?? 0,
            'cash_denomination'  => $session->closing_cash ?? 0,
            'or_number'          => 'OR-' . str_pad($session->id, 4, '0', STR_PAD_LEFT),
            'bill_number_start'  => 'BILL-' . str_pad($session->id, 4, '0', STR_PAD_LEFT),
            'bill_number_end'    => 'BILL-' . str_pad($session->id + 1, 4, '0', STR_PAD_LEFT),
        ];
    }
}
