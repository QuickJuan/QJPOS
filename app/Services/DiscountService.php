<?php
namespace App\Services;

use App\Enums\Discount\TypeEnum;
use App\Http\Resources\DiscountResource;
use App\Models\CartItem;
use App\Models\Discount;

class DiscountService
{

    public function __construct(public Discount $discount)
    {
        $this->discount = $discount;
    }

    /**
     * Calculate discount amount for given items
     * discountId: ID of the discount to apply
     * items: array of ids from cart items or order items
     */
    public function calculateDiscountAmount(int $discountId, array $itemIds, ?float $quantity = null)
    {
        $discount = $this->discount->find($discountId);

        $results = [];

        foreach ($itemIds as $itemId) {
            $cartItem = CartItem::find($itemId);

            if ($cartItem) {
                $itemQuantity = $quantity ?? $cartItem->quantity;
                $amount       = $itemQuantity * $cartItem->price;

                $results[] = $discount['remove_tax']
                    ? $this->calculateDiscountWithTaxRemoval($discount, $amount)
                    : match ($discount['type']) {
                    'percentage' => $this->calculatePercentageDiscount($discount, $amount),
                    'fixed', 'amount' => $this->calculateFixedDiscount($discount, $amount),
                    default      => $this->calculateFixedDiscount($discount, $amount),
                };
            }
        }

        return $results;
    }

    /**
     * Calculate discount with tax removal
     * Extracts tax from price, applies discount to net amount, then adds tax back to discount
     */
    private function calculateDiscountWithTaxRemoval(Discount $discount, float $amount): array
    {
        $taxRate      = config('sales.tax_rate');
        $vatExempt    = $amount / $taxRate;
        $taxAmount    = 0;
        $vatableSales = 0;
        $lessTax      = $amount - $vatExempt;

        $discount->type == TypeEnum::PERCENTAGE->value
            ? $discountAmount = $vatExempt * ($discount->amount / 100)
            : $discountAmount = $discount->amount;

        return [
            'taxRate'        => $taxRate,
            'vatExempt'      => $vatExempt,
            'taxAmount'      => $taxAmount,
            'vatableSales'   => $vatableSales,
            'lessTax'        => $lessTax,
            'discountAmount' => $discountAmount,
        ];
    }

    /**
     * Calculate percentage discount
     */
    private function calculatePercentageDiscount(Discount $discount, float $amount): array
    {
        $taxRate      = config('sales.tax_rate');
        $vatExempt    = 0;
        $vatableSales = $amount / $taxRate;
        $taxAmount    = $amount - $vatableSales;
        $lessTax      = 0;

        $discountAmount = $amount * ($discount->amount / 100);

        return [
            'taxRate'        => $taxRate,
            'vatExempt'      => $vatExempt,
            'taxAmount'      => $taxAmount,
            'vatableSales'   => $vatableSales,
            'lessTax'        => $lessTax,
            'discountAmount' => $discountAmount,
        ];
    }

    /**
     * Calculate fixed amount discount
     */
    private function calculateFixedDiscount(Discount $discount, float $amount): array
    {
        $taxRate      = config('sales.tax_rate');
        $vatExempt    = 0;
        $vatableSales = $amount / $taxRate;
        $taxAmount    = $amount - $vatableSales;
        $lessTax      = 0;

        return [
            'taxRate'        => $taxRate,
            'vatExempt'      => $vatExempt,
            'taxAmount'      => $taxAmount,
            'vatableSales'   => $vatableSales,
            'lessTax'        => $lessTax,
            'discountAmount' => $discount->amount,
        ];
    }

    public function getAvailableDiscounts()
    {
        return Discount::select('id', 'discount_name', 'type', 'amount', 'discount_type', 'remove_tax', 'require_customer_info')
            ->get();
    }
}
