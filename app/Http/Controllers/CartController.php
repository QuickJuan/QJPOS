<?php
namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\OtpVerificationService;
use App\Services\OtpSecretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CartResource;
use Illuminate\Http\RedirectResponse;
use App\Services\CashierSessionService;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\SettleBillRequest;
use App\Http\Requests\TransferItemsRequest;
use App\Http\Requests\CreateTableCartRequest;
use App\Http\Resources\ReceiptOrdersResource;
use App\Http\Requests\ApplyDiscountToCartItemRequest;
use App\Http\Requests\VoidCartItemRequest;
use App\Http\Requests\UpdateServiceChargeRequest;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CashierSessionService $cashierSessionService,
        protected PaymentService $paymentService,
        protected OtpVerificationService $otpVerificationService
    ) {
        $this->cartService           = $cartService;
        $this->cashierSessionService = $cashierSessionService;
        $this->paymentService        = $paymentService;
        $this->otpVerificationService = $otpVerificationService;
    }

    public function create(CreateTableCartRequest $request)
    {
        try {
            $cart = $this->cartService->createCart($request);
            // Redirect to resto index (ordering page) with table ID
            return redirect()->route('resto.index', ['tableId' => $request->input('table_id')])
                ->with('success', 'Order created successfully.');
        } catch (Exception $e) {
            Log::error('Cart creation error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'There was an error creating cart: ' . $e->getMessage());
        }
    }

    // public function addToCart(CartRequest $request): RedirectResponse
    public function addToCart(Request $request): RedirectResponse
    {
        try {
            $this->cartService->addToCart($request);
            return redirect()->back()->with('success', 'Item added to cart successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?: 'There was an error adding item to cart.');
        }
    }

    public function updateCart(Request $request, int $cartId): RedirectResponse
    {
        try {
            if (! $cartId) {
                return redirect()->back()->with('error', 'Cart ID is empty.');
            }

            $this->cartService->updateCart($request, $cartId);

            return redirect()->back()->with('success', 'Cart updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error updating cart.');
        }
    }

    public function updateServiceCharge(UpdateServiceChargeRequest $request, int $cartId): RedirectResponse|JsonResponse
    {
        try {
            $cart = $this->cartService->updateServiceCharge($request, $cartId);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service charge updated successfully.',
                    'cart'    => $cart,
                ]);
            }

            return redirect()->back()->with('success', 'Service charge updated successfully.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'There was an error updating service charge.',
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage() ?: 'There was an error updating service charge.');
        }
    }

    public function mergeCart(Request $request): RedirectResponse
    {
        try {
            $sourceCartId  = $request->input('source_cart_id');
            $targetTableId = $request->input('target_table_id');

            if (! $sourceCartId || ! $targetTableId) {
                return redirect()->back()->with('error', 'Source cart ID and target table ID are required.');
            }

            $mergedCart = $this->cartService->mergeCart($request, $sourceCartId, $targetTableId);

            return redirect()->back()->with('success', 'Cart items merged successfully and source cart is deleted.');
        } catch (Exception $e) {
            \Log::error('Cart merge error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error merging cart items: ' . $e->getMessage());
        }
    }

    public function updateCartItem(Request $request, int $cartItemId): RedirectResponse
    {
        try {
            if (! $cartItemId) {
                info('Cart item ID is empty in updateCartItem');
                return redirect()->back()->with('error', 'Cart item ID is empty.');
            }

            $this->cartService->updateCartItem($request, $cartItemId);

            return redirect()->back()->with('success', 'Cart item updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error in updating cart item.');
        }
    }

    public function voidCartItem(VoidCartItemRequest $request, int $cartItemId): RedirectResponse
    {
        try {
            if (! $cartItemId) {
                return redirect()->back()->with('error', 'Cart item ID is empty.');
            }

            $validated = $request->validated();

            $this->otpVerificationService->verify($request->user(), $validated['otp_code']);

            $this->cartService->voidCartItem(
                reason: $validated['reason'],
                cartItemId: $cartItemId,
                user: $request->user()
            );

            return redirect()->back()->with('success', 'Cart item removed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error in removing cart item.');
        }
    }

    public function applyDiscountToCartItem(ApplyDiscountToCartItemRequest $request): RedirectResponse
    {
        try {
            $this->cartService->applyDiscountToCartItem($request);

            return redirect()->back()->with('success', 'Discount applied to cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error applying discount to cart item.');
        }
    }

    public function clearDiscountToCartItem(int $cartItemId): RedirectResponse
    {
        try {
            if (! $cartItemId) {
                return redirect()->back()->with('error', 'Cart item ID is empty.');
            }

            $this->cartService->clearDiscountToCartItem($cartItemId);

            return redirect()->back()->with('success', 'Discount applied to cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error applying discount to cart item.');
        }
    }

    public function applyModifierToCartItem(Request $request): RedirectResponse
    {
        try {
            $this->cartService->applyModifierToCartItem($request);

            return redirect()->back()->with('success', 'Modifier applied to cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error applying modifier to cart item.');
        }
    }

    public function applyAddOnToCartItem(Request $request): RedirectResponse
    {
        try {
            $this->cartService->applyAddOnToCartItem($request);

            return redirect()->back()->with('success', 'Add-on added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error adding add-on to cart item.');
        }
    }

    public function removeModifierFromCartItem(Request $request): RedirectResponse
    {
        try {
            $this->cartService->removeModifierFromCartItem($request);

            return redirect()->back()->with('success', 'Modifier removed from cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error removing modifier from cart item.');
        }
    }

    public function deleteCartItem(int $cartItemId): RedirectResponse|JsonResponse
    {
        try {
            if (! $cartItemId) {
                return redirect()->back()->with('error', 'Cart item ID is empty.');
            }

            $cartItem = CartItem::findOrFail($cartItemId);

            // If order is placed, require OTP approval
            if ($cartItem->placed_order) {
                // Get eligible approvers (manager, supervisor, OIC, admin, super admin)
                $eligibleRoles = ['manager', 'supervisor', 'oic', 'admin', 'super admin'];
                $approvers = User::whereHas('roles', function ($query) use ($eligibleRoles) {
                    $query->whereIn('name', $eligibleRoles);
                })
                ->where('branch_id', auth()->user()->branch_id)
                ->where('otp_enabled', true)
                ->select('id', 'name', 'email')
                ->get();

                // Return JSON response with approvers list for modal
                return response()->json([
                    'success' => false,
                    'requires_approval' => true,
                    'cart_item_id' => $cartItemId,
                    'approvers' => $approvers,
                    'message' => 'This item is from a placed order. OTP approval from an authorized user is required to delete it.',
                ], 422);
            }

            // If not a placed order, delete directly
            $this->cartService->deleteCartItem($cartItemId);

            return redirect()->back()->with('success', 'Cart item deleted successfully.');
        } catch (Exception $e) {
            Log::error('Delete cart item error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error deleting cart item.');
        }
    }

    /**
     * Delete a cart item with OTP approval from an authorized user
     */
    public function deleteCartItemWithApproval(Request $request): JsonResponse
    {
        try {
            // Log the incoming request data to see what's being sent
            Log::info('Delete cart item with approval - Request data', [
                'all_data' => $request->all(),
                'has_reason' => $request->has('reason'),
                'reason_value' => $request->input('reason'),
            ]);

            $validated = $request->validate([
                'cart_item_id' => 'required|integer|exists:cart_items,id',
                'approver_email' => 'required|email|exists:users,email',
                'otp_code' => 'required|string|digits:6',
                'reason' => 'nullable|string|max:500',
            ]);

            $cartItem = CartItem::findOrFail($validated['cart_item_id']);
            $approver = User::where('email', $validated['approver_email'])->firstOrFail();

            // Verify approver has OTP enabled
            if (!$approver->otp_enabled || !$approver->otp_secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected approver does not have OTP enabled.',
                ], 422);
            }

            // Verify approver is in the same branch
            if ($approver->branch_id !== auth()->user()->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approver must be from the same branch.',
                ], 422);
            }

            // Verify OTP code using approver's secret
            if (!OtpSecretService::verifyCode($approver->otp_secret, $validated['otp_code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code. Please try again.',
                ], 422);
            }

            // Add reason validation if provided
            $reason = $validated['reason'] ?? 'Item deleted by ' . $approver->name;

            Log::info('About to delete cart item', [
                'cart_item_id' => $validated['cart_item_id'],
                'reason_from_validated' => $validated['reason'] ?? null,
                'final_reason' => $reason,
            ]);

            // Delete the cart item with approver info (will save to void_items if placed order)
            $this->cartService->deleteCartItem($validated['cart_item_id'], $approver, $reason);

            // Log the approval action
            Log::info('Cart item deleted with OTP approval', [
                'cart_item_id' => $validated['cart_item_id'],
                'deleted_by' => auth()->id(),
                'approved_by' => $approver->id,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart item deleted successfully with approval.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Delete cart item with approval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was an error deleting the cart item.',
            ], 500);
        }
    }

    /**
     * Get eligible approvers for a cart item deletion
     */
    public function getApproversForItem(int $cartItemId): JsonResponse
    {
        try {
            $cartItem = CartItem::findOrFail($cartItemId);

            // If not a placed order, no approvers needed
            if (!$cartItem->placed_order) {
                return response()->json([
                    'success' => true,
                    'approvers' => [],
                    'message' => 'No approvers needed for non-placed orders.',
                ], 200);
            }

            // Get eligible approvers (admin, manager, supervisor, OIC)
            $eligibleRoles = ['admin', 'manager', 'supervisor', 'oic'];
            $approvers = User::whereHas('roles', function ($query) use ($eligibleRoles) {
                $query->whereIn('name', $eligibleRoles);
            })
                ->where('branch_id', auth()->user()->branch_id)
                ->where('otp_enabled', true)
                ->select('id', 'name', 'email')
                ->get();

            return response()->json([
                'success' => true,
                'approvers' => $approvers,
                'message' => 'Approvers retrieved successfully.',
            ], 200);

        } catch (Exception $e) {
            Log::error('Get approvers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was an error retrieving approvers.',
            ], 500);
        }
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $payload = [
                'cart_id'  => $request->input('cart_id'),
                'table_id' => $request->input('table_id'),
                'served_by' => $request->input('served_by'),
                'serving_number' => $request->input('serving_number'),
            ];

            $response = $this->cartService->placeOrder($payload);

            if (! $response["success"]) {
                return response()->json([
                    'success' => false,
                    'message' => 'There was an error placing order.',
                ], 400);
            }

            return response()->json($response, 200);

        } catch (Exception $e) {
            Log::error('Place order error: ' . $e->getMessage());
            return response()->json([
                'message' => 'There was an error placing order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reprintPlacedOrder(Request $request, int $batchNumber): JsonResponse
    {
        try {
            $served = $request->query('served');
            $response = $this->cartService->getPlacedOrderByBatchNumber($batchNumber, $served);

            return response()->json($response, 200);
        } catch (Exception $e) {
            Log::error('Reprint order error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'Failed to re-print order.',
            ], 404);
        }
    }

    public function listReprintBatches(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 30);
            $branchId = (int) ($request->query('branchId')
                ?? $request->query('branch_id')
                ?? (session('active_branch')->id ?? 0));

            $batches = $this->cartService->getRecentPlacedBatches($branchId ?: null, $limit);

            return response()->json([
                'success' => true,
                'data' => $batches,
            ], 200);
        } catch (Exception $e) {
            Log::error('List reprint batches error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load recent batches.',
            ], 500);
        }
    }

    public function showSettlePayment(Cart $cart): Response
    {
        $cart->load([
            'cartItems',
            'cartItems.product',
            'cartItems.productPackaging',
            'cartItems.children',
            'cartItems.children.product',
            'cartItems.children.productPackaging',
            'cartItems.servedBy:id,name',
            'cashier',
            'cashierSession',
            'branch',
            'customer.eWallet',
            'tableRoom.tableRoomLocation',
        ]);

        return Inertia::render('Resto/SettlePayment', [
            'cart' => new CartResource($cart),
        ]);
    }

    public function settleBill(SettleBillRequest $request): JsonResponse | RedirectResponse
    {
        try {
            info('Attempting to settle bill for cart ID: ' . $request->input('cart_id'));
            $order = $this->paymentService->settleBill($request->validated());

            info('Bill settled successfully for cart ID: ' . $request->input('cart_id'));

            // return redirect()->back()->with('success', 'Bill settled successfully.');
            return response()->json([
                'success' => true,
                'message' => 'Bill settled successfully.',
                'data'    => new ReceiptOrdersResource($order),
            ], 200);
        } catch (Exception $e) {
            info('Settle bill error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was an error settling the bill.',
                'error'   => $e->getMessage(),
            ], 500);
            // return redirect()->back()->with('error', 'There was an error settling the bill.');
        }
    }

    public function transferItems(TransferItemsRequest $request): RedirectResponse
    {
        try {
            $this->cartService->transferItems($request);

            return redirect()->back()->with('success', 'Items transfered successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error transferring cart items.');
        }
    }

    public function claimOrder(Request $request, int $tableId): RedirectResponse
    {
        try {
            if (! $tableId) {
                return redirect()->back()->with('error', 'Table ID is empty.');
            }

            $this->cartService->claimOrder($tableId);

            return redirect()->back()->with('success', 'Order claimed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error claiming the order: ' . $e->getMessage());
        }
    }

    public function transferOrder(Request $request, int $tableId): RedirectResponse
    {
        try {
            if (! $tableId) {
                return redirect()->back()->with('error', 'Source table ID is empty.');
            }

            if (! $request->filled('target_table_id')) {
                return redirect()->back()->with('error', 'Target table ID is required.');
            }



            $this->cartService->transferOrder($tableId, $request->input('target_table_id'));

            return redirect()->back()->with('success', 'Order transferred successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error transferring the order: ' . $e->getMessage());
        }
    }

    public function updateBillNumber(Request $request, int $cartId): RedirectResponse
    {
        try {
            if (! $cartId) {
                return redirect()->back()->with('error', 'Cart ID is empty.');
            }

            $branchId = $request->input('branch_id') ?? session('active_branch')->id;
            if (! $branchId) {
                return redirect()->back()->with('error', 'Branch ID is required.');
            }

            $this->cartService->updateBillNumber($cartId, $branchId);

            return redirect()->back()->with('success', 'Bill number updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error updating bill number: ' . $e->getMessage());
        }
    }

    public function printBill(int $cartId): JsonResponse
    {
        try {
            $cart = $this->cartService->printBill($cartId);

            return response()->json(new CartResource($cart), 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'There was an error in fetching print bill data' . $e->getMessage()], 400);
        }
    }

    public function getServers(): JsonResponse
    {
        try {
            // Fetch users with server or waiter role using Spatie Permission
            $servers = \App\Models\User::role(['Server', 'Waiter'])
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $servers,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get servers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch servers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search for product by barcode and optionally add to cart
     */
    public function searchBarcode(Request $request): JsonResponse
    {
        try {
            $barcode = $request->input('barcode');

            if (empty($barcode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode is required.',
                ], 400);
            }

            $result = $this->cartService->searchByBarcode($barcode);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with barcode: ' . $barcode,
                ], 404);
            }

            // If table_id and quantity are provided, add to cart automatically
            if ($request->filled('table_id') && $request->filled('quantity')) {
                $addToCartRequest = new Request([
                    'product_id' => $result['product_id'],
                    'product_packaging_id' => $result['product_packaging_id'],
                    'quantity' => $request->input('quantity'),
                    'table_id' => $request->input('table_id'),
                    'order_type' => $request->input('order_type', 'dine_in'),
                    'withParent' => false,
                ]);
                $addToCartRequest->setUserResolver($request->getUserResolver());

                $cartItem = $this->cartService->addToCart($addToCartRequest);

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'data' => $result,
                    'cart_item' => $cartItem,
                ], 200);
            }

            // Just return the search result
            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (Exception $e) {
            Log::error('Barcode search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search barcode.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCustomer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cart_id' => 'required|integer|exists:carts,id',
                'customer_id' => 'nullable|integer|exists:customers,id',
            ]);

            $cart = Cart::findOrFail($request->input('cart_id'));
            $cart->update([
                'customer_id' => $request->input('customer_id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully.',
                'cart' => new CartResource($cart),
            ], 200);
        } catch (Exception $e) {
            Log::error('Update customer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
