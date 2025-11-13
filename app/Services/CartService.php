<?php
namespace App\Services;

use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CashierSession;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductPackaging;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(public Cart $model, public CashierSession $cashierSession, public DiscountService $discountService)
    {
        $this->model           = $model;
        $this->cashierSession  = $cashierSession;
        $this->discountService = $discountService;
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

        $productPackaging = ProductPackaging::findOrFail($request['product_packaging_id']);

        if (! $productPackaging) {
            throw new Exception('No product packaging found.');
        }

        $newSelectedOptions = $request['selected_options'] ?? [];
        $orderType          = $request['order_type'];
        $withParent         = $request['withParent'];

        $price    = $productPackaging->price;
        $quantity = $request['quantity'] ?? 1;
        $amount   = $productPackaging->price * $quantity;

        $cartItem = $cart->cartItems()
            ->create([
                'product_id'           => $request['product_id'],
                'product_packaging_id' => $request['product_packaging_id'],
                'quantity'             => $quantity,
                'price'                => $price,
                'amount'               => $amount,
                'sub_total'            => $amount,
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

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::find($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        $quantity = $request['quantity'] ?? $cartItem->quantity;

        $basePrice  = $cartItem->price;
        $totalPrice = $basePrice * $quantity;

        return $cartItem->update([
            'quantity'  => $quantity,
            'amount'    => $totalPrice / $quantity,
            'sub_total' => $totalPrice,
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

    public function applyDiscountToCartItem(Request $request): mixed
    {
        $cartItems = CartItem::findMany($request->cartItemIds);

        if ($cartItems->isEmpty()) {
            throw new Exception('Cart items not found.');
        }

        $calculatedDiscountAmounts = $this->discountService->calculateDiscountAmount($request->discount_id, $request->cartItemIds);

        $results = [];

        $discount = Discount::findOrFail($request->discount_id);

        if (! $discount) {
            throw new Exception('Discount not found.');
        }

        foreach ($cartItems as $index => $cartItem) {
            $calculatedDiscountAmount = $calculatedDiscountAmounts[$index];

            $subTotal = $discount->remove_tax
                ? $cartItem->amount - $calculatedDiscountAmount['lessTax'] - $calculatedDiscountAmount['discountAmount']
                : $cartItem->amount - $calculatedDiscountAmount['discountAmount'];

            $cartItem->update([
                'discount_id'      => $discount->id,
                'discount_amount'  => $calculatedDiscountAmount['discountAmount'],
                'vatable_sales'    => $calculatedDiscountAmount['vatableSales'],
                'vat_exempt_sales' => $calculatedDiscountAmount['vatExempt'],
                'vat_amount'       => $calculatedDiscountAmount['taxAmount'],
                'less_tax'         => $calculatedDiscountAmount['lessTax'],
                'amount'           => $cartItem->price * $cartItem->quantity,
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

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

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

        $cart = Cart::authCashier()->cashierSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::findOrFail($cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item is empty.');
        }

        return $cartItem->update([
            'discount'    => null,
            'discount_id' => null,
        ]);
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

    public function calculateDiscount(Request $request)
    {
        $discountService = app(DiscountService::class);
        $taxRate         = config('sales.tax_rate');

        $discountId = $request->input('discount_id');
        $items      = $request->input('items', []);

        if (! $discountId || empty($items)) {
            return [
                'discount_amount' => 0,
                'subtotal'        => 0,
                'tax_amount'      => 0,
                'total'           => 0,
                'item_discounts'  => [],
            ];
        }

        $discountAmount = $discountService->calculateDiscountAmount($discountId, $items);
        $discount       = $discountService->getDiscountById($discountId);

        // Calculate subtotal
        $subtotal = collect($items)->sum(function ($item) {
            $price    = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 1);
            return $price * $quantity;
        });

        // Calculate vatable amount if tax should be removed
        $baseAmount = $discount['remove_tax'] ? $subtotal / $taxRate : $subtotal;

        // Calculate discounted subtotal
        $discountedSubtotal = $baseAmount - $discountAmount;

        // Calculate tax
        $taxAmount = $discount['remove_tax'] ? 0 : $discountedSubtotal * 0.12;

        // Calculate final total
        $total = $discountedSubtotal + $taxAmount;
        info($total);

        // Calculate per-item discount amounts for preview
        $itemDiscounts        = [];
        $totalDiscountPerItem = $discountAmount / count($items);

        foreach ($items as $item) {
            $itemPrice = (float) ($item['price'] ?? 0);
            $quantity  = (int) ($item['quantity'] ?? 1);
            $lineTotal = $itemPrice * $quantity;

            // For preview, distribute discount evenly across items
            $itemDiscountAmount = $totalDiscountPerItem;

            // Calculate discounted price for this item
            $discountedPrice = max(0, $lineTotal - $itemDiscountAmount);

            $itemDiscounts[] = [
                'id'               => $item['id'],
                'original_price'   => round($lineTotal, 2),
                'discounted_price' => round($discountedPrice, 2),
                'discount_amount'  => round($itemDiscountAmount, 2),
                // 'vat_amount'       => ,
            ];
        }

        return [
            'discount_amount'     => round($discountAmount, 2),
            'subtotal'            => round($subtotal, 2),
            'discounted_subtotal' => round($discountedSubtotal, 2),
            'tax_amount'          => round($taxAmount, 2),
            'total'               => round($total, 2),
            'discount_details'    => $discount,
            'item_discounts'      => $itemDiscounts,
        ];
    }
}
