<?php
namespace App\Services;

use App\Enums\TableRoomStatusType;
use App\Http\Resources\CartResource;
use App\Http\Resources\PreparationItemCollectionResource;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CashierSession;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\TableRoom;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(public Cart $model, public CashierSession $cashierSession, public DiscountService $discountService, public BranchService $branchService)
    {
        $this->model           = $model;
        $this->cashierSession  = $cashierSession;
        $this->discountService = $discountService;
        $this->branchService   = $branchService;
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
                    'cashier_id'         => Auth::id(),
                    'cashier_session_id' => Auth::user()->cashierSession->id,
                    'table_room_id'      => $table->id,
                    'branch_id'          => $payload['branch_id'],
                ]);

                $table->update([
                    'status'        => TableRoomStatusType::OCCUPIED->value,
                    'time_in'       => now(),
                    'number_of_pax' => $payload['pax'],
                    'customer_name' => $payload['guest_name'],
                ]);

                // Load the tableRoom relationship
                $cart->load('tableRoom');

                return $cart;
            });

        } catch (Exception $e) {
            throw new Exception('Failed to create cart: ' . $e->getMessage());
        }

    }

    public function addToCart(Request $request)
    {
        // Verify cashier session is active
        // $cashierSession = $this->cashierSession->openSession()->first();
        // if (! $cashierSession) {
        //     throw new Exception('No active cashier session found.');
        // }



         // Verify user is authenticated

        if (! $request->user()->id) {
            throw new Exception('No active cashier session found.');
        }

        $tableId = $request->input('table_id');
        $table = TableRoom::find($tableId);

        if (! $table) {
            throw new Exception('Table not found.');
        }

        $cart = $table->cart;

        if (!$cart) {

            $payload = [
                'table_id'  => $tableId,
                'branch_id' => $request->user()->branch_id,
                'pax'       => $request->input('pax', 1),
                'guest_name'=> $request->input('guest_name', 'Guest'),
            ];
           $cart =  $this->createCart($payload);
        }

        // Get or create cart for current cashier session
        // $cart = $this->getOrCreateCart($request);

        // Fetch product and packaging information
        $product          = Product::findOrFail($request['product_id']);
        $productPackaging = ProductPackaging::find($request['product_packaging_id']);

        // Extract request data
        $quantity  = $request['quantity'] ?? 1;
        $basePrice = $this->getProductPrice($product, $productPackaging);
        $price     = $this->applyVatToPrice($basePrice, $product);
        $orderType = $request['order_type'];

        // Calculate pricing and tax for main item
        $pricingData = $this->calculatePricingData($basePrice, $quantity, $product);

        // Create main cart item
        $cartItem = $this->createCartItem(
            $cart,
            $product,
            $request,
            $quantity,
            $price,
            $pricingData,
            $orderType
        );

        // Add child items (options/add-ons) if applicable
        if ($request['withParent'] ?? false) {
            $this->addChildItems($cartItem, $request['selected_options'] ?? [], $product, $quantity, $orderType);
        }

        return $cartItem->load('children');
    }

    /**
     * Get or create a cart for the current cashier session
     */
    private function getOrCreateCart( Request $request): Cart
    {
        $cartAttributes = [
            // 'cashier_id'         => $request->user()->id,
            // 'cashier_session_id' => $request->user()->cashierSession->id,
            'branch_id'          => $request->user()->branch_id,
        ];

        if ($request->filled('table_id')) {
            $cartAttributes['table_room_id'] = $request->input('table_id');
        }

        return Cart::firstOrCreate($cartAttributes);
    }

    /**
     * Determine the product price based on packaging
     */
    private function getProductPrice(Product $product, ?ProductPackaging $productPackaging): float
    {
        return $product->multiple_packaging && $productPackaging
            ? (float) $productPackaging->price
            : (float) $product->price;
    }

    /**
     * Calculate pricing data including amount and tax information
     */
    private function calculatePricingData(float $price, int $quantity, Product $product): array
    {
        $priceWithTax = $this->applyVatToPrice($price, $product);
        $amount       = $priceWithTax * $quantity;

        return $this->computeTaxBreakdown($amount, $product);
    }

    /**
     * Apply VAT to price if product is VAT and not inclusive
     */
    private function applyVatToPrice(float $price, Product $product): float
    {
        if ($product->vat_type === 'vat' && ! $product->vat_inclusive) {
            return $price + ($price * ($product->vat_rate / 100));
        }

        return $price;
    }

    /**
     * Calculate tax breakdown (vatable sales, vat amount, etc.)
     */
    private function computeTaxBreakdown(float $amount, Product $product): array
    {
        if ($product->vat_type !== 'vat') {
            return [
                'vatable_sales'    => 0,
                'vat_amount'       => 0,
                'non_vat_sales'    => $amount,
                'vat_exempt_sales' => 0,
                'less_tax'         => 0,
            ];
        }

        $vatable_sales = $amount / (1 + ($product->vat_rate / 100));
        $vat_amount    = $amount - $vatable_sales;

        return [
            'vatable_sales'    => $vatable_sales,
            'vat_amount'       => $vat_amount,
            'non_vat_sales'    => 0,
            'vat_exempt_sales' => 0,
            'less_tax'         => 0,
        ];
    }

    /**
     * Create main cart item with calculated pricing
     */
    private function createCartItem(
        Cart $cart,
        Product $product,
        Request $request,
        int $quantity,
        float $price,
        array $pricingData,
        string $orderType
    ): CartItem {
        $amount = $pricingData['vatable_sales'] + $pricingData['vat_amount'] + $pricingData['non_vat_sales'];

        return $cart->cartItems()->create([
            'product_id'           => $product->id,
            'description'          => $product->receipt_alias,
            'product_packaging_id' => $request['product_packaging_id'] ?? null,
            'quantity'             => $quantity,
            'price'                => $price,
            'amount'               => $amount,
            'sub_total'            => $amount,
            'order_type'           => $orderType,
            'vatable_sales'        => $pricingData['vatable_sales'],
            'vat_exempt_sales'     => $pricingData['vat_exempt_sales'],
            'vat_amount'           => $pricingData['vat_amount'],
            'non_vat_sales'        => $pricingData['non_vat_sales'],
            'less_tax'             => $pricingData['less_tax'],
            'tax_type'             => $product->vat_type,
            'tax_percentage'       => $product->vat_rate,
            'tax_included'         => $product->vat_inclusive,
        ]);
    }

    /**
     * Add child items (options/add-ons) to parent cart item
     */
    private function addChildItems(
        CartItem $parentItem,
        array $selectedOptions,
        Product $parentProduct,
        int $parentQuantity,
        string $orderType
    ): void {
        foreach ($selectedOptions as $selectedOption) {
            foreach ($selectedOption['items'] as $childItemData) {
                $this->createChildItem(
                    $parentItem,
                    $childItemData,
                    $parentProduct,
                    $parentQuantity,
                    $orderType
                );
            }
        }
    }

    /**
     * Create a single child cart item with proper pricing and tax calculation
     */
    private function createChildItem(
        CartItem $parentItem,
        array $childItemData,
        Product $parentProduct,
        int $parentQuantity,
        string $orderType
    ): void {
        try {
            $childPrice = (float) ($childItemData['price'] ?? 0);

            // Skip items with no price
            if ($childPrice <= 0) {
                return;
            }

            $pricingData = $this->calculatePricingData($childPrice, $parentQuantity, $parentProduct);
            $amount      = $pricingData['vatable_sales'] + $pricingData['vat_amount'] + $pricingData['non_vat_sales'];

            $parentItem->children()->create([
                'parent_id'            => $parentItem->id,
                'cart_id'              => $parentItem->cart_id,
                'product_id'           => $childItemData['product_id'],
                'product_packaging_id' => $childItemData['product_packaging_id'] ?? null,
                'quantity'             => $childItemData['quantity'],
                'price'                => $childPrice,
                'amount'               => $amount,
                'order_type'           => $orderType,
                'sub_total'            => $amount,
                'vatable_sales'        => $pricingData['vatable_sales'],
                'vat_exempt_sales'     => $pricingData['vat_exempt_sales'],
                'vat_amount'           => $pricingData['vat_amount'],
                'non_vat_sales'        => $pricingData['non_vat_sales'],
                'less_tax'             => $pricingData['less_tax'],
            ]);
        } catch (\Throwable $e) {
            info('Failed to add child cart item', [
                'message'   => $e->getMessage(),
                'item_data' => $childItemData,
            ]);
        }
    }

    /**
     * Apply discount to cart item and handle tax removal if needed
     *
     * When a discount with remove_tax = true is applied:
     * - vatable_sales becomes 0
     * - vat_amount becomes 0
     * - The full amount is set to vat_exempt_sales
     */
    private function applyDiscountTaxLogic(
        array $discountData,
        float $amount,
        bool $shouldRemoveTax = false
    ): array {
        // If discount removes tax, move all sales to vat_exempt_sales
        if ($shouldRemoveTax) {
            return [
                'vatable_sales'    => 0,
                'vat_exempt_sales' => $amount,
                'vat_amount'       => 0,
                'non_vat_sales'    => 0,
            ];
        }

        // Otherwise, use the discount calculation data
        return [
            'vatable_sales'    => $discountData['vatableSales'] ?? 0,
            'vat_exempt_sales' => $discountData['vatExempt'] ?? 0,
            'vat_amount'       => $discountData['taxAmount'] ?? 0,
            'non_vat_sales'    => 0,
        ];
    }

    /**
     * Calculate the tax amount from a subtotal when VAT is inclusive
     *
     * Formula: tax = subtotal - (subtotal / (1 + taxRate/100))
     */
    private function calculateInclusiveTaxAmount(float $subtotal, float $taxRate): float
    {
        $vatable = $subtotal / (1 + ($taxRate / 100));
        return $subtotal - $vatable;
    }

    public function store($cartItemData)
    {
        $cart = Cart::create($cartData);

        return $cart;
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

        // If there's a discount applied, recalculate with tax logic
        $discountComputation = null;
        $shouldRemoveTax     = false;
        $lessTax             = 0;

        if (! empty($cartItem->discount_id)) {
            $discount        = Discount::find($cartItem->discount_id);
            $shouldRemoveTax = $discount?->remove_tax ?? false;

            $results = $this->discountService->calculateDiscountAmount(
                $cartItem->discount_id,
                [$cartItem->id],
                $quantity
            );

            $discountComputation = $results[0] ?? null;
            $lessTax             = $discountComputation['lessTax'] ?? 0;
        } else {
            // Calculate less_tax from the product's VAT configuration
            $product = $cartItem->product;
            if ($product && $product->vat_type === 'VAT' && $product->vat_inclusive) {
                $lessTax = $this->calculateInclusiveTaxAmount($amount, $product->vat_rate);
            }
        }

        $discount = $discountComputation
            ? ($discountComputation['discountAmount'] ?? 0) + ($discountComputation['lessTax'] ?? 0)
            : 0;
        $subtotal = $amount - $discount;

        // Apply tax logic based on discount's remove_tax flag
        $taxData = $this->applyDiscountTaxLogic($discountComputation ?? [], $subtotal, $shouldRemoveTax);

        return $cartItem->update([
            'quantity'         => $quantity,
            'amount'           => $amount,
            'sub_total'        => $subtotal,
            'discount_id'      => $cartItem->discount_id ?? null,
            'discount_amount'  => $discountComputation['discountAmount'] ?? 0.00,
            'vatable_sales'    => $taxData['vatable_sales'],
            'vat_exempt_sales' => $taxData['vat_exempt_sales'],
            'vat_amount'       => $taxData['vat_amount'],
            'less_tax'         => $lessTax,
            'tax_type'         => $product->vat_type,
            'tax_percentage'   => $product->vat_rate,
            'tax_included'     => $product->vat_inclusive,
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
        $cartItems       = CartItem::findMany($request->cartItemIds);
        $discount        = Discount::findOrFail($request->discount_id);
        $shouldRemoveTax = $discount->remove_tax ?? false;

        $calculatedDiscountAmounts = $this->discountService->calculateDiscountAmount(
            $request->discount_id,
            $request->cartItemIds
        );

        $results = [];

        foreach ($cartItems as $index => $cartItem) {
            $calculatedDiscountAmount = $calculatedDiscountAmounts[$index];
            $amount                   = $cartItem->price * $cartItem->quantity;
            $subTotal                 = $amount - ($calculatedDiscountAmount['lessTax'] + $calculatedDiscountAmount['discountAmount']);

            // Determine less_tax: use from discount calculation if available, otherwise calculate from product VAT
            $lessTax = $calculatedDiscountAmount['lessTax'] ?? 0;

            if ($lessTax == 0 && ! $shouldRemoveTax) {
                $product = $cartItem->product;
                if ($product && $product->vat_type === 'VAT' && $product->vat_inclusive) {
                    $lessTax = $this->calculateInclusiveTaxAmount($subTotal, $product->vat_rate);
                }
            }

            // Apply tax logic based on discount's remove_tax flag
            $taxData = $this->applyDiscountTaxLogic($calculatedDiscountAmount, $subTotal, $shouldRemoveTax);

            $cartItem->update([
                'amount'           => $amount,
                'discount_id'      => $discount->id,
                'discount_amount'  => $calculatedDiscountAmount['discountAmount'],
                'vatable_sales'    => $taxData['vatable_sales'],
                'vat_exempt_sales' => $taxData['vat_exempt_sales'],
                'vat_amount'       => $taxData['vat_amount'],
                'less_tax'         => $lessTax,
                'sub_total'        => $subTotal,
            ]);

            $results[] = $cartItem;
        }

        return $results;
    }

    public function applyModifierToCartItem(Request $request)
    {
        // dd($request->all());
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
            $modifiers = [];

            foreach ($request['modifierOptions'] as $key => $options) {
                foreach ($options as $option) {
                    if (! empty($option['name'])) {
                        $modifiers[] = $option['name'];
                    }
                }
            }

            if (! empty($request['specialInstructions'])) {
                $modifiers[] = $request['specialInstructions'];
            }

            $modifiers = array_values($modifiers);

            // Check if cart item already has existing modifiers
            $existingMetaData  = $cartItem->meta_data ?? [];
            $existingModifiers = $existingMetaData['modifier'] ?? [];

            // Merge existing modifiers with new modifiers
            $allModifiers = array_merge($existingModifiers, $modifiers);

            // Remove duplicates to avoid duplicate entries
            $allModifiers = array_unique($allModifiers);

            // Re-index the array
            $allModifiers = array_values($allModifiers);

            // Re-index the array
            $existingMetaData['modifier'] = $allModifiers;

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
        $currentMetaData = $cartItem->meta_data;
        $modifierValue   = $request->modifierValue;

        if (isset($currentMetaData['modifier']) && is_array($currentMetaData['modifier'])) {
            $key = array_search($modifierValue, $currentMetaData['modifier']);

            if ($key !== false) {
                unset($currentMetaData['modifier'][$key]);
                // Re-index the array to maintain consecutive numeric keys
                $currentMetaData['modifier'] = array_values($currentMetaData['modifier']);
            }
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

        $cart = CartItem::find($cartItemId)->cart;

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

        // Get product for tax calculation
        $product = $cartItem->product;

        // Recalculate tax breakdown when removing discount
        $pricingData = $this->calculatePricingData($price, $quantity, $product);
        $subtotal    = $amount; //$pricingData['vatable_sales'] + $pricingData['vat_amount'] + $pricingData['non_vat_sales'];

        return $cartItem->update([
            'discount_amount'  => 0.00,
            'discount_id'      => null,
            'amount'           => $amount,
            'sub_total'        => $subtotal,
            'vatable_sales'    => $pricingData['vatable_sales'],
            'vat_exempt_sales' => $pricingData['vat_exempt_sales'],
            'vat_amount'       => $pricingData['vat_amount'],
            'non_vat_sales'    => $pricingData['non_vat_sales'],
            'less_tax'         => $pricingData['less_tax'],
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
                        'orderNumber'      => $orderNumber,
                        'cart'             => $cart->fresh(['tableRoom']),
                        'placedOrderItems' => $newOrderItems,
                        'tableRoom'        => $cart->tableRoom,
                        'success'          => true,
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
        $cart = Cart::where('table_room_id', $sourceTableId)
            ->where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

        if (! $cart) {
            throw new Exception('No active cart found for the source table.');
        }

        // Verify target table exists and is available
        $targetTable = TableRoom::findOrFail($targetTableId);

        if ($targetTable->status !== TableRoomStatusType::AVAILABLE->value) {
            throw new Exception('Target table must be available to transfer the cart.');
        }

        // Update the cart's table_room_id
        $cart->update([
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

        return $cart;
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

    public function getCartByTable(int $tableId): ?Cart
    {
        return $this->model->where('table_room_id', $tableId)
            ->with(['cartItems', 'tableRoom'])
            ->first();
    }

    public function getCartByTableAsResource(int $tableId): ?CartResource
    {
        $cart = $this->model->where('table_room_id', $tableId)
            ->with(['cartItems.product', 'cartItems.productPackaging', 'tableRoom'])
            ->first();

        return new CartResource($cart);
    }

    public function printBill(int $cartId)
    {
        $cart = $this->model
            ->with([
                'cartItems',
                'cartItems.product',
                'cashierSession.branch',
                'customer',
                'tableRoom.tableRoomLocation',
            ])
            ->find($cartId);

        if (! $cart) {
            throw new Exception("Cart not foundsss.");
        }

        $branchId = $cart->branch_id;

        if ($branchId) {
            // Update the bill number for the cart
            $billNumber = $this->branchService->getNextBillNumber($branchId);

            // Update Bill No.
            $cart->bill_no        = $billNumber;
            $cart->save();
        }

        return $cart->fresh();
    }

    public function transferItems(Request $request)
    {
        $cashierSession = $this->cashierSession->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        // Find the order for the source table
        $cart = Cart::authCashier()
            ->cashierOpenSession($cashierSession->id)
            ->where('table_room_id', $request->targetTableId)
            ->first();

        if (! $cart) {
            throw new Exception('Cart not found.');
        }

        return CartItem::whereIn('id', $request->cartItemIds)->update(['cart_id' => $cart->id]);
    }
}
