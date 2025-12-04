<?php
namespace App\Services;

use Exception;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Models\CashierSession;
use App\Models\ProductPackaging;
use App\Enums\TableRoomStatusType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PreparationItemCollectionResource;

class CartService
{
    protected float $taxRate;

    public function __construct(public Cart $model, public CashierSession $cashierSession, public DiscountService $discountService, public BranchService $branchService)
    {
        $this->model           = $model;
        $this->cashierSession  = $cashierSession;
        $this->discountService = $discountService;
        $this->branchService   = $branchService;
        $this->taxRate         = config('sales.tax_rate');
    }

    public function createCart($payload)
    {
        //DB transaction to maintain consistency
        try {
            return DB::transaction(function () use ($payload) {
                $table = TableRoom::find($payload['table_id']);
                if (! $table) {
                    throw new Exception('Table not found.');
                }

                //find if ther is a cart that is associated with the table
                $cart = Cart::firstOrCreate([
                    'cashier_id'    => Auth::id(),
                    'table_room_id' => $table->id,
                ]);

                $table->update([
                    'status'  => TableRoomStatusType::OCCUPIED->value,
                    'time_in' => now(),
                ]);

                return $cart;
            });

        } catch (Exception $e) {
            throw new Exception('Failed to create cart: ' . $e->getMessage());
        }

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
            foreach ($newSelectedOptions as $selectedOption) {
                foreach ($selectedOption['items'] as $item) {
                    try {
                        $cartItem->children()
                            ->create([
                                'parent_id'            => $cartItem->id,
                                'cart_id'              => $cartItem->cart_id,
                                'product_id'           => $item['product_id'],
                                'product_packaging_id' => $item['product_packaging_id'] ?? null,
                                'quantity'             => $item['quantity'],
                                'price'                => $item['price'],
                                'amount'               => $item['price'] * $item['quantity'],
                                'order_type'           => $orderType,
                                'sub_total'            => $item['price'] * $item['quantity'],
                            ]);
                    } catch (\Throwable $e) {
                        info('Failed on option:', [
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return $cartItem->load('children');
    }

    public function updateCart(Request $request, int $cartId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::find($cartId);

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        // Verify the cart belongs to the current cashier session
        if ($cart->cashier_session_id !== $cashierSession->id) {
            throw new Exception('Unauthorized cart access.');
        }

        // Update cart properties
        $updateData = $request->only(['table_id', 'order_type', 'customer_name', 'customer_phone']);

        // Remove null values to avoid overwriting existing data with null
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            throw new Exception('No valid data provided for update.');
        }

        $cart->update($updateData);

        return $cart->fresh();
    }

    public function mergeCart(Request $request, int $sourceCartId, int $targetTableId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            info('no active cashier seeion');
            throw new Exception('No active cashier session found.');
        }

        // Find the source cart
        $sourceCart = Cart::find($sourceCartId);
        if (! $sourceCart) {
            info('source caret not found');
            throw new Exception('Source cart not found.');
        }

        // Verify the source cart belongs to the current cashier session
        if ($sourceCart->cashier_session_id !== $cashierSession->id) {
            info('unauthorized');
            throw new Exception('Unauthorized source cart access.');
        }

        // Find the target cart by table ID
        $targetCart = Cart::where('table_room_id', $targetTableId)
            ->first();

        if (! $targetCart) {
            warning('Target table cart not found');
            throw new Exception('Target table cart not found.');
        }

        // Get all cart items from source cart
        $sourceCartItems = $sourceCart->cartItems;

        if ($sourceCartItems->isEmpty()) {
            warning('No Items to merge from the source cart');
            throw new Exception('No items to merge from source cart.');
        }

        // Move all cart items from source cart to target cart
        foreach ($sourceCartItems as $cartItem) {
            $cartItem->update([
                'cart_id' => $targetCart->id,
            ]);

            // Also update child items (options/addons) if any
            $cartItem->children()->update([
                'cart_id' => $targetCart->id,
            ]);
        }

        // Recalculate target cart totals
        $this->recalculateCartTotals($targetCart);

        // Delete the source cart since it's now empty
        $sourceCart->delete();

        return $targetCart->fresh();

        // return $targetCart->fresh(['cartItems']);
    }

    protected function recalculateCartTotals(Cart $cart): void
    {
        $cartItems = $cart->cartItems()->whereNull('parent_id')->get();

        $subTotal      = 0;
        $totalDiscount = 0;

        foreach ($cartItems as $item) {
            $subTotal += $item->sub_total;
            $totalDiscount += $item->discount_amount ?? 0;
        }

        $totalAmount = $subTotal - $totalDiscount;

        $cart->update([
            'sub_total'       => $subTotal,
            'discount_amount' => $totalDiscount,
            'total_amount'    => $totalAmount,
        ]);
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

        $cartItems = CartItem::findMany($request->cartItemIds);

        if ($cartItems->isEmpty()) {
            throw new Exception('Cart items is empty.');
        }

        return $cartItems->map(function ($cartItem) use ($request) {
            $modifier = [];

            foreach ($request['modifierOptions'] as $key => $modifierOption) {
                $modifier[$key] = $modifierOption;
            }

            $modifier['specialInstructions'] = $request['specialInstructions'];

            // Check if cart item already has existing modifiers
            $existingMetaData = $cartItem->meta_data ?? [];

            // Append the new modifier to existing meta_data
            $existingMetaData[] = [
                'modifier' => $modifier,
            ];

            $cartItem->update([
                'meta_data' => $existingMetaData,
            ]);
        });
    }

    public function removeModifierFromCartItem(Request $request): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::authCashier()->cashierOpenSession($cashierSession->id)->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cartItem = CartItem::findOrFail($request->cartItemId);

        if (! $cartItem) {
            throw new Exception('Cart item not found.');
        }

        // Get current meta_data and remove the specific modifier
        $currentMetaData = $cartItem->meta_data ?? [];
        $modifierIndex   = $request->modifierIndex;

        if (isset($currentMetaData[$modifierIndex])) {
            unset($currentMetaData[$modifierIndex]);
            // Re-index the array
            $currentMetaData = array_values($currentMetaData);
        }

        return $cartItem->update([
            'meta_data' => $currentMetaData,
        ]);
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

        $cartItem = CartItem::findOrFail($cartItemId);
        if (! $cartItem) {
            throw new Exception('Cart item is empty.');
        }
        return $cartItem->delete();
    }

    public function placeOrder($payload): mixed
    {
        try {
            return DB::transaction(function () use ($payload) {
                // Get the current open cashier session
                $cart        = Cart::findOrFail($payload['cart_id']);
                $orderNumber = null;

                $branchId = $cart->branch_id ?? $cart->cashierSession->branch_id ?? null;
                if ($branchId) {
                    // Update the bill number for the cart
                    $orderNumber = $this->branchService->getNextOrderNumber($branchId);
                }

                if ($cart) {
                    // Update cart items to placed_order
                    $cart->cartItems()
                        ->where('placed_order', false)
                        ->where('batch_number', null)
                        ->update([
                            'placed_order' => true,
                            'batch_number' => $orderNumber,
                        ]);

                    $cart->update([
                        'table_room_id' => $payload['table_id'],
                    ]);

                    // Get only necessary columns to prevent memory exhaustion
                    $cartItems = CartItem::where('cart_id', $cart->id)
                        ->where('batch_number', $orderNumber)
                        ->select('id', 'cart_id', 'product_id', 'quantity', 'price', 'order_type', 'notes', 'meta_data', 'placed_order', 'batch_number')
                        ->get();

                    $newOrderItems = new PreparationItemCollectionResource($cartItems);

                    return [
                        'orderNumber' => $orderNumber,
                        'cart'        => $cart->fresh(['tableRoom']),
                        'placedOrderItems'  => $newOrderItems,
                        'tableRoom'   => $cart->tableRoom,
                        'success'     => true,
                    ];
                }

                return [
                    'success' => false,
                ];
            });
        } catch (\Exception $e) {
            // Transaction automatically rolls back on exception
            throw new Exception('Failed to place order: ' . $e->getMessage());
        }
    }

    public function claimOrder(int $tableId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        // Find the cart for this table in the current session
        $order = Order::where('table_room_id', $tableId)
            ->where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

        if (! $order) {
            throw new Exception('No active order found for this table.');
        }

        if ($order->table_room_id) {
            $tableRoom = $order->tableRoom;
            if ($tableRoom) {
                $tableRoom->update([
                    'status'        => TableRoomStatusType::AVAILABLE->value,
                    'time_in'       => null,
                    'customer_name' => null,
                    'number_of_pax' => null,
                ]);
            }
        }

        return $order->orderItems()
            ->update([
                'is_served' => true,
            ]);
    }

    public function transferOrder(int $sourceTableId, int $targetTableId): mixed
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        // Find the order for the source table
        $order = Order::where('table_room_id', $sourceTableId)
            ->where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

        if (! $order) {
            throw new Exception('No active order found for the source table.');
        }

        // Verify target table exists and is available
        $targetTable = TableRoom::findOrFail($targetTableId);

        if ($targetTable->status !== TableRoomStatusType::AVAILABLE->value) {
            throw new Exception('Target table must be available to transfer the order.');
        }

        // Update the order's table_room_id
        $order->update([
            'table_room_id' => $targetTableId,
        ]);

        // Update source table to available
        $sourceTable = TableRoom::findOrFail($sourceTableId);
        $sourceTable->update([
            'status'        => TableRoomStatusType::AVAILABLE->value,
            'time_in'       => null,
            'customer_name' => null,
            'number_of_pax' => null,
        ]);

        // Update target table to occupied
        $targetTable->update([
            'status'        => TableRoomStatusType::OCCUPIED->value,
            'time_in'       => now(),
            'customer_name' => $sourceTable->customer_name,
            'number_of_pax' => $sourceTable->number_of_pax,
        ]);

        return $order;
    }

    public function updateBillNumber(int $cartId, int $branchId): void
    {
        $branch = Branch::find($branchId);
        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        // Increment the bill number
        $branch->increment('bill_no');
        $newBillNumber = $branch->bill_no;

        // Update the cart with the new bill number
        $cart = Cart::find($cartId);
        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        $cart->update(['bill_no' => $newBillNumber]);
    }
}
