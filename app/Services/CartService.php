<?php
namespace App\Services;

use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CashierSession;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(public Cart $model, public CashierSession $cashierSession)
    {
        $this->model          = $model;
        $this->cashierSession = $cashierSession;
    }

    public function addToCart(Request $request): void
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

        // Check if item already exists in cart with same product
        $existingItem = $cart->cartItems()
            ->where('product_id', $request['product_id'])
            ->where('order_type', $request['order_type'])
            ->first();

        $newSelectedOptions = $request['selected_options'] ?? [];
        $quantity           = $request['quantity'] ?? 1;
        $totalPrice         = $request['total_price'];
        $orderType          = $request['order_type'];

        // if ($existingItem) {
        //     // Product exists in cart
        //     $currentSelectedOptions = $existingItem->selected_options ?? [];

        //     // Check if selected options are the same
        //     if (collect($currentSelectedOptions)->pluck('id')->sort()->values()->all() ===
        //         collect($newSelectedOptions)->pluck('id')->sort()->values()->all()) {
        //         // Same options - update quantity and subtotal
        //         $existingItem->update([
        //             'quantity'  => $existingItem->quantity + $quantity,
        //             'sub_total' => ($existingItem->quantity + $quantity) * $existingItem->price,
        //         ]);
        //     } else {
        //         $mergedOptions = array_merge($currentSelectedOptions, $newSelectedOptions); // Recalculate total price based on merged options
        //         $basePrice     = $existingItem->price;                                      // This should be the base product price
        //         $optionsTotal  = collect($mergedOptions)->sum('price');
        //         $newTotalPrice = ($basePrice + $optionsTotal) * ($existingItem->quantity + $quantity);

        //         $existingItem->update([
        //             'selected_options' => $mergedOptions,
        //             'quantity'         => $existingItem->quantity + $quantity,
        //             'amount'           => $newTotalPrice / ($existingItem->quantity + $quantity),
        //             'sub_total'        => $newTotalPrice,
        //             'order_type'       => $orderType,
        //         ]);
        //     }
        // } else {
        // Product does not exist - create new cart item
        $cart->cartItems()->create([
            'product_id'           => $request['product_id'],
            'parent_id'            => $request['parent_id'],
            'product_packaging_id' => $request['product_packaging_id'],
            'quantity'             => $quantity,
            'price'                => $totalPrice / $quantity,
            'amount'               => $totalPrice,
            'sub_total'            => $totalPrice,
            'selected_options'     => $newSelectedOptions,
            'order_type'           => $orderType,
        ]);
        // }
    }

    public function updateCartItem(Request $request, int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession
            ->openSession()
            ->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()
            ->cashierSession($cashierSession->id)
            ->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = $cart->cartItems()->find($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        $quantity        = $request['quantity'] ?? $cartItem->quantity;
        $selectedOptions = $request['selected_options'] ?? $cartItem->selected_options ?? [];

        $basePrice    = $cartItem->price; // This should be the base product price
        $optionsTotal = collect($selectedOptions)->sum('price');
        $totalPrice   = ($basePrice + $optionsTotal) * $quantity;

        return $cartItem->update([
            'quantity'         => $quantity,
            'selected_options' => $selectedOptions,
            'amount'           => $totalPrice / $quantity,
            'sub_total'        => $totalPrice,
        ]);
    }

    public function voidCartItem(Request $request, int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::find($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        return $cartItem->update([
            'is_void' => true,
            'reason'  => $request->reason,
        ]);
    }

    public function applyDiscountToCartItem(Request $request, int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = $cart->cartItems()->find($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        $discountId     = $request->input('discount_id');
        $discountAmount = $request->input('discount_amount', 0);
        $couponCode     = $request->input('coupon_code');

        // Recalculate subtotal with discount
        $quantity        = $cartItem->quantity;
        $price           = $cartItem->price;
        $selectedOptions = $cartItem->selected_options ?? [];
        $optionsTotal    = collect($selectedOptions)->sum('price');
        $baseTotal       = ($price + $optionsTotal) * $quantity;
        $newSubTotal     = $baseTotal - $discountAmount;

        return $cartItem->update([
            'discount_id' => $discountId,
            'discount'    => $discountAmount,
            'coupon_code' => $couponCode,
            'sub_total'   => max(0, $newSubTotal), // Ensure subtotal doesn't go negative
        ]);
    }

    public function applyModifierToCartItem(Request $request, $cartItemIds)
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItems = $cart->cartItems()->findMany($cartItemIds);

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

    public function deleteCartItem(int $cartItemId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

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
            'is_served'   => true,
            'discount_id' => $request->filled('discountId') ?? null,
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
