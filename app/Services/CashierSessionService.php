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
        $session = null;

        $shiftId = $request->input('shift_no');
        if ($shiftId) {
            $session = $this->model->find($shiftId);
        }

        if (! $session) {
            throw new Exception('No open session found');
        }

        $rawBreakdown = $request->input('cash_denomination_details', []);
        $normalizedBreakdown = $this->normalizeCashDenominationDetails($rawBreakdown);

        $expectedCash = $this->calculateExpectedCashByCurrency($session);
        $normalizedBreakdown = $this->mergeExpectedCashIntoBreakdown(
            $normalizedBreakdown,
            $expectedCash
        );

        $cashDenominationTotal = (float) (
            $normalizedBreakdown['totals']['cash_in_base']
            ?? $normalizedBreakdown['grand_total_in_base']
            ?? 0
        );
        $closingCash = ($session->beginning_cash ?? 0) + $cashDenominationTotal;
        $varianceInBase = (float) (
            $normalizedBreakdown['totals']['variance_in_base']
            ?? ($cashDenominationTotal - ($expectedCash['totals']['expected_cash_in_base'] ?? 0))
        );

        $ordersTotals = $this->orderService->getTotalOrdersPerShift($session->id);
        $productCounts = $this->orderService->getOrderItemsCount($session->id);
        $voidOrderItems = $this->orderService->getVoidOrderItemsPerShift($session->id);
        $refundOrders = $this->orderService->getRefundOrdersPerShift($session->id);
        $refundfromOtherShifts = $this->orderService->getRefundAFromOtherShiftOrders($session->cashier_id);
        $discounts = $this->orderService->getDiscountBreakdownPerShift($session->id);


        /**
         * break down discount base form discount types from order items
         * use DB query to get sum of discount amount per discount type for a specific shift
         */




        $session->update([
            'closing_time'              => now(),
            'closing_cash'              => $closingCash,
            'cash_denomination'         => $cashDenominationTotal,
            'cash_denomination_details' => $normalizedBreakdown,
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
                'total_sku'      => (int) $productCounts->total_sku,
                'total_quantity' => (float) $productCounts->total_quantity,
                'void_order_items' => $voidOrderItems,
                'refund_count'    => (int) $refundOrders->total_refunded_orders,
                'refund_amount'    => (float) $refundOrders->total_refunded_amount,
                'refund_from_other_shifts_amount' => (float) $refundfromOtherShifts->total_refunded_amount,
                'refund_from_other_shifts_count' => (int) $refundfromOtherShifts->total_refunded_orders,
                'expected_cash_in_base' => $expectedCash['totals']['expected_cash_in_base'] ?? null,
                'change_paid_in_base' => $expectedCash['totals']['change_paid_in_base'] ?? null,
                'variance' => $varianceInBase,
            ],
        ]);

        return $session->fresh();
    }

    /**
     * Ensure cash denomination details follow the structured multi-currency schema.
     */
    private function normalizeCashDenominationDetails($details): array
    {
        if (! is_array($details)) {
            return $this->convertLegacyDenominations([]);
        }

        if (! isset($details['currencies']) || ! is_array($details['currencies'])) {
            return $this->convertLegacyDenominations($details);
        }

        $currencies = [];
        foreach ($details['currencies'] as $currency) {
            if (! is_array($currency)) {
                continue;
            }

            $denominations = [];
            foreach ($currency['denominations'] ?? [] as $entry) {
                if (! isset($entry['value'], $entry['count'])) {
                    continue;
                }

                $value = (float) $entry['value'];
                $count = (int) $entry['count'];

                if ($count <= 0) {
                    continue;
                }

                $denominations[] = [
                    'id' => $entry['id'] ?? null,
                    'label' => $entry['label'] ?? number_format($value, 2),
                    'value' => $value,
                    'count' => $count,
                    'total' => $entry['total'] ?? $value * $count,
                ];
            }

            $exchangeRate = (float) ($currency['exchange_rate'] ?? 1);
            $amountInCurrency = (float) ($currency['amount_in_currency'] ?? $currency['total_amount'] ?? array_sum(array_column($denominations, 'total')));
            $amountInBase = (float) ($currency['amount_in_base'] ?? $currency['total_in_base'] ?? $amountInCurrency * $exchangeRate);

            $currencies[] = [
                'currency_id' => $currency['currency_id'] ?? null,
                'currency_code' => $currency['currency_code'] ?? '',
                'currency_name' => $currency['currency_name'] ?? '',
                'symbol' => $currency['symbol'] ?? null,
                'exchange_rate' => $exchangeRate,
                'denominations' => array_values($denominations),
                'amount_in_currency' => $amountInCurrency,
                'amount_in_base' => $amountInBase,
            ];
        }

        $cashTotal = array_reduce($currencies, fn($carry, $currency) => $carry + (float) ($currency['amount_in_base'] ?? 0), 0.0);
        $giftCheck = (float) ($details['gift_check_total'] ?? data_get($details, 'totals.gift_check_in_base', 0));
        $grandTotal = $cashTotal + $giftCheck;

        return [
            'base_currency_id' => $details['base_currency_id'] ?? null,
            'base_currency_code' => $details['base_currency_code'] ?? null,
            'base_currency_symbol' => $details['base_currency_symbol'] ?? null,
            'gift_check_total' => $giftCheck,
            'totals' => [
                'cash_in_base' => $cashTotal,
                'gift_check_in_base' => $giftCheck,
                'combined_in_base' => $grandTotal,
                'variance_in_base' => data_get($details, 'totals.variance_in_base'),
            ],
            'currencies' => $currencies,
            'grand_total_in_base' => $grandTotal,
        ];
    }

    /**
     * Support legacy payloads that only contained denomination => count mappings.
     */
    private function convertLegacyDenominations(array $legacy): array
    {
        $denominations = [];

        foreach ($legacy as $denomination => $count) {
            $count = (int) $count;
            $value = (float) $denomination;

            if ($count <= 0 || $value <= 0) {
                continue;
            }

            $denominations[] = [
                'id' => null,
                'label' => number_format($value, 2),
                'value' => $value,
                'count' => $count,
                'total' => $value * $count,
            ];
        }

        $total = array_sum(array_column($denominations, 'total'));

        return [
            'base_currency_id' => null,
            'base_currency_code' => null,
            'base_currency_symbol' => null,
            'gift_check_total' => 0,
            'totals' => [
                'cash_in_base' => $total,
                'gift_check_in_base' => 0,
                'combined_in_base' => $total,
                'variance_in_base' => null,
            ],
            'currencies' => [[
                'currency_id' => null,
                'currency_code' => 'CASH',
                'currency_name' => 'Cash',
                'symbol' => null,
                'exchange_rate' => 1,
                'denominations' => $denominations,
                'amount_in_currency' => $total,
                'amount_in_base' => $total,
            ]],
            'grand_total_in_base' => $total,
        ];
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

        $cashBreakdown = $session->cash_denomination_details ?? [];
        $expectedCashInBase = data_get($cashBreakdown, 'totals.expected_cash_in_base');
        $varianceInBase = data_get($cashBreakdown, 'totals.variance_in_base');

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
            'variance'                  => $varianceInBase ?? ($session->cash_denomination - ($netSales + $serviceCharge)),
            'expected_cash'             => $expectedCashInBase ?? null,
            'cash_denomination_details' => $cashBreakdown,
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
