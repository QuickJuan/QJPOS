<?php
namespace App\Services;

use App\Enums\PaymentType;
use App\Enums\TableRoomLocation\LocationType;
use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Currency;
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
        protected InventoryStockService $inventoryStockService
    ) {
        $this->branchService         = $branchService;
        $this->inventoryStockService = $inventoryStockService;
    }

    public function settleBill($payload): mixed
    {
        try {
            return DB::transaction(function () use ($payload) {
                $paymentContext = $this->preparePaymentContext($payload);

                $cart = Cart::with(['cartItems', 'tableRoom.tableRoomLocation', 'cashierSession'])->findOrFail($payload['cart_id']);

                // Save cart and cart items to order (will throw exception if fails)
                [$order, $changeAmount] = $this->saveCartToOrder($cart, $payload, $paymentContext);
                $this->saveCartItemsToOrderItems($cart->cartItems, $order);
                $this->saveVoidCartItemsToOrderItems($cart, $order);

                $this->inventoryStockService->deductOrderInventory($order);

                $paymentContext['change_amount'] = $changeAmount;
                $this->recordPayment($order, $paymentContext);

                // Update table status if applicable
                if ($cart->table_room_id
                    && $cart->tableRoom
                    && $cart->tableRoom->tableRoomLocation
                    && $cart->tableRoom->tableRoomLocation->location_type != LocationType::TAKEOUT->value) {

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
                    'payments.currency',
                ]);
            });
        } catch (\Exception $e) {
             info('Settle bill failed', ['error' => $e->getMessage(), 'cartId' => $payload['cart_id']]);
            Log::error('Settle bill failed', ['error' => $e->getMessage(), 'cartId' => $payload['cart_id']]);
            throw $e;
        }
    }

    private function saveCartToOrder($cart, $payload, array $paymentContext)
    {
        // Get cart items for processing
        $serviceCharge = $cart->tableRoom->calculateServiceCharge($cart);
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
        $paymentType = $paymentContext['method']->payment_type instanceof PaymentType
            ? $paymentContext['method']->payment_type->value
            : $paymentContext['method']->payment_type;

        $metaData['payment_info'] = [
            'method' => $paymentContext['method']->name,
            'payment_type' => $paymentType,
            'currency' => [
                'id' => $paymentContext['currency']->id,
                'code' => $paymentContext['currency']->code,
                'symbol' => $paymentContext['currency']->symbol,
                'exchange_rate' => $paymentContext['currency']->exchange_rate,
                'is_default' => $paymentContext['currency']->is_default,
            ],
            'amount_in_payment_currency' => $paymentContext['amount_in_payment_currency'],
            'amount_in_default_currency' => $baseAmountPaid,
            'exchange_rate' => $paymentContext['currency']->exchange_rate,
            'change' => $changeAmount,
            'details' => $paymentContext['payment_details'] ?? [],
        ];

        // Get branch_id from cart's cashier session, or fallback to current user's branch
        $branchId = $cart->cashierSession?->branch_id ?? $cashier->branch_id ?? $cart->branch_id;

        if (!$branchId) {
            throw new \Exception('Unable to determine branch_id for invoice generation');
        }

        $invoiceNumber = $this->branchService->getNextInvoiceNumber($branchId);

        // Create the order
        $order = Order::create([
            'invoice_no'         => $invoiceNumber,
            'cashier_id'         => $cashier->id,
            'cashier_session_id' => $cashier->cashierSession?->id ?? $cart->cashier_session_id,
            'bill_no'            => $cart->bill_no,
            'table_room_id'      => $cart->table_room_id,
            'customer_id'        => $cart->customer_id,
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

    private function saveVoidCartItemsToOrderItems($cart, $order)
    {
        $voidCartItems = CartItem::where('cart_id', $cart->id)
            ->where('is_void', true)
            ->get();

        if ($voidCartItems->isEmpty()) {
            return;
        }

        $this->persistCartItemsAsOrderItems($voidCartItems, $order, true);
    }

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
            'vatable_sales'        => $cartItem->vatable_sales ?? 0,
            'vat_exempt_sales'     => $cartItem->vat_exempt_sales ?? 0,
            'vat_amount'           => $cartItem->vat_amount ?? 0,
            'non_vat_sales'        => $cartItem->non_vat_sales ?? 0,
            'less_tax'             => $cartItem->less_tax ?? 0,
            'amount'               => $cartItem->amount,
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
        ];
    }

    private function preparePaymentContext(array $payload): array
    {
        $paymentMethod = PaymentMethod::with('currency')->findOrFail($payload['payment_method_id']);

        $currencyId = $payload['currency_id'] ?? $paymentMethod->currency_id;
        $currency = Currency::findOrFail($currencyId);
        $paymentDetails = $payload['payment_details'] ?? [];
        if (! is_array($paymentDetails)) {
            $paymentDetails = [];
        }

        $amountInPaymentCurrency = (float) ($payload['amount_in_payment_currency'] ?? $payload['amount_paid'] ?? 0);

        if ($amountInPaymentCurrency <= 0) {
            throw new \InvalidArgumentException('Amount tendered must be greater than zero.');
        }

        $baseAmountPaid = (float) ($payload['computed_amount_paid'] ?? round($amountInPaymentCurrency * (float) $currency->exchange_rate, 2));

        return [
            'method' => $paymentMethod,
            'currency' => $currency,
            'amount_in_payment_currency' => $amountInPaymentCurrency,
            'base_amount_paid' => $baseAmountPaid,
            'reference_number' => $payload['reference_number'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'payment_details' => $paymentDetails,
        ];
    }

    private function recordPayment(Order $order, array $paymentContext): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $paymentContext['method']->id,
            'currency_id' => $paymentContext['currency']->id,
            'amount' => $paymentContext['base_amount_paid'],
            'amount_in_payment_currency' => $paymentContext['amount_in_payment_currency'],
            'exchange_rate' => $paymentContext['currency']->exchange_rate,
            'change_amount' => $paymentContext['change_amount'] ?? 0,
            'reference_number' => $paymentContext['reference_number'],
            'notes' => $paymentContext['notes'],
            'payment_details' => $paymentContext['payment_details'] ?? [],
        ]);
    }

}
