<?php
namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Services\CartService;
use App\Services\CashierSessionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService, protected CashierSessionService $cashierSessionService)
    {
        $this->cartService           = $cartService;
        $this->cashierSessionService = $cashierSessionService;
    }

    public function addToCart(CartRequest $request): RedirectResponse
    {
        try {
            $this->cartService->addToCart($request);
            return redirect()->back()->with('success', 'Item added to cart successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error adding item to cart.');
        }
    }

    public function updateCartItem(Request $request, int $cartItemId): RedirectResponse
    {
        try {
            $this->cartService->updateCartItem($request, $cartItemId);

            return redirect()->back()->with('success', 'Cart item updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error in updating cart item.');
        }
    }

    public function voidCartItem(Request $request, int $cartItemId): RedirectResponse
    {
        try {
            $this->cartService->voidCartItem($request, $cartItemId);

            return redirect()->back()->with('success', 'Cart item removed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error in removing cart item.');
        }
    }

    public function applyDiscountToCartItem(Request $request, int $cartItemId): JsonResponse
    {
        try {
            $result = $this->cartService->applyDiscountToCartItem($request, $cartItemId);

            return response()->json([
                'success' => true,
                'message' => 'Discount applied to cart item successfully.',
                'data'    => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'There was an error applying discount to cart item.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function clearDiscountToCartItem(int $cartItemId): RedirectResponse
    {
        try {
            $this->cartService->clearDiscountToCartItem($cartItemId);

            return redirect()->back()->with('success', 'Discount applied to cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error applying discount to cart item.');
        }
    }

    public function applyModifierToCartItem(Request $request, int $cartItemIds): RedirectResponse
    {
        try {
            $this->cartService->applyModifierToCartItem($request, $cartItemIds);

            return redirect()->back()->with('success', 'Discount applied to cart item successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error applying discount to cart item.');
        }
    }

    public function deleteCartItem(int $cartItemId): RedirectResponse
    {
        try {
            $this->cartService->deleteCartItem($cartItemId);

            return redirect()->back()->with('success', 'Cart item deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error deleting cart item.');
        }
    }

    public function placeOrder(Request $request, int $cartId): RedirectResponse
    {
        try {
            $this->cartService->placeOrder($request, $cartId);

            return redirect()->route('retail-cashier.tables')->with('success', 'Successfully Placed Order.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error placing order.');
        }
    }

    public function settleBill(Request $request, int $cartId): RedirectResponse
    {
        try {
            $this->cartService->settleBill($request, $cartId);

            return redirect()->back()->with('success', 'Bill settled successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error settling the bill.');
        }
    }

    public function calculateDiscount(Request $request): JsonResponse
    {
        try {
            $result = $this->cartService->calculateDiscount($request);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => 'There was an error calculating discount.' . $e->getMessage()], 500);
        }
    }
}
