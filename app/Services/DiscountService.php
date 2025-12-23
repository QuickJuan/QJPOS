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

        if (! $discount) {
            return [];
        }

        $cartItems = CartItem::whereIn('id', $itemIds)->get()->keyBy('id');
        $itemsData = [];

        foreach ($itemIds as $itemId) {
            $cartItem = $cartItems->get($itemId);

            if (! $cartItem) {
                continue;
            }

            $itemQuantity = $quantity ?? $cartItem->quantity;
            $amount       = $itemQuantity * $cartItem->price;

            $itemsData[] = [
                'id'     => $itemId,
                'amount' => $amount,
            ];
        }

        if (empty($itemsData)) {
            return [];
        }

        $allocations = $this->buildFixedAllocations($discount, $itemsData);
        $results     = [];

        foreach ($itemsData as $index => $data) {
            $amount           = $data['amount'];
            $allocatedAmount  = $allocations[$index] ?? null;

            if ($discount['remove_tax']) {
                $results[] = $this->calculateDiscountWithTaxRemoval($discount, $amount, $allocatedAmount);
                continue;
            }

            $results[] = match ($discount['type']) {
                'percentage'      => $this->calculatePercentageDiscount($discount, $amount),
                'fixed', 'amount' => $this->calculateFixedDiscount($discount, $amount, $allocatedAmount),
                default           => $this->calculateFixedDiscount($discount, $amount, $allocatedAmount),
            };
        }

        return $results;
    }

    /**
     * Calculate discount with tax removal
     * Extracts tax from price, applies discount to net amount, then adds tax back to discount
     */
    private function calculateDiscountWithTaxRemoval(Discount $discount, float $amount, ?float $allocatedAmount = null): array
    {
        $taxRate      = config('sales.tax_rate');
        $vatExempt    = $amount / $taxRate;
        $taxAmount    = 0;
        $vatableSales = 0;
        $lessTax      = $amount - $vatExempt;

        $discount->type == TypeEnum::PERCENTAGE->value
            ? $discountAmount = $vatExempt * ($discount->amount / 100)
            : $discountAmount = $allocatedAmount ?? $discount->amount;

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
    private function calculateFixedDiscount(Discount $discount, float $amount, ?float $allocatedAmount = null): array
    {
        $taxRate      = config('sales.tax_rate');
        $vatExempt    = 0;
        $vatableSales = $amount / $taxRate;
        $taxAmount    = $amount - $vatableSales;
        $lessTax      = 0;

        $discountAmount = $allocatedAmount ?? $discount->amount;
        $discountAmount = min($discountAmount, $amount);

        return [
            'taxRate'        => $taxRate,
            'vatExempt'      => $vatExempt,
            'taxAmount'      => $taxAmount,
            'vatableSales'   => $vatableSales,
            'lessTax'        => $lessTax,
            'discountAmount' => $discountAmount,
        ];
    }

    private function buildFixedAllocations(Discount $discount, array $itemsData): array
    {
        if (! in_array($discount->type, ['fixed', 'amount'], true)) {
            return [];
        }

        $discountValue = (float) $discount->amount;

        if ($discountValue <= 0) {
            return array_fill(0, count($itemsData), 0.0);
        }

        return array_map(static function ($item) use ($discountValue) {
            $amount = (float) ($item['amount'] ?? 0);

            if ($amount <= 0) {
                return 0.0;
            }

            return min($discountValue, $amount);
        }, $itemsData);
    }

    public function getAvailableDiscounts()
    {
        $query = Discount::select('id', 'discount_name', 'type', 'amount', 'discount_type', 'remove_tax', 'require_customer_info', 'sort_order')
            ->orderBy('sort_order', 'asc');
        return $query->get()->toArray();
    }
}
