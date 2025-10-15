<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Requests\CartRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Services\CashierSessionService;

class CartController extends Controller
{
    public function __construct(protected CashierSessionService $cashierSessionService)
    {
        $this->cashierSessionService = $cashierSessionService;
    }

    public function addToCart(CartRequest $request): RedirectResponse
    {
        // Get or create cart for current cashier session
        $cashierSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if (! $cashierSession) {
            return redirect()->back()->with('error', 'No active cashier session found.');
        }

        $cart = Cart::firstOrCreate([
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
        ]);

        // Check if item already exists in cart with same product
        $existingItem = $cart->cartItems()
            ->where('product_id', $request['product_id'])
            ->first();

        $newSelectedOptions = $request['selected_options'] ?? [];
        $quantity = $request['quantity'] ?? 1;
        $totalPrice = $request['total_price'];

        if ($existingItem) {
            // Product exists in cart
            $currentSelectedOptions = $existingItem->selected_options ?? [];

            // Check if selected options are the same
            if (collect($currentSelectedOptions)->pluck('id')->sort()->values()->all() ===
                collect($newSelectedOptions)->pluck('id')->sort()->values()->all()) {
                // Same options - update quantity and subtotal
                $existingItem->update([
                    'quantity'  => $existingItem->quantity + $quantity,
                    'sub_total' => ($existingItem->quantity + $quantity) * $existingItem->price,
                ]);
            } else {
                // Different options - merge selected options
                $mergedOptions = array_merge($currentSelectedOptions, $newSelectedOptions);

                // Recalculate total price based on merged options
                $basePrice = $existingItem->price; // This should be the base product price
                $optionsTotal = collect($mergedOptions)->sum('price');
                $newTotalPrice = ($basePrice + $optionsTotal) * ($existingItem->quantity + $quantity);

                $existingItem->update([
                    'selected_options' => $mergedOptions,
                    'quantity'         => $existingItem->quantity + $quantity,
                    'amount'           => $newTotalPrice / ($existingItem->quantity + $quantity),
                    'sub_total'        => $newTotalPrice,
                ]);
            }
        } else {
            // Product does not exist - create new cart item
            $cart->cartItems()->create([
                'product_id'       => $request['product_id'],
                'quantity'         => $quantity,
                'price'            => $totalPrice / $quantity,
                'amount'           => $totalPrice,
                'sub_total'        => $totalPrice,
                'selected_options' => $newSelectedOptions,
            ]);
        }
        return redirect()->back()->with('success', 'Item added to cart successfully.');
    }

    public function updateCartItem(Request $request, $cartItemId): RedirectResponse
    {
        $cashierSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if (! $cashierSession) {
            return redirect()->back()->with('error', 'No active cashier session found.');
        }

        $cart = Cart::where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

        if (! $cart) {
            return redirect()->back()->with('error', 'Cart not found.');
        }

        $cartItem = $cart->cartItems()->find($cartItemId);

        if (! $cartItem) {
            return redirect()->back()->with('error', 'Cart item not found.');
        }

        $quantity = $request['quantity'] ?? $cartItem->quantity;
        $selectedOptions = $request['selected_options'] ?? $cartItem->selected_options ?? [];

        // Recalculate price based on base product price + options
        $basePrice = $cartItem->price; // This should be the base product price
        $optionsTotal = collect($selectedOptions)->sum('price');
        $totalPrice = ($basePrice + $optionsTotal) * $quantity;

        $cartItem->update([
            'quantity'         => $quantity,
            'selected_options' => $selectedOptions,
            'amount'           => $totalPrice / $quantity,
            'sub_total'        => $totalPrice,
        ]);

        return redirect()->back()->with('success', 'Cart item updated successfully.');
    }

    public function deleteCartItem($cartItemId): RedirectResponse
    {
        $cashierSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if (! $cashierSession) {
            return redirect()->back()->with('error', 'No active cashier session found.');
        }

        $cart = Cart::where('cashier_id', Auth::id())
            ->where('cashier_session_id', $cashierSession->id)
            ->first();

        if (! $cart) {
            return redirect()->back()->with('error', 'Cart not found.');
        }

        $cartItem = $cart->cartItems()->find($cartItemId);

        if (! $cartItem) {
            return redirect()->back()->with('error', 'Cart item not found.');
        }

        $cartItem->delete();

        return redirect()->back()->with('success', 'Cart item deleted successfully.');
    }
}
