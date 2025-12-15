<?php
namespace App\Services;

use App\Enums\Discount\DiscountType;
use App\Enums\Order\Status;
use App\Enums\TableRoomStatusType;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\Discount;
use App\Models\TableRoom;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionService
{
    public function __construct(public CashierSession $model, private OrderService $orderService)
    {
        $this->model = $model;
    }

    public function startSession(Request $request): CashierSession
    {

        // Check if user already has an open session
        // $existingSession = $this->model
        //     ->openSession()
        //     ->first();
        $existingSession = CashierSession::where('cashier_id', $request->user()->id)
            ->whereNull('closing_time')
            ->first();

        if ($existingSession) {
            throw new Exception('You already have an open session. Please continue or close it first before starting new one.');
        }

        $session = $this->model->create([
            'business_date'  => now()->toDateString(),
            'branch_id'      => $request->user()->branch_id,
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

    public function closeShift(Request $request)
    {
        $session = null;

        $shiftId = $request->input('shift_no');
        if ($shiftId) {
            $session = $this->model->find($shiftId);
        }

        if (! $session) {
            throw new Exception('No open session found');
        }

        $closingCash = $session->beginning_cash + $request->cash_denomination;

        $ordersTotals = $this->orderService->getTotalOrdersPerShift($session->id);
        $productCounts = $this->orderService->getOrderItemsCount($session->id);
        $voidOrderItems = $this->orderService->getVoidOrderItemsPerShift($session->id);

        info('------- order totals -------');
        info(json_encode($ordersTotals));
        info('------- product counts -------');
        info(json_encode($productCounts));
        info('------- void order items -------');
        info(json_encode($voidOrderItems));
        dd(" testing close shift ");

        $closeSession = $session->update([
            'closing_time'              => now(),
            'closing_cash'              => $closingCash,
            'cash_denomination_details' => $request->cash_denomination_details,
            'cash_denomination'         => $request->cash_denomination,
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
        $billNumber,
        $receiptNumber,
        $generalSettings
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
            'billNumber'         => $billNumber,
            'receiptNumber'      => $receiptNumber,
            'generalSettings'    => $generalSettings,
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
        // Get all orders for this session with relationships where the status
        $settledOrders = $session->orders()->with(['orderItems', 'tableRoom'])->where('status', Status::SETTLED->value)->get();
        $refundOrders  = $session->orders()->with(['orderItems', 'tableRoom'])->where('status', Status::REFUND->value)->get();

        $seniorDiscount = Discount::where('discount_type', DiscountType::SENIOR->value)->first() ?? null;
        $pwdDiscount    = Discount::where('discount_type', DiscountType::PWD->value)->first() ?? null;

        $itemsSettled         = 0;
        $guestsServed         = 0;
        $totalQuantity        = 0;
        $regularDiscountTotal = 0;
        $seniorDiscountTotal  = 0;
        $pwdDiscountTotal     = 0;
        $transactionsCount    = $settledOrders->count();
        $cancelledAmount      = $refundOrders->sum('total_due');
        $grossSales           = $settledOrders->sum('total_amount');
        $netSales             = $settledOrders->sum('total_due');
        $serviceCharge        = $settledOrders->sum('service_charge');
        $nonVatableSales      = $settledOrders->sum('non_vat');
        $vatableSales         = $settledOrders->sum('vatable_sales');
        $vatAmount            = $settledOrders->sum('vat_amount');
        $vatExemptSales       = $settledOrders->sum('vat_exempt_sales');
        $totalLessTax         = $settledOrders->sum('less_tax');

        foreach ($settledOrders as $order) {
            // Calculate order total from orderItems
            $totalQuantity += $order->orderItems->sum('quantity');

            $seniorDiscountTotal += $seniorDiscount ? $order->orderItems
                ->where('discount_id', $seniorDiscount->id)
                ->sum('discount_amount') : 0;

            $pwdDiscountTotal += $pwdDiscount ? $order->orderItems
                ->where('discount_id', $pwdDiscount->id)
                ->sum('discount_amount') : 0;

            $regularDiscountTotal += $seniorDiscount || $pwdDiscount
                ? $order->orderItems
                ->where('discount_id', '!=', $seniorDiscount?->id)
                ->where('discount_id', '!=', $pwdDiscount?->id)
                ->sum('discount_amount')
                : 0;

            $itemsSettled += $order->orderItems->count();

            // Count guests - each order has a table with number_of_pax
            if ($order->tableRoom) {
                $guestsServed += $order->tableRoom->number_of_pax ?? 0;
            }
        }

        $sessionSummary = [
            'gross_sales'               => $grossSales,
            'net_sales'                 => $netSales,
            'items_settled'             => $itemsSettled,
            'guests_served'             => $guestsServed,
            'transactions_count'        => $transactionsCount,
            'sku_count'                 => $itemsSettled,
            'total_quantity'            => $totalQuantity,
            'cancelled_amount'          => $cancelledAmount,
            'regular_discount'          => $regularDiscountTotal,
            'senior_discount'           => $seniorDiscountTotal,
            'pwd_discount'              => $pwdDiscountTotal,
            'non_vat_sales'             => $nonVatableSales,
            'vat_sales'                 => $vatableSales,
            'vat_amount'                => $vatAmount,
            'vat_exempt_sales'          => $vatExemptSales,
            'less_tax'                  => $totalLessTax,
            'service_charge'            => $serviceCharge,
            'session_number'            => str_pad($session->id, 4, '0', STR_PAD_LEFT),
            'beginning_cash'            => $session->beginning_cash ?? 0,
            'closing_cash'              => $session->closing_cash ?? 0,
            'cash_denomination'         => $session->cash_denomination,
            'variance'                  => $session->cash_denomination - ($netSales + $serviceCharge),
            'cash_denomination_details' => $session->cash_denomination_details,
            'or_number_start'           => $settledOrders->first()->invoice_no ?? null,
            'or_number_end'             => $settledOrders->last()->invoice_no ?? null,
            'bill_number_start'         => $settledOrders->first()->bill_no ?? null,
            'bill_number_end'           => $settledOrders->last()->bill_no ?? null,
        ];

        // Save the formatted data to the meta_data column for future use.
        $session->update([
            'meta_data' => $sessionSummary,
        ]);

        return $sessionSummary;
    }
}
