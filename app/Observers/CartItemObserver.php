<?php

namespace App\Observers;

use App\Models\CartItem;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        $this->updateCartServiceCharge($cartItem);
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        $this->updateCartServiceCharge($cartItem);
    }

    /**
     * Handle the CartItem "deleted" event.
     */
    public function deleted(CartItem $cartItem): void
    {
        $this->updateCartServiceCharge($cartItem);
    }

    /**
     * Update the service charge for the cart
     */
    protected function updateCartServiceCharge(CartItem $cartItem): void
    {
        $cart = $cartItem->cart;

        if (!$cart) {
            return;
        }

        // Load the table relationship
        $cart->load('tableRoom');

        if (!$cart->tableRoom) {
            return;
        }

        // Refresh cart items relationship to get latest subtotals
        $cart->load('cartItems');

        // Recalculate service charge based on table location
        $serviceCharge = $cart->tableRoom->calculateServiceCharge($cart);

        // Update the cart's service charge
        $cart->updateQuietly(['service_charge' => $serviceCharge]);

        // Refresh the cart to ensure the updated service_charge is available
        $cart->refresh();
    }
}
