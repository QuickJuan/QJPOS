<?php
namespace App\Services;

use App\Enums\Discount\DiscountType;
use App\Enums\Order\Status;
use App\Enums\TableRoomStatusType;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\Payment;
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
        $session = $request->user()->cashierSessions()->whereNull('closing_time')->latest()->first();

        if (! $session) {
            info('No open session found for cashier ID: ' . Auth::id());
            throw new Exception('No open session found');
        }

        info('session');
        info(json_encode($session));

        $cashDenominationDetails = $request->input('cash_denomination_details', []);

        info('Cash Denomination Details: ');
        info(json_encode($cashDenominationDetails));

        // Frontend sends correct structure with totals already calculated
        $cashDenominationTotal = (float) (
            $cashDenominationDetails['totals']['cash_in_base']
            ?? $cashDenominationDetails['totals']['combined_in_base']
            ?? 0
        );
        $closingCash = ($session->beginning_cash ?? 0) + $cashDenominationTotal;

        // Get all shift statistics in one optimized query
        info('orders totals start fetching');
        $ordersTotals = $this->orderService->getSummarySalesPerShift($session->id);

        info('orders totals fetched');
        info(json_encode($ordersTotals));

        $discounts = $this->orderService->getDiscountBreakdownPerShift($session->id);

        info('discounts processed');
        info(json_encode($discounts));

        $voidOrderItems = $this->orderService->getVoidOrderItemsPerShift($session->id);
        $refundOrders = $this->orderService->getRefundOrdersPerShift($session->id);
        $refundfromOtherShifts = $this->orderService->getRefundAFromOtherShiftOrders($session->cashier_id);

        info('totals processed');

        // Get default currency
        $defaultCurrency = Currency::where('is_default', true)->first();
        $defaultCurrencyId = $defaultCurrency?->id;

        // Get payment breakdown by payment method AND currency
        $paymentsByTypeAndCurrency = Payment::query()
            ->selectRaw('payment_methods.payment_type, payments.currency_id, SUM(payments.amount_in_payment_currency) as amount_tendered, SUM(payments.amount) as pay_amount, SUM(payments.change_amount) as total_change')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $session->id)
            ->where('orders.status', Status::SETTLED->value)
            ->groupBy('payment_methods.payment_type', 'payments.currency_id')
            ->get();

        info('payments processed');
        info(json_encode($paymentsByTypeAndCurrency));

        // Calculate total change given (always in default currency)
        $totalChange = Payment::query()
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $session->id)
            ->where('orders.status', Status::SETTLED->value)
            ->sum('change_amount');

        info('total change: ' . $totalChange);

        // Build payment breakdown, deducting change from default currency cash only
        $paymentBreakdown = [];
        foreach ($paymentsByTypeAndCurrency as $payment) {
            $paymentType = strtolower($payment->payment_type);
            $amount = (float) $payment->total_amount;

            // If this is cash in default currency, subtract change
            if ($paymentType === 'cash' && $payment->currency_id == $defaultCurrencyId) {
                $amount = max(0, $amount - (float) $totalChange);
            }

            $paymentBreakdown[$paymentType] = ($paymentBreakdown[$paymentType] ?? 0) + $amount;
        }

        info('payment breakdown processed');
        info(json_encode($paymentBreakdown));

        // Organize by standard payment method types
        $organizedPaymentBreakdown = [
            'cash' => $paymentBreakdown['cash'] ?? 0,
            'card' => $paymentBreakdown['card'] ?? 0,
            'credit' => $paymentBreakdown['credit'] ?? 0,
            'e-wallet' => $paymentBreakdown['e-wallet'] ?? 0,
            'gift-check' => $paymentBreakdown['gift-check'] ?? 0,
        ];

        info('organized payment breakdown processed');
        info(json_encode($organizedPaymentBreakdown));

        /**
         * break down discount base form discount types from order items
         * use DB query to get sum of discount amount per discount type for a specific shift
         */


        $shiftData = [
            // 'closing_time'              => now(),
            'closing_cash'              => $closingCash,
            'cash_denomination'         => $cashDenominationTotal,
            'cash_denomination_details' => $cashDenominationDetails,
            'meta_data'                 => [
                'total_orders'   => (float) $ordersTotals->total_orders,
                'gross_sales'  => (float) $ordersTotals->total_amount,
                'item_discount'=> (float) $ordersTotals->item_discount,
                'discounts'    => $discounts,
                'service_charge' => (float) $ordersTotals->service_charge,
                'less_tax'     => (float) $ordersTotals->less_tax,
                'net_sales'    => (float) $ordersTotals->total_due,
                'vatable_sales' => (float) $ordersTotals->vatable_sales,
                'vat_amount'    => (float) $ordersTotals->vat_amount,
                'vat_exempt_sales' => (float) $ordersTotals->vat_exempt_sales,
                'zero_rated_sales' => (float) $ordersTotals->zero_rated_sales,
                'non_vat_sales' => (float) $ordersTotals->non_vat_sales,
                'min_invoice_no' => (int)  $ordersTotals->min_invoice_no,
                'max_invoice_no' => (int) $ordersTotals->max_invoice_no,
                'min_bill_no'    => (int) $ordersTotals->min_bill_no,
                'max_bill_no'    => (int) $ordersTotals->max_bill_no,
                'total_sku'      => (int) $ordersTotals->total_sku,
                'total_quantity' => (float) $ordersTotals->total_quantity,
                'void_order_items' => $voidOrderItems,
                'refund_count'    => (int) $refundOrders->total_refunded_orders,
                'refund_amount'    => (float) $refundOrders->total_refunded_amount,
                'refund_from_other_shifts_amount' => (float) $refundfromOtherShifts->total_refunded_amount,
                'refund_from_other_shifts_count' => (int) $refundfromOtherShifts->total_refunded_orders,
                'payment_breakdown' => $organizedPaymentBreakdown,
                'payment_received' => $paymentsByTypeAndCurrency->map(function ($payment) {
                    return [
                        'payment_type' => $payment->payment_type,
                        'currency_id' => $payment->currency_id,
                        'total_amount' => (float) $payment->total_amount,
                    ];
                })->values(),
            ],
        ];
        info('Shift Data: ');
        info(json_encode($shiftData));
        info('End Shift Data');

        // $session->update($shiftData);

        return $session->fresh();
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
        if (!$session) {
            return [];
        }

        // Get order totals using the same method as closeShift
        $ordersTotals = $this->orderService->getTotalOrdersPerShift($session->id);
        $productCounts = $this->orderService->getOrderItemsCount($session->id);
        $voidOrderItems = $this->orderService->getVoidOrderItemsPerShift($session->id);
        $refundOrders = $this->orderService->getRefundOrdersPerShift($session->id);
        $discounts = $this->orderService->getDiscountBreakdownPerShift($session->id);

        // Get default currency
        $defaultCurrency = Currency::where('is_default', true)->first();
        $defaultCurrencyId = $defaultCurrency?->id;

        // Get payment breakdown by payment method AND currency
        $paymentsByTypeAndCurrency = Payment::query()
            ->selectRaw('payment_methods.payment_type, payments.currency_id, SUM(payments.amount) as total_amount')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $session->id)
            ->where('orders.status', Status::SETTLED->value)
            ->groupBy('payment_methods.payment_type', 'payments.currency_id')
            ->get();

        // Calculate total change given (always in default currency)
        $totalChange = Payment::query()
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.cashier_session_id', $session->id)
            ->where('orders.status', Status::SETTLED->value)
            ->sum('change_amount');

        // Build payment breakdown, deducting change from default currency cash only
        $paymentBreakdown = [];
        foreach ($paymentsByTypeAndCurrency as $payment) {
            $paymentType = strtolower($payment->payment_type);
            $amount = (float) $payment->total_amount;

            // If this is cash in default currency, subtract change
            if ($paymentType === 'cash' && $payment->currency_id == $defaultCurrencyId) {
                $amount = max(0, $amount - (float) $totalChange);
            }

            $paymentBreakdown[$paymentType] = ($paymentBreakdown[$paymentType] ?? 0) + $amount;
        }

        // Organize by standard payment method types
        $organizedPaymentBreakdown = [
            'cash' => $paymentBreakdown['cash'] ?? 0,
            'card' => $paymentBreakdown['card'] ?? 0,
            'credit' => $paymentBreakdown['credit'] ?? 0,
            'e-wallet' => $paymentBreakdown['e-wallet'] ?? 0,
            'gift-check' => $paymentBreakdown['gift-check'] ?? 0,
        ];

        // Use meta_data if already closed, otherwise calculate fresh
        if ($session->closing_time && $session->meta_data) {
            return array_merge($session->meta_data, [
                'total_sales' => (float) ($session->meta_data['net_sales'] ?? 0),
                'beginning_cash' => (float) ($session->beginning_cash ?? 0),
                'payment_breakdown' => $organizedPaymentBreakdown,
            ]);
        }

        $data =  [
            'total_orders' => (float) ($ordersTotals->total_orders ?? 0),
            'gross_sales' => (float) ($ordersTotals->total_amount ?? 0),
            'item_discount' => (float) ($ordersTotals->item_discount ?? 0),
            'discounts' => $discounts ?? [],
            'service_charge' => (float) ($ordersTotals->service_charge ?? 0),
            'less_tax' => (float) ($ordersTotals->less_tax ?? 0),
            'net_sales' => (float) ($ordersTotals->total_due ?? 0),
            'total_sales' => (float) ($ordersTotals->total_due ?? 0),
            'vatable_sales' => (float) ($ordersTotals->vatable_sales ?? 0),
            'vat_amount' => (float) ($ordersTotals->vat_amount ?? 0),
            'vat_exempt_sales' => (float) ($ordersTotals->vat_exempt_sales ?? 0),
            'zero_rated_sales' => (float) ($ordersTotals->zero_rated_sales ?? 0),
            'non_vat_sales' => (float) ($ordersTotals->non_vat_sales ?? 0),
            'min_invoice_no' => (int) ($ordersTotals->min_invoice_no ?? 0),
            'max_invoice_no' => (int) ($ordersTotals->max_invoice_no ?? 0),
            'min_bill_no' => (int) ($ordersTotals->min_bill_no ?? 0),
            'max_bill_no' => (int) ($ordersTotals->max_bill_no ?? 0),
            'total_sku' => (int) ($productCounts->total_sku ?? 0),
            'total_quantity' => (float) ($productCounts->total_quantity ?? 0),
            'void_order_items' => $voidOrderItems ?? [],
            'refund_count' => (int) ($refundOrders->total_refunded_orders ?? 0),
            'refund_amount' => (float) ($refundOrders->total_refunded_amount ?? 0),
            'beginning_cash' => (float) ($session->beginning_cash ?? 0),
            'payment_breakdown' => $organizedPaymentBreakdown,
            'session_number' => str_pad($session->id, 4, '0', STR_PAD_LEFT),
        ];
        info(json_encode($data));
        return $data;
    }

    /**
     * Build the expected cash per currency using tendered payments for the session.
     */
    private function calculateExpectedCashByCurrency(CashierSession $session): array
    {
        $defaultCurrency = Currency::default()
            ->select('id', 'code', 'name', 'symbol', 'exchange_rate')
            ->first();

        $defaultCurrencyData = [
            'currency_id' => $defaultCurrency->id ?? null,
            'currency_code' => $defaultCurrency->code ?? 'CASH',
            'currency_name' => $defaultCurrency->name ?? 'Primary Currency',
            'symbol' => $defaultCurrency->symbol ?? '₱',
            'exchange_rate' => (float) ($defaultCurrency->exchange_rate ?? 1),
        ];

        $currencyMap = [];
        $totalChangeInBase = 0.0;

        $payments = Payment::query()
            ->with(['currency', 'paymentMethod'])
            ->whereHas('order', function ($query) use ($session) {
                $query->where('cashier_session_id', $session->id)
                      ->where('status', Status::SETTLED->value);
            })
            ->whereHas('paymentMethod', function ($query) {
                $query->where('payment_type', 'cash');
            })
            ->get();

        foreach ($payments as $payment) {
            $paymentCurrency = $payment->currency ?? $defaultCurrency;
            $currencyData = [
                'currency_id' => $paymentCurrency?->id ?? $defaultCurrencyData['currency_id'],
                'currency_code' => $paymentCurrency?->code ?? $defaultCurrencyData['currency_code'],
                'currency_name' => $paymentCurrency?->name ?? $defaultCurrencyData['currency_name'],
                'symbol' => $paymentCurrency?->symbol ?? $defaultCurrencyData['symbol'],
                'exchange_rate' => (float) ($paymentCurrency?->exchange_rate ?? $defaultCurrencyData['exchange_rate']),
            ];

            $currencyKey = $this->getCurrencyKey($currencyData['currency_id'], $currencyData['currency_code']);
            if (! isset($currencyMap[$currencyKey])) {
                $currencyMap[$currencyKey] = $this->initializeExpectedCurrencyRow($currencyData);
            }

            $amountInCurrency = (float) ($payment->amount_in_payment_currency ?? $payment->amount ?? 0);
            $amountInBase = (float) ($payment->amount ?? $amountInCurrency * $currencyMap[$currencyKey]['exchange_rate']);
            $changeInBase = (float) ($payment->change_amount ?? 0);

            $currencyMap[$currencyKey]['expected_in_currency'] += $amountInCurrency;
            $currencyMap[$currencyKey]['expected_in_base'] += $amountInBase;

            // Change is always dispensed using the default currency
            if ($currencyMap[$currencyKey]['currency_id'] === $defaultCurrencyData['currency_id']) {
                $currencyMap[$currencyKey]['expected_in_currency'] -= $changeInBase;
                $currencyMap[$currencyKey]['expected_in_base'] -= $changeInBase;
            } else {
                $defaultKey = $this->getCurrencyKey($defaultCurrencyData['currency_id'], $defaultCurrencyData['currency_code']);
                if (! isset($currencyMap[$defaultKey])) {
                    $currencyMap[$defaultKey] = $this->initializeExpectedCurrencyRow($defaultCurrencyData);
                }
                $currencyMap[$defaultKey]['expected_in_currency'] -= $changeInBase;
                $currencyMap[$defaultKey]['expected_in_base'] -= $changeInBase;
            }

            $totalChangeInBase += $changeInBase;
        }

        // Always include the default currency so we can factor in beginning cash
        $defaultKey = $this->getCurrencyKey($defaultCurrencyData['currency_id'], $defaultCurrencyData['currency_code']);
        if (! isset($currencyMap[$defaultKey])) {
            $currencyMap[$defaultKey] = $this->initializeExpectedCurrencyRow($defaultCurrencyData);
        }

        $beginningCash = (float) ($session->beginning_cash ?? 0);
        if ($beginningCash !== 0.0) {
            $currencyMap[$defaultKey]['expected_in_currency'] += $beginningCash;
            $currencyMap[$defaultKey]['expected_in_base'] += $beginningCash;
        }

        $expectedTotalBase = array_reduce(
            $currencyMap,
            fn($carry, $currency) => $carry + (float) ($currency['expected_in_base'] ?? 0),
            0.0
        );

        return [
            'base_currency_id' => $defaultCurrencyData['currency_id'],
            'base_currency_code' => $defaultCurrencyData['currency_code'],
            'base_currency_symbol' => $defaultCurrencyData['symbol'],
            'currencies' => array_values($currencyMap),
            'totals' => [
                'expected_cash_in_base' => $expectedTotalBase,
                'change_paid_in_base' => $totalChangeInBase,
            ],
        ];
    }

    /**
     * Merge the expected cash data into the captured denomination breakdown for downstream consumers.
     */
    private function mergeExpectedCashIntoBreakdown(array $actualBreakdown, array $expectedBreakdown): array
    {
        $actualCurrencies = [];
        foreach ($actualBreakdown['currencies'] ?? [] as $currency) {
            $key = $this->getCurrencyKey($currency['currency_id'] ?? null, $currency['currency_code'] ?? null);
            $actualCurrencies[$key] = $currency;
        }

        $expectedCurrencies = [];
        foreach ($expectedBreakdown['currencies'] ?? [] as $currency) {
            $key = $this->getCurrencyKey($currency['currency_id'] ?? null, $currency['currency_code'] ?? null);
            $expectedCurrencies[$key] = $currency;
        }

        $allKeys = array_unique(array_merge(array_keys($actualCurrencies), array_keys($expectedCurrencies)));
        if (empty($allKeys)) {
            $allKeys = array_keys($expectedCurrencies);
        }

        $mergedCurrencies = [];
        foreach ($allKeys as $key) {
            $actual = $actualCurrencies[$key] ?? [];
            $expected = $expectedCurrencies[$key] ?? [];

            $actualAmountCurrency = (float) ($actual['amount_in_currency'] ?? $actual['total_amount'] ?? 0);
            $exchangeRate = (float) ($actual['exchange_rate'] ?? $expected['exchange_rate'] ?? 1);
            $actualAmountBase = (float) ($actual['amount_in_base'] ?? $actual['total_in_base'] ?? $actualAmountCurrency * $exchangeRate);
            $expectedCurrencyAmount = (float) ($expected['expected_in_currency'] ?? 0);
            $expectedBaseAmount = (float) ($expected['expected_in_base'] ?? 0);

            $mergedCurrencies[] = array_merge($actual, [
                'currency_id' => $actual['currency_id'] ?? $expected['currency_id'] ?? null,
                'currency_code' => $actual['currency_code'] ?? $expected['currency_code'] ?? $actualBreakdown['base_currency_code'] ?? 'CASH',
                'currency_name' => $actual['currency_name'] ?? $expected['currency_name'] ?? ($actualBreakdown['base_currency_code'] ?? 'Cash'),
                'symbol' => $actual['symbol'] ?? $expected['symbol'] ?? $actualBreakdown['base_currency_symbol'] ?? '₱',
                'exchange_rate' => $exchangeRate,
                'amount_in_currency' => $actualAmountCurrency,
                'amount_in_base' => $actualAmountBase,
                'expected_in_currency' => $expectedCurrencyAmount,
                'expected_in_base' => $expectedBaseAmount,
                'variance_in_currency' => $actualAmountCurrency - $expectedCurrencyAmount,
                'variance_in_base' => $actualAmountBase - $expectedBaseAmount,
            ]);
        }

        $actualTotalBase = array_reduce(
            $mergedCurrencies,
            fn($carry, $currency) => $carry + (float) ($currency['amount_in_base'] ?? 0),
            0.0
        );

        $expectedTotalBase = $expectedBreakdown['totals']['expected_cash_in_base']
            ?? array_reduce(
                $mergedCurrencies,
                fn($carry, $currency) => $carry + (float) ($currency['expected_in_base'] ?? 0),
                0.0
            );

        $varianceBase = $actualTotalBase - $expectedTotalBase;

        $actualBreakdown['currencies'] = $mergedCurrencies;
        $actualBreakdown['totals']['cash_in_base'] = $actualTotalBase;
        $actualBreakdown['totals']['expected_cash_in_base'] = $expectedTotalBase;
        $actualBreakdown['totals']['variance_in_base'] = $varianceBase;

        return $actualBreakdown;
    }

    private function getCurrencyKey($currencyId, $currencyCode): string
    {
        if ($currencyId !== null) {
            return (string) $currencyId;
        }

        return strtolower($currencyCode ?? 'default');
    }

    private function initializeExpectedCurrencyRow(array $currencyData): array
    {
        return [
            'currency_id' => $currencyData['currency_id'] ?? null,
            'currency_code' => $currencyData['currency_code'] ?? 'CASH',
            'currency_name' => $currencyData['currency_name'] ?? ($currencyData['currency_code'] ?? 'Cash'),
            'symbol' => $currencyData['symbol'] ?? null,
            'exchange_rate' => (float) ($currencyData['exchange_rate'] ?? 1),
            'expected_in_currency' => 0.0,
            'expected_in_base' => 0.0,
        ];
    }
}
