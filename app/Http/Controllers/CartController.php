<?php
namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Services\CartService;
use App\Services\CashierSessionService;
use Exception;
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

    public function deleteCartItem(int $cartItemId): RedirectResponse
    {
        try {
            $this->cartService->deleteCartItem($cartItemId);

            return redirect()->back()->with('success', 'Cart item deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error deleting cart item.');
        }
    }
}
