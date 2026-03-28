<?php
namespace App\Services;

use App\Enums\PaymentType;
use App\Enums\TableRoomLocation\LocationType;
use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomerPayable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\TableRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected BranchService $branchService,
        protected InventoryStockService $inventoryStockService,
        protected PointsService $pointsService
    ) {
        $this->branchService         = $branchService;
        $this->inventoryStockService = $inventoryStockService;
        $this->pointsService         = $pointsService;
    }

    public function settleBill($payload): mixed
    {
        try {
            return DB::transaction(function () use ($payload) {
                // Check if this is a mixed payment
                if (!empty($payload['is_mixed_payment']) && !empty($payload['payments'])) {
                    return $this->settleMixedPaymentBill($payload);
                }

                $paymentContext = $this->preparePaymentContext($payload);

                $cart = Cart::with(['cartItems', 'tableRoom.tableRoomLocation', 'cashierSession'])->findOrFail($payload['cart_id']);

                info('dito cart ');
                info(json_encode($cart))    ;
                info('dito cart end');
                // Save cart and cart items to order (will throw exception if fails)
                [$order, $changeAmount] = $this->saveCartToOrder($cart, $payload, $paymentContext);
                // Void items are already saved to void_items table when they were voided
                $this->saveCartItemsToOrderItems($cart->cartItems, $order);

                info('save cart items to order' );
                info($order);


                $this->inventoryStockService->deductOrderInventory($order);

                $paymentContext['change_amount'] = $changeAmount;
                $this->recordPayment($order, $paymentContext);

                // Handle points redemption/earning
                $this->handleCustomerPoints($order, $payload, $paymentContext);

                // Update table status if applicable
                if ($cart->table_room_id
                    && $cart->tableRoom
                    && $cart->tableRoom->tableRoomLocation
                    && ! in_array(
                        $cart->tableRoom->tableRoomLocation->location_type,
                        [LocationType::TAKEOUT->value, LocationType::DELIVER->value],
                        true
                    )) {

                    TableRoom::where('id', $cart->table_room_id)->update([
                        'status'        => TableRoomStatusType::AVAILABLE->value,
                        'time_in'       => null,
                        'customer_name' => null,
                        'number_of_pax' => null,
                    ]);
                }

                // Delete cart and its items
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->delete();

                return $order->load([
                    'orderItems',
                    'orderItems.product',
                    'cashierSession.branch',
                    'customer',
                    'payments.paymentMethod',
                ]);
            });
        } catch (\Exception $e) {
             info('Settle bill failed', ['error' => $e->getMessage(), 'cartId' => $payload['cart_id']]);
            Log::error('Settle bill failed', ['error' => $e->getMessage(), 'cartId' => $payload['cart_id']]);
            throw $e;
        }
    }

    private function saveCartToOrder($cart, $payload, array $paymentContext, bool $isMixedPayment = false)
    {
        // Get cart items for processing
        $serviceCharge = $cart->tableRoom ? $cart->tableRoom->calculateServiceCharge($cart) : (float) ($cart->service_charge ?? 0);
        $cartItems     = $cart->cartItems;

        $totalAmount    = $cartItems->sum('amount');
        $lessTax        = $cartItems->sum('less_tax') ?? 0;
        $itemDiscount   = $cartItems->sum('discount_amount') ?? 0;
        $totalDue       = $totalAmount - ($itemDiscount + $lessTax + $cart->total_discount ?? 0);
        $vatableSales   = $cartItems->sum('vatable_sales') ?? 0;
        $vatAmount      = $cartItems->sum('vat_amount') ?? 0;
        $vatExemptSales = $cartItems->sum('vat_exempt_sales') ?? 0;
        $zeroRatedSales = $cartItems->sum('zero_rated_sales') ?? 0;
        $nonVatSales    = $cartItems->sum('non_vat_sales') ?? 0;

        // Get current authenticated cashier
        $cashier = Auth::user();

        // Get cart meta_data and add change and settled_at
        $metaData               = is_array($cart->meta_data) ? $cart->meta_data : [];
        $baseAmountPaid         = $paymentContext['base_amount_paid'];
        $totalWithCharges       = $totalDue + $serviceCharge;
        $changeAmount           = max(0, $baseAmountPaid - $totalWithCharges);

        $metaData['change']     = $changeAmount;
        $metaData['settled_at'] = now();
        if ($cart->source === 'customer') {
            $metaData['guest_checkout'] = array_merge(
                $metaData['guest_checkout'] ?? [],
                [
                    'reference_no' => $cart->reference_no,
                    'submitted_at' => optional($cart->submitted_at)->toIso8601String(),
                    'processed_at' => optional($cart->processed_at)->toIso8601String(),
                ]
            );
        }

        if ($isMixedPayment) {
            $metaData['payment_info'] = [
                'mixed_payment' => true,
                'payment_count' => count($payload['payments'] ?? []),
            ];
        } else {
            $paymentType = $paymentContext['method']->payment_type instanceof PaymentType
                ? $paymentContext['method']->payment_type->value
                : $paymentContext['method']->payment_type;

            $metaData['payment_info'] = [
                'method' => $paymentContext['method']->name,
                'payment_type' => $paymentType,
                'currency' => [
                    'code' => $paymentContext['currency_code'],
                    'symbol' => $paymentContext['currency_symbol'],
                    'exchange_rate' => $paymentContext['exchange_rate'],
                ],
                'amount_in_payment_currency' => $paymentContext['amount_in_payment_currency'],
                'amount_in_default_currency' => $baseAmountPaid,
                'exchange_rate' => $paymentContext['exchange_rate'],
                'change' => $changeAmount,
                'details' => $paymentContext['payment_details'] ?? [],
            ];
        }

        // Get branch_id from cart's cashier session, or fallback to current user's branch
        $branchId = Auth::user()->branch_id;

        if (!$branchId) {
            throw new \Exception('Unable to determine branch_id for invoice generation');
        }

        $invoiceNumber = $this->branchService->getNextInvoiceNumber($branchId);

        // Get customer_id from payload (for credit payments) or from cart
        $customerId = $payload['customer_id'] ?? $cart->customer_id;

        // Resolve an active cashier session: prefer the cart's session, otherwise latest open session for the cashier
        $cashierSessionId =
            $cashier->cashierSessions()
                ->whereNull('closing_time')
                ->latest('id')
                ->value('id');

        if (!$cashierSessionId) {
            throw new \Exception('Unable to settle bill: no active cashier session found.');
        }

        // Create the order
        $order = Order::create([
            'invoice_no'         => $invoiceNumber,
            'cashier_id'         => $cashier->id,
            'cashier_session_id' => $cashierSessionId,
            'bill_no'            => $cart->bill_no,
            'branch_id'          => $branchId,
            'table_room_id'      => $cart->table_room_id,
            'customer_id'        => $customerId,
            'discount_id'        => $cart->discount_id,
            'coupon_id'          => $cart->coupon_id,
            'coupon_code'        => $cart->coupon_code,
            'total_amount'       => $totalAmount,
            'total_discount'     => $payload['total_discount'] ?? 0,
            'less_tax'           => $lessTax,
            'item_discount'      => $itemDiscount,
            'total_due'          => $totalDue,
            'amount_tendered'    => $baseAmountPaid,
            'service_charge'     => $serviceCharge,
            'notes'              => $cart->notes,
            'meta_data'          => $metaData,
            'vatable_sales'      => $vatableSales,
            'vat_amount'         => $vatAmount,
            'vat_exempt_sales'   => $vatExemptSales,
            'zero_rated_sales'   => $zeroRatedSales,
            'non_vat'            => $nonVatSales,
        ]);

        return [$order, $changeAmount];
    }

    private function saveCartItemsToOrderItems($cartItems, $order)
    {
        $this->persistCartItemsAsOrderItems($cartItems, $order);
    }

    // Void items are now saved immediately when voided in CartService::voidCartItem()
    // No longer needed here during settlement

    private function persistCartItemsAsOrderItems($cartItems, $order, bool $forceVoid = false): void
    {
        $idMap = [];

        foreach ($cartItems->sortBy('parent_id') as $cartItem) {
            $parentOrderItemId = null;

            if ($cartItem->parent_id && isset($idMap[$cartItem->parent_id])) {
                $parentOrderItemId = $idMap[$cartItem->parent_id];
            }

            $orderItem = OrderItem::create($this->mapCartItemToOrderItemData(
                $cartItem,
                $order,
                $parentOrderItemId,
                $forceVoid
            ));

            $idMap[$cartItem->id] = $orderItem->id;
        }
    }

    private function mapCartItemToOrderItemData($cartItem, $order, ?int $parentOrderItemId = null, bool $forceVoid = false): array
    {
        return [
            'order_id'             => $order->id,
            'parent_id'            => $parentOrderItemId,
            'product_id'           => $cartItem->product_id,
            'description'          => $cartItem->description,
            'product_packaging_id' => $cartItem->product_packaging_id,
            'quantity'             => $cartItem->quantity,
            'price'                => $cartItem->price,
            'discount_amount'      => $cartItem->discount_amount ?? 0,
            'pax_count'            => $cartItem->pax_count,
            'discounted_pax'       => $cartItem->discounted_pax,
            'vatable_sales'        => $cartItem->vatable_sales ?? 0,
            'vat_exempt_sales'     => $cartItem->vat_exempt_sales ?? 0,
            'vat_amount'           => $cartItem->vat_amount ?? 0,
            'non_vat_sales'        => $cartItem->non_vat_sales ?? 0,
            'less_tax'             => $cartItem->less_tax ?? 0,
            'amount'               => $cartItem->amount,
            'cost'                 => $cartItem->cost,
            'profit'               => $cartItem->profit,
            'order_type'           => $cartItem->order_type,
            'discount_id'          => $cartItem->discount_id,
            'coupon_code'          => $cartItem->coupon_code,
            'sub_total'            => $cartItem->sub_total,
            'is_served'            => $cartItem->is_served,
            'placed_order'         => $cartItem->placed_order,
            'is_void'              => $forceVoid ? true : ($cartItem->is_void ?? false),
            'reason'               => $cartItem->reason,
            'notes'                => $cartItem->notes,
            'meta_data'            => $cartItem->meta_data ?? [],
            'served_by'            => $cartItem->served_by,
            'serving_number'       => $cartItem->serving_number,
            'placed_order_time'    => $cartItem->placed_order_time ?? now(),
            'served_time'          => $cartItem->served_time,
            'batch_number'          => $cartItem->batch_number ?? null,
        ];
    }

    private function preparePaymentContext(array $payload): array
    {
        $paymentMethod = PaymentMethod::findOrFail($payload['payment_method_id']);

        // Check if credit payment requires customer_id
        $paymentType = $paymentMethod->payment_type;
        if ($paymentType instanceof PaymentType) {
            $paymentType = $paymentType->value;
        }

        if (strtolower($paymentType) === 'credit' && empty($payload['customer_id'])) {
            throw new \InvalidArgumentException('Customer ID is required for credit payments.');
        }

        // Check if points payment requires customer_id
        if (strtolower($paymentType) === 'points' && empty($payload['customer_id'])) {
            throw new \InvalidArgumentException('Customer ID is required for points payments.');
        }

        // Use payment method's embedded currency for cash, default currency (rate 1) for others
        $exchangeRate = 1.0;
        $currencyCode = 'PHP';
        $currencySymbol = '₱';

        if ($paymentMethod->isCash() && $paymentMethod->currency_code) {
            $exchangeRate = (float) ($paymentMethod->exchange_rate ?? 1.0);
            $currencyCode = $paymentMethod->currency_code;
            $currencySymbol = $paymentMethod->symbol ?? '₱';
        }

        $paymentDetails = $payload['payment_details'] ?? [];
        if (! is_array($paymentDetails)) {
            $paymentDetails = [];
        }

        $amountInPaymentCurrency = (float) ($payload['amount_in_payment_currency'] ?? $payload['amount_paid'] ?? 0);

        if ($amountInPaymentCurrency <= 0) {
            throw new \InvalidArgumentException('Amount tendered must be greater than zero.');
        }

        $baseAmountPaid = (float) ($payload['computed_amount_paid'] ?? round($amountInPaymentCurrency * $exchangeRate, 2));

        return [
            'method' => $paymentMethod,
            'currency_code' => $currencyCode,
            'currency_symbol' => $currencySymbol,
            'exchange_rate' => $exchangeRate,
            'amount_in_payment_currency' => $amountInPaymentCurrency,
            'base_amount_paid' => $baseAmountPaid,
            'reference_number' => $payload['reference_number'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'payment_details' => $paymentDetails,
            'customer_id' => $payload['customer_id'] ?? null,
            'points_used' => $payload['points_used'] ?? null,
        ];
    }

    private function recordPayment(Order $order, array $paymentContext): Payment
    {
        // Get payment type
        $paymentType = $paymentContext['method']->payment_type;
        if ($paymentType instanceof PaymentType) {
            $paymentType = $paymentType->value;
        }

        // Prepare payment details - add customer info for credit and points payments
        $paymentDetails = $paymentContext['payment_details'] ?? [];
        if ((strtolower($paymentType) === 'credit' || strtolower($paymentType) === 'points') && $order->customer) {
            $paymentDetails['customer_name'] = $order->customer->customer_name;
            $paymentDetails['customer_contact'] = $order->customer->contact_no ?? $order->customer->email ?? '';
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $paymentContext['method']->id,
            'amount' => $paymentContext['base_amount_paid'],
            'amount_in_payment_currency' => $paymentContext['amount_in_payment_currency'],
            'exchange_rate' => $paymentContext['exchange_rate'],
            'change_amount' => $paymentContext['change_amount'] ?? 0,
            'reference_number' => $paymentContext['reference_number'],
            'notes' => $paymentContext['notes'],
            'payment_details' => $paymentDetails,
        ]);

        // If payment type is credit, create a customer payable record

        if (strtolower($paymentType) === 'credit') {
            // Customer ID is already validated in preparePaymentContext
            if (!$order->customer_id) {
                throw new \Exception('Customer ID is missing for credit payment.');
            }

            info('Creating customer payable for order ID: ' . $order->id . ', Customer ID: ' . $order->customer_id);
            CustomerPayable::create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'customer_id' => $order->customer_id,
                'payment_method_id' => $paymentContext['method']->id,
                'currency_id' => $paymentContext['method']->currency_id ?? null,
                'amount_due' => $paymentContext['base_amount_paid'],
                'amount_paid' => 0,
                'balance' => $paymentContext['base_amount_paid'],
                'status' => 'open',
                'due_date' => now()->addDays(30), // Default 30 days credit term
                'notes' => $paymentContext['notes'],
                'meta' => [
                    'currency_code' => $paymentContext['currency_code'],
                    'currency_symbol' => $paymentContext['currency_symbol'],
                    'exchange_rate' => $paymentContext['exchange_rate'],
                    'amount_in_payment_currency' => $paymentContext['amount_in_payment_currency'],
                    'payment_details' => $paymentContext['payment_details'] ?? [],
                ],
            ]);
        }

        return $payment;
    }

    /**
     * Handle customer points redemption and earning based on payment type
     */
    private function handleCustomerPoints(Order $order, array $payload, array $paymentContext): void
    {
        $paymentType = $this->getPaymentType($paymentContext);

        $this->logPointsProcessing($order, $paymentType);

        if ($this->shouldRedeemPoints($paymentType)) {
            $this->redeemCustomerPoints($order, $payload);
            return;
        }

        if ($this->shouldEarnPoints($order, $paymentType)) {
            $this->earnCustomerPoints($order);
        }
    }

    /**
     * Extract payment type from payment context
     */
    private function getPaymentType(array $paymentContext): string
    {
        $paymentType = $paymentContext['method']->payment_type;

        if ($paymentType instanceof PaymentType) {
            return $paymentType->value;
        }

        return $paymentType;
    }

    /**
     * Log points processing initiation
     */
    private function logPointsProcessing(Order $order, string $paymentType): void
    {
        Log::info('handleCustomerPoints called', [
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'payment_type' => $paymentType,
            'total_due' => $order->total_due,
        ]);
    }

    /**
     * Determine if points should be redeemed for this payment
     */
    private function shouldRedeemPoints(string $paymentType): bool
    {
        return strtolower($paymentType) === 'points';
    }

    /**
     * Determine if points should be earned for this order
     */
    private function shouldEarnPoints(Order $order, string $paymentType): bool
    {
        return $order->customer_id && strtolower($paymentType) !== 'credit';
    }

    /**
     * Redeem points from customer's e-wallet
     */
    private function redeemCustomerPoints(Order $order, array $payload): void
    {
        if (!$order->customer_id) {
            throw new \Exception('Customer ID is required for points payment.');
        }

        $customer = \App\Models\Customer::with('eWallet')->findOrFail($order->customer_id);

        if (!$customer->eWallet) {
            throw new \Exception('Customer does not have an e-wallet.');
        }

        $pointsRequired = $payload['points_used'] ?? $order->total_due;
        $ewalletService = app(\App\Services\EWalletService::class);

        try {
            $ewalletService->redeemPoints($customer->eWallet, $pointsRequired, $order);

            Log::info('Points redeemed via e-wallet', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points_redeemed' => $pointsRequired,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to redeem points', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to redeem points. ' . $e->getMessage());
        }
    }

    /**
     * Redeem points for a specific payment in mixed payment scenario
     */
    private function redeemCustomerPointsForPayment(Order $order, array $paymentContext): void
    {
        $customerId = $paymentContext['customer_id'] ?? null;
        $pointsToRedeem = $paymentContext['points_used'] ?? 0;

        if (!$customerId) {
            throw new \Exception('Customer ID is required for points payment.');
        }

        if ($pointsToRedeem <= 0) {
            throw new \Exception('Points to redeem must be greater than zero.');
        }

        $customer = \App\Models\Customer::with('eWallet')->findOrFail($customerId);

        if (!$customer->eWallet) {
            throw new \Exception('Customer does not have an e-wallet.');
        }

        $ewalletService = app(\App\Services\EWalletService::class);

        try {
            $ewalletService->redeemPoints($customer->eWallet, $pointsToRedeem, $order);

            Log::info('Points redeemed via e-wallet (mixed payment)', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points_redeemed' => $pointsToRedeem,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to redeem points (mixed payment)', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to redeem points. ' . $e->getMessage());
        }
    }

    /**
     * Award points to customer for their purchase
     */
    private function earnCustomerPoints(Order $order): void
    {
        Log::info('Attempting to earn points', [
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
        ]);

        $customer = \App\Models\Customer::with('eWallet')->find($order->customer_id);

        if (!$customer) {
            Log::warning('Customer not found', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ]);
            return;
        }

        $this->ensureCustomerHasEWallet($customer, $order);

        $pointsEarned = $this->pointsService->calculatePointsFromAmount($order->total_due);

        $this->logPointsCalculation($order, $customer, $pointsEarned);

        if ($pointsEarned <= 0) {
            Log::info('No points earned - amount too low', [
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'points_earned' => $pointsEarned,
            ]);
            return;
        }

        $this->awardPointsToCustomer($customer, $pointsEarned, $order);
    }

    /**
     * Ensure customer has an e-wallet, create if missing
     */
    private function ensureCustomerHasEWallet(\App\Models\Customer $customer, Order $order): void
    {
        if ($customer->eWallet) {
            return;
        }

        Log::info('Creating e-wallet for existing customer', [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
        ]);

        $customer->eWallet()->create([
            'balance' => 0,
            'earned_points' => 0,
            'redeemed_points' => 0,
            'points_balance' => 0,
            'total_loaded' => 0,
            'total_spent' => 0,
            'is_active' => true,
        ]);

        $customer->load('eWallet');
    }

    /**
     * Log points calculation details
     */
    private function logPointsCalculation(Order $order, \App\Models\Customer $customer, float $pointsEarned): void
    {
        Log::info('Points calculation', [
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'total_due' => $order->total_due,
            'points_earned' => $pointsEarned,
        ]);
    }

    /**
     * Award calculated points to customer via e-wallet service
     */
    private function awardPointsToCustomer(\App\Models\Customer $customer, float $points, Order $order): void
    {
        $ewalletService = app(\App\Services\EWalletService::class);

        try {
            $ewalletService->earnPoints($customer->eWallet, $points, $order);

            Log::info('Points earned via e-wallet', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points_earned' => $points,
                'amount_spent' => $order->total_due,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to award points', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Settle bill with mixed payments (multiple payment methods)
     */
    private function settleMixedPaymentBill(array $payload): mixed
    {
        $cart = Cart::with(['cartItems', 'tableRoom.tableRoomLocation', 'cashierSession'])->findOrFail($payload['cart_id']);

        // Calculate totals
        $serviceCharge = $cart->tableRoom->calculateServiceCharge($cart);
        $cartItems = $cart->cartItems;
        $totalAmount = $cartItems->sum('amount');
        $lessTax = $cartItems->sum('less_tax') ?? 0;
        $itemDiscount = $cartItems->sum('discount_amount') ?? 0;
        $totalDue = $totalAmount - ($itemDiscount + $lessTax + $cart->total_discount ?? 0);
        $totalWithCharges = $totalDue + $serviceCharge;

        // Validate payments array
        $payments = $payload['payments'] ?? [];
        if (empty($payments)) {
            throw new \InvalidArgumentException('Mixed payment requires at least one payment.');
        }

        // Validate total paid covers the bill
        $totalPaid = 0;
        $totalChange = 0;
        $paymentContexts = [];
        $pointsPaymentCustomerId = null;

        foreach ($payments as $index => $paymentData) {
            $context = $this->preparePaymentContext($paymentData);

            // Track customer_id from points payment to set as order customer
            $paymentType = $this->getPaymentType($context);
            if ($paymentType === 'points' && isset($context['customer_id'])) {
                $pointsPaymentCustomerId = $context['customer_id'];
            }

            // Calculate amount applied (excluding change)
            $amountApplied = min($context['base_amount_paid'], $totalWithCharges - $totalPaid);
            $context['amount_applied'] = $amountApplied;

            // Only cash payments can have change
            $isCash = $this->isCashPayment($context['method']);
            if ($isCash) {
                $changeForThisPayment = max(0, $context['base_amount_paid'] - ($totalWithCharges - $totalPaid));
                $context['change_amount'] = $changeForThisPayment;
                $totalChange += $changeForThisPayment;
            } else {
                $context['change_amount'] = 0;
                // Non-cash payments cannot exceed remaining balance
                if ($context['base_amount_paid'] > ($totalWithCharges - $totalPaid)) {
                    throw new \InvalidArgumentException(
                        'Non-cash payment methods cannot have change. Amount must be exact or less than remaining balance.'
                    );
                }
            }

            $totalPaid += $amountApplied;
            $paymentContexts[] = $context;
        }

        if ($totalPaid < $totalWithCharges) {
            throw new \InvalidArgumentException('Total payments do not cover the bill.');
        }

        // Create order with mixed payment flag
        // If any payment has customer_id (points or credit), pass it in the payload for order creation
        if ($pointsPaymentCustomerId) {
            $payload['customer_id'] = $pointsPaymentCustomerId;
        }

        [$order, $_] = $this->saveCartToOrder($cart, $payload, $paymentContexts[0], true);
        $this->saveCartItemsToOrderItems($cart->cartItems, $order);
        $this->inventoryStockService->deductOrderInventory($order);

        // Record all payments
        foreach ($paymentContexts as $context) {
            $this->recordPayment($order, $context);
        }

        // Handle points redemption and earning
        // Points payment handling - redeem points from customer
        $hasPointsPayment = false;
        foreach ($paymentContexts as $context) {
            $paymentType = $this->getPaymentType($context);
            if ($paymentType === 'points' && isset($context['customer_id']) && isset($context['points_used'])) {
                $hasPointsPayment = true;
                // Redeem points for this specific payment
                $this->redeemCustomerPointsForPayment($order, $context);
            }
        }

        // Earn points on non-points payments if customer is set
        // Only earn points once for the entire order, not per payment
        if ($order->customer_id && !$hasPointsPayment) {
            $this->earnCustomerPoints($order);
        }

        // Update table status if applicable
        if ($cart->table_room_id
            && $cart->tableRoom
            && $cart->tableRoom->tableRoomLocation
            && ! in_array(
                $cart->tableRoom->tableRoomLocation->location_type,
                [LocationType::TAKEOUT->value, LocationType::DELIVER->value],
                true
            )) {

            TableRoom::where('id', $cart->table_room_id)->update([
                'status' => TableRoomStatusType::AVAILABLE->value,
                'time_in' => null,
                'customer_name' => null,
                'number_of_pax' => null,
            ]);
        }

        // Delete cart and its items
        CartItem::where('cart_id', $cart->id)->delete();
        $cart->delete();

        return $order->load([
            'orderItems',
            'orderItems.product',
            'cashierSession.branch',
            'customer',
            'payments.paymentMethod',
        ]);
    }

    /**
     * Check if payment method is cash
     */
    private function isCashPayment(PaymentMethod $method): bool
    {
        $paymentType = $method->payment_type;
        if ($paymentType instanceof PaymentType) {
            $paymentType = $paymentType->value;
        }
        return strtolower($paymentType) === 'cash';
    }

}
