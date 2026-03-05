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
     * paxCount: Number of people sharing the item
     * discountedPax: Number of people entitled to discount
     */
    public function calculateDiscountAmount(int $discountId, array $itemIds, ?float $quantity = null, ?int $paxCount = null, ?int $discountedPax = null, ?float $overrideAmount = null)
    {
        $discount = $this->discount->find($discountId);

        if (! $discount) {
            return [];
        }

        // Resolve amount override (used for manual discounts supplied by cashier)
        if ($overrideAmount !== null) {
            $discount->amount = $overrideAmount;
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

        $allocations = $this->buildFixedAllocations($discount, $itemsData, $overrideAmount);
        $results     = [];

        foreach ($itemsData as $index => $data) {
            $amount           = $data['amount'];
            $allocatedAmount  = $allocations[$index] ?? null;

            // Handle PAX division if required
            if ($discount->required_pax && $paxCount && $discountedPax && $paxCount > 0) {
                $results[] = $this->calculatePaxDividedDiscount($discount, $amount, $paxCount, $discountedPax, $allocatedAmount);
                continue;
            }

            if ($discount['remove_tax']) {
                $results[] = $this->calculateDiscountWithTaxRemoval($discount, $amount, $allocatedAmount);
                continue;
            }

            $results[] = match ($discount['type']) {
                'percentage'            => $this->calculatePercentageDiscount($discount, $amount),
                'fixed', 'amount', 'manual' => $this->calculateFixedDiscount($discount, $amount, $allocatedAmount),
                default                 => $this->calculateFixedDiscount($discount, $amount, $allocatedAmount),
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
     * Calculate PAX-divided discount
     * Divides the price by number of PAX and applies discount to specified number of PAX
     *
     * Example: Product = 100 (tax inclusive), PAX = 2, Discounted PAX = 1
     * - Price per PAX = 100 / 2 = 50
     * - 1 PAX gets discount applied (might remove tax and apply discount)
     * - Other PAX: regular price (50)
     */
    private function calculatePaxDividedDiscount(Discount $discount, float $amount, int $paxCount, int $discountedPax, ?float $allocatedAmount = null): array
    {
        $taxRate = config('sales.tax_rate');

        // Divide the total amount by pax count to get per-pax amount
        $perPaxAmount = $amount / $paxCount;

        // Calculate the portion that gets discount (discounted pax)
        $discountedPortionAmount = $perPaxAmount * $discountedPax;

        // Calculate the regular portion (non-discounted pax)
        $regularPortionAmount = $perPaxAmount * ($paxCount - $discountedPax);

        // Process the discounted portion
        if ($discount->remove_tax) {
            // Remove tax from discounted portion
            $vatExemptDiscounted = $discountedPortionAmount / $taxRate;
            $lessTaxDiscounted = $discountedPortionAmount - $vatExemptDiscounted;

            // Calculate discount on tax-free amount
            if ($discount->type == TypeEnum::PERCENTAGE->value) {
                $discountAmount = $vatExemptDiscounted * ($discount->amount / 100);
            } else {
                // For fixed discount, divide by pax
                $fixedPerPax = ($allocatedAmount ?? $discount->amount) / $paxCount;
                $discountAmount = min($fixedPerPax * $discountedPax, $vatExemptDiscounted);
            }

            // Regular portion keeps tax
            $regularVatableSales = $regularPortionAmount / $taxRate;
            $regularTaxAmount = $regularPortionAmount - $regularVatableSales;

            return [
                'taxRate'        => $taxRate,
                'vatExempt'      => $vatExemptDiscounted,  // Only discounted portion is vat exempt
                'taxAmount'      => $regularTaxAmount,      // Tax from regular portion
                'vatableSales'   => $regularVatableSales,   // Vatable sales from regular portion
                'lessTax'        => $lessTaxDiscounted,     // Tax removed from discounted portion
                'discountAmount' => $discountAmount,
            ];
        } else {
            // No tax removal - standard discount calculation on discounted portion
            $vatableSalesDiscounted = $discountedPortionAmount / $taxRate;
            $taxAmountDiscounted = $discountedPortionAmount - $vatableSalesDiscounted;

            // Calculate discount
            if ($discount->type == TypeEnum::PERCENTAGE->value) {
                $discountAmount = $discountedPortionAmount * ($discount->amount / 100);
            } else {
                // For fixed discount, divide by pax
                $fixedPerPax = ($allocatedAmount ?? $discount->amount) / $paxCount;
                $discountAmount = min($fixedPerPax * $discountedPax, $discountedPortionAmount);
            }

            // Regular portion calculation
            $regularVatableSales = $regularPortionAmount / $taxRate;
            $regularTaxAmount = $regularPortionAmount - $regularVatableSales;

            return [
                'taxRate'        => $taxRate,
                'vatExempt'      => 0,
                'taxAmount'      => $taxAmountDiscounted + $regularTaxAmount,
                'vatableSales'   => $vatableSalesDiscounted + $regularVatableSales,
                'lessTax'        => 0,
                'discountAmount' => $discountAmount,
            ];
        }
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

    private function buildFixedAllocations(Discount $discount, array $itemsData, ?float $overrideAmount = null): array
    {
        if (! in_array($discount->type, ['fixed', 'amount', 'manual'], true)) {
            return [];
        }

        $discountValue = (float) ($overrideAmount ?? $discount->amount);

        if ($discountValue <= 0) {
            return array_fill(0, count($itemsData), 0.0);
        }

        // If an override is provided (manual discount), distribute proportionally across items
        if ($overrideAmount !== null) {
            $total = array_sum(array_map(static fn ($item) => (float) ($item['amount'] ?? 0), $itemsData));

            if ($total <= 0) {
                return array_fill(0, count($itemsData), 0.0);
            }

            return array_map(static function ($item) use ($discountValue, $total) {
                $amount = (float) ($item['amount'] ?? 0);

                if ($amount <= 0) {
                    return 0.0;
                }

                $proRated = ($amount / $total) * $discountValue;

                return min($proRated, $amount);
            }, $itemsData);
        }

        // Default behavior: apply full fixed amount per item (legacy)
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
        $query = Discount::select('id', 'discount_name', 'type', 'amount', 'discount_type', 'remove_tax', 'require_customer_info', 'required_pax', 'sort_order')
            ->orderBy('sort_order', 'asc');
        return $query->get()->toArray();
    }
}
