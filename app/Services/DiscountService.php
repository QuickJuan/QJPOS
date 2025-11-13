<?php
namespace App\Services;

use App\Enums\Discount\TypeEnum;
use App\Models\CartItem;
use App\Models\Discount;
use Illuminate\Support\Facades\Log;

class DiscountService
{

    const CACHE_KEY      = 'active_discounts';
    const CACHE_DURATION = 3600;

    public function __construct(public Discount $discount)
    {
        $this->discount = $discount;
    }

    /**
     * Get all active discounts
     */
    public function getActiveDiscounts()
    {
        try {
            return $this->discount->active()->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch active discounts', ['error' => $e->getMessage()]);
        }
    }
    // public function getActiveDiscounts(): Collection
    // {
    //     try {
    //         return Cache::remember(
    //             self::CACHE_KEY,
    //             self::CACHE_DURATION,
    //             function () {
    //                 Log::info('Fetching active discounts from database');

    //                 $discounts = Discount::query()
    //                     ->select([
    //                         'id',
    //                         'discount_name',
    //                         'description',
    //                         'amount',
    //                         'type',
    //                         'discount_type',
    //                         'remove_tax',
    //                         'require_customer_info',
    //                         'created_at',
    //                         'updated_at',
    //                     ])
    //                     ->get();

    //                 return collect(DiscountResource::collection($discounts)->resolve());
    //             }
    //         );
    //     } catch (\Exception $e) {
    //         Log::error('Failed to fetch active discounts: ' . $e->getMessage());
    //         // Fallback to direct database query without cache
    //         try {
    //             $discounts = Discount::query()
    //                 ->select([
    //                     'id',
    //                     'discount_name',
    //                     'amount',
    //                     'type',
    //                     'discount_type',
    //                     'remove_tax',
    //                     'require_customer_info',
    //                     'created_at',
    //                     'updated_at',
    //                 ])
    //                 ->get();

    //             $mapped = $discounts->map(function ($discount) {
    //                 return [
    //                     'id'                    => $discount->id,
    //                     'discount_name'         => $discount->discount_name,
    //                     'description'           => '',
    //                     'amount'                => $discount->amount,
    //                     'type'                  => $discount->type,
    //                     'discount_type'         => $discount->discount_type,
    //                     'remove_tax'            => $discount->remove_tax,
    //                     'require_customer_info' => $discount->require_customer_info,
    //                     'created_at'            => $discount->created_at,
    //                     'updated_at'            => $discount->updated_at,
    //                 ];
    //             });
    //             return $mapped;
    //         } catch (\Exception $fallbackError) {
    //             Log::error('Fallback discount fetch also failed: ' . $fallbackError->getMessage());
    //             return collect();
    //         }
    //     }
    // }

    /**
     * Get discounts grouped by type
     */
    public function getDiscountsGroupedByType(): array
    {
        $discounts = $this->getActiveDiscounts();

        return [
            'percentage' => $discounts->where('type', 'percentage'),
            'fixed'      => $discounts->whereIn('type', ['fixed', 'amount']),
            'other'      => $discounts->whereNotIn('type', ['percentage', 'fixed', 'amount']),
        ];
    }

    /**
     * Get discount by ID
     */
    public function getDiscountById(string | int $discountId): ?array
    {
        return $this->getActiveDiscounts()->firstWhere('id', $discountId);
    }

    /**
     * Calculate discount amount for given items
     * discountId: ID of the discount to apply
     * items: array of ids from cart items or order items
     */
    public function calculateDiscountAmount(int $discountId, array $itemIds)
    {
        $discount = $this->discount->find($discountId);

        if (! $discount) {
            return 0.0;
        }

        $results = [];

        foreach ($itemIds as $itemId) {
            $cartItem = CartItem::find($itemId);

            if ($cartItem) {
                $amount = $cartItem->quantity * $cartItem->price;

                $results[] = $discount['remove_tax']
                    ? $this->calculateDiscountWithTaxRemoval($discount, $amount)
                    : match ($discount['type']) {
                    'percentage' => $this->calculatePercentageDiscount($discount, $amount),
                    'fixed', 'amount' => $this->calculateFixedDiscount($discount, $amount),
                    default      => 0.0
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

/**
 * Calculate subtotal from items
 */
    private function calculateSubtotal(array $items): float
    {
        return collect($items)->sum(function ($item) {
            $price    = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 1);
            return $price * $quantity;
        });
    }

/**
 * Validate if discount can be applied to given items
 */
    public function canApplyDiscount(string | int $discountId, array $items): bool
    {
        $discount = $this->getDiscountById($discountId);

        if (! $discount) {
            return false;
        }

        // Add additional validation logic here
        // For example: minimum order amount, item categories, etc.

        return true;
    }

/**
 * Get discount statistics for reporting
 */
    public function getDiscountStats(): array
    {
        $discounts = $this->getActiveDiscounts();

        return [
            'total_discounts'        => $discounts->count(),
            'percentage_discounts'   => $discounts->where('type', 'percentage')->count(),
            'fixed_discounts'        => $discounts->whereIn('type', ['fixed', 'amount'])->count(),
            'customer_info_required' => $discounts->where('require_customer_info', true)->count(),
        ];
    }
}
