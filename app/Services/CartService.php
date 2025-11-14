<?php
namespace App\Services;

use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CashierSession;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPackaging;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected float $taxRate;

    public function __construct(public Cart $model, public CashierSession $cashierSession, public DiscountService $discountService)
    {
        $this->model           = $model;
        $this->cashierSession  = $cashierSession;
        $this->discountService = $discountService;
        $this->taxRate         = config('sales.tax_rate');
    }

    public function addToCart(Request $request)
    {
        // Get or create cart for current cashier session
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cartAttributes = [
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
        ];

        if ($request->filled('table_id')) {
            $cartAttributes['table_room_id'] = $request->input('table_id');
        }

        $cart = Cart::firstOrCreate($cartAttributes);

        $product          = Product::findOrFail($request['product_id']);
        $productPackaging = ProductPackaging::find($request['product_packaging_id']);

        $newSelectedOptions = $request['selected_options'] ?? [];
        $orderType          = $request['order_type'];
        $withParent         = $request['withParent'];

        $price = $product->multiple_packaging && $productPackaging
            ? $productPackaging->price
            : $product->price;
        $quantity = $request['quantity'] ?? 1;
        $amount   = $price * $quantity;
        $discount = 0;
        $subtotal = $amount - $discount;

        $cartItem = $cart->cartItems()
            ->create([
                'product_id'           => $request['product_id'],
                'product_packaging_id' => $request['product_packaging_id'] ?? null,
                'quantity'             => $quantity,
                'price'                => $price,
                'amount'               => $amount,
                'sub_total'            => $subtotal,
                'order_type'           => $orderType,
            ]);

        if ($withParent) {
            foreach ($newSelectedOptions as $option) {
                try {
                    $cartItem->children()
                        ->create([
                            'parent_id'            => $cartItem->id,
                            'cart_id'              => $cartItem->cart_id,
                            'product_id'           => $option['product_id'],
                            'product_packaging_id' => $option['product_packaging_id'] ?? null,
                            'quantity'             => 1,
                            'price'                => $option['price'],
                            'amount'               => $option['price'],
                            'order_type'           => $orderType,
                            'sub_total'            => $option['price'],
                        ]);
                } catch (\Throwable $e) {
                    info('Failed on option:', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $cartItem->load('children');
    }

    public function updateCartItem(Request $request, int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::find($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        $price    = $cartItem->price;
        $quantity = $request['quantity'] ?? $cartItem->quantity;
        $amount   = $price * $quantity;

        $discountComputation = null;

        if (! empty($cartItem->discount_id)) {
            $results = $this->discountService->calculateDiscountAmount(
                $cartItem->discount_id,
                [$cartItem->id],
                $quantity
            );

            $discountComputation = $results[0] ?? null;
        }

        $discount = $discountComputation
            ? ($discountComputation['discountAmount'] ?? 0) + ($discountComputation['lessTax'] ?? 0)
            : 0;
        $subtotal = $amount - $discount;

        return $cartItem->update([
            'quantity'         => $quantity,
            'amount'           => $amount,
            'sub_total'        => $subtotal,
            'discount_id'      => $cartItem->discount_id ?? null,
            'discount_amount'  => $discountComputation['discountAmount'] ?? 0.00,
            'vatable_sales'    => $discountComputation['vatableSales'] ?? 0.00,
            'vat_exempt_sales' => $discountComputation['vatExempt'] ?? 0.00,
            'vat_amount'       => $discountComputation['taxAmount'] ?? 0.00,
            'less_tax'         => $discountComputation['lessTax'] ?? 0.00,
        ]);
    }

    public function voidCartItem(Request $request, int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::find($cartItemId);

        return $cartItem->update([
            'is_void' => true,
            'reason'  => $request->reason,
        ]);
    }

    public function applyDiscountToCartItem(Request $request): mixed
    {
        $cartItems = CartItem::findMany($request->cartItemIds);

        $calculatedDiscountAmounts = $this->discountService->calculateDiscountAmount($request->discount_id, $request->cartItemIds);

        $results = [];

        $discount = Discount::findOrFail($request->discount_id);

        foreach ($cartItems as $index => $cartItem) {
            $calculatedDiscountAmount = $calculatedDiscountAmounts[$index];
            $amount                   = $cartItem->price * $cartItem->quantity;
            $subTotal                 = $amount - ($calculatedDiscountAmount['lessTax'] + $calculatedDiscountAmount['discountAmount']);

            $cartItem->update([
                'amount'           => $amount,
                'discount_id'      => $discount->id,
                'discount_amount'  => $calculatedDiscountAmount['discountAmount'],
                'vatable_sales'    => $calculatedDiscountAmount['vatableSales'],
                'vat_exempt_sales' => $calculatedDiscountAmount['vatExempt'],
                'vat_amount'       => $calculatedDiscountAmount['taxAmount'],
                'less_tax'         => $calculatedDiscountAmount['lessTax'],
                'sub_total'        => $subTotal,
            ]);

            $results[] = $cartItem;
        }

        return $results;
    }

    public function applyModifierToCartItem(Request $request)
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItems = $cart->cartItems()->findMany($request->cartItemIds);

        if ($cartItems->isEmpty()) {
            throw new Exception('Cart items is empty.');
        }

        return $cartItems->map(function ($cartItem) use ($request) {
            $modifier = [];

            foreach ($request['modifierOptions'] as $key => $modifierOption) {
                $modifier[$key] = $modifierOption;
            }

            $modifier['specialInstructions'] = $request['specialInstructions'];

            $cartItem->update([
                'meta_data' => [
                    [
                        'modifier' => $modifier,
                    ],
                ],
            ]);
        });
    }

    public function clearDiscountToCartItem(int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::findOrFail($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item is empty.');
        }

        $price    = $cartItem->price;
        $quantity = $cartItem->quantity;
        $amount   = $price * $quantity;
        $discount = 0;
        $subtotal = $amount - $discount;

        return $cartItem->update([
            'discount_amount'  => 0.00,
            'discount_id'      => 0.00,
            'vatable_sales'    => 0.00,
            'vat_exempt_sales' => 0.00,
            'vat_amount'       => 0.00,
            'non_vat_sales'    => 0.00,
            'less_tax'         => 0.00,
            'amount'           => $amount,
            'sub_total'        => $subtotal,
        ]);
    }

    public function deleteCartItem(int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::findOrFail($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        return $cartItem->delete();
    }

    public function placeOrder(Request $request, int $cartId): int | RedirectResponse
    {
        // Get the current open cashier session
        $cart = Cart::findOrFail($cartId);

        return $cart->cartItems()->update([
            'placed_order' => true,
            'discount_id'  => $request->filled('discountId') ?? null,
        ]);
    }

    public function settleBill(Request $request, int $cartId): mixed
    {
        $cart = Cart::findOrFail($cartId);

        // Create the order
        $order = Order::create([
            'cashier_id'         => $cart->cashier_id,
            'cashier_session_id' => $cart->cashier_session_id,
            'table_room_id'      => $cart->table_room_id,
            'notes'              => $cart->notes,
            'meta_data'          => [
                'amount_paid'  => $request->amount_paid,
                'total_amount' => $request->total_amount,
                'change'       => $request->amount_paid - $request->total_amount,
                'settled_at'   => now(),
            ],
        ]);

        // Convert cart items to order items
        foreach ($cart->cartItems as $cartItem) {
            OrderItem::create([
                'order_id'             => $order->id,
                'product_id'           => $cartItem->product_id,
                'product_packaging_id' => $cartItem->product_packaging_id,
                'quantity'             => $cartItem->quantity,
                'price'                => $cartItem->price,
                'amount'               => $cartItem->amount,
                'order_type'           => $cartItem->order_type,
                'discount'             => $cartItem->discount,
                'discount_id'          => $cartItem->discount_id,
                'coupon_code'          => $cartItem->coupon_code,
                'sub_total'            => $cartItem->sub_total,
                'is_served'            => true,
                'is_void'              => $cartItem->is_void,
                'reason'               => $cartItem->reason,
                'selected_options'     => $cartItem->selected_options,
            ]);
        }

        // Delete cart items after converting to order items
        $cart->cartItems()->delete();

        // Delete the cart itself
        $cart->delete();

        // If there's a table, mark it as vacant
        if ($cart->table_room_id) {
            $tableRoom = $cart->tableRoom;
            if ($tableRoom) {
                $tableRoom->update([
                    'status'        => TableRoomStatusType::VACANT->value,
                    'time_in'       => null,
                    'customer_name' => null,
                    'number_of_pax' => null,
                ]);
            }
        }

        return $order;
    }
}
