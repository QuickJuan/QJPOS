<?php

namespace App\Rules;

use Closure;
use App\Models\Cart;
use Illuminate\Contracts\Validation\ValidationRule;

class CartHasUnplacedItems implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
   public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cart = Cart::find($value);

        if (!$cart) {
            $fail('The cart does not exist.');
            return;
        }

        $unplacedItems = $cart->cartItems()
            ->where('placed_order', false)
            ->exists();

        if (!$unplacedItems) {
            $fail('The cart has no items to place order.');
        }
    }
}
