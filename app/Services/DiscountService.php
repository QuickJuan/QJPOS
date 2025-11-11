<?php
namespace App\Services;

use App\Models\Discount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\DiscountResource;

class DiscountService
{

    const CACHE_KEY = 'active_discounts';
    const CACHE_DURATION = 3600;
    /**
     * Get all active discounts
     */
    // public function getActiveDiscounts(): Collection
    // {
    //     try {
    //         $discounts = Discount::active()->get();

    //         return collect(DiscountResource::collection($discounts)->resolve());
    //     } catch (\Exception $e) {
    //         Log::error('Failed to fetch active discounts', ['error' => $e->getMessage()]);
    //         try {
    //             $discounts = Discount::query()->active()->get();

    //             $mapped = $discounts->map(fn($discount) => [
    //                 'id'                    => $discount->id,
    //                 'discount_name'         => $discount->discount_name,
    //                 'description'           => '',
    //                 'amount'                => $discount->amount,
    //                 'type'                  => $discount->type,
    //                 'discount_type'         => $discount->discount_type,
    //                 'remove_tax'            => $discount->remove_tax,
    //                 'require_customer_info' => $discount->require_customer_info,
    //                 'created_at'            => $discount->created_at,
    //                 'updated_at'            => $discount->updated_at,
    //             ]);
    //             return $mapped;
    //         } catch (\Exception $fallbackError) {
    //             Log::error('Fallback discount fetch also failed: ' . $fallbackError->getMessage());
    //             return collect();
    //         }
    //     }
    // }
    public function getActiveDiscounts(): Collection
    {
        try {
            return Cache::remember(
                self::CACHE_KEY,
                self::CACHE_DURATION,
                function () {
                    Log::info('Fetching active discounts from database');

                    $discounts = Discount::query()
                        ->select([
                            'id',
                            'discount_name',
                            'description',
                            'amount',
                            'type',
                            'discount_type',
                            'remove_tax',
                            'require_customer_info',
                            'created_at',
                            'updated_at',
                        ])
                        ->get();

                    return collect(DiscountResource::collection($discounts)->resolve());
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch active discounts: ' . $e->getMessage());
            // Fallback to direct database query without cache
            try {
                $discounts = Discount::query()
                    ->select([
                        'id',
                        'discount_name',
                        'amount',
                        'type',
                        'discount_type',
                        'remove_tax',
                        'require_customer_info',
                        'created_at',
                        'updated_at',
                    ])
                    ->get();

                $mapped = $discounts->map(function ($discount) {
                        return [
                            'id' => $discount->id,
                            'discount_name' => $discount->discount_name,
                            'description' => '',
                            'amount' => $discount->amount,
                            'type' => $discount->type,
                            'discount_type' => $discount->discount_type,
                            'remove_tax' => $discount->remove_tax,
                            'require_customer_info' => $discount->require_customer_info,
                            'created_at' => $discount->created_at,
                            'updated_at' => $discount->updated_at,
                        ];
                    });
                return $mapped;
            } catch (\Exception $fallbackError) {
                Log::error('Fallback discount fetch also failed: ' . $fallbackError->getMessage());
                return collect();
            }
        }
    }

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
    public function calculateDiscountAmount(int $discountId, array $items): float
    {
        $discount = $this->getDiscountById($discountId);

        if (! $discount) {
            return 0.0;
        }

        $subtotal = $this->calculateSubtotal($items);

        // If remove_tax is true, we need to:
        // 1. Extract the tax amount from the price
        // 2. Apply discount to the tax-exclusive price
        // 3. Add the extracted tax to the final discount
        if ($discount['remove_tax']) {
            return $this->calculateDiscountWithTaxRemoval($discount, $subtotal);
        }

        // Standard discount calculation on full price
        return match ($discount['type']) {
            'percentage' => $this->calculatePercentageDiscount($discount['amount'], $subtotal),
            'fixed', 'amount' => $this->calculateFixedDiscount($discount['amount'], $subtotal),
            default      => 0.0
        };
    }

    /**
     * Calculate discount with tax removal
     * Extracts tax from price, applies discount to net amount, then adds tax back to discount
     */
    private function calculateDiscountWithTaxRemoval(array $discount, float $subtotal): float
    {
        $taxRate = config('sales.tax_rate');

        // Extract tax from the price
        $taxAmount = $subtotal - ($subtotal / $taxRate);
        $netAmount = $subtotal / $taxRate; // Price without tax

        // Apply discount to the net amount
        $discountOnNet = match ($discount['type']) {
            'percentage' => $this->calculatePercentageDiscount($discount['amount'], $netAmount),
            'fixed', 'amount' => $this->calculateFixedDiscount($discount['amount'], $netAmount),
            default      => 0.0
        };

        // Total discount = discount on net amount + tax amount
        return $discountOnNet + $taxAmount;
    }

    /**
     * Calculate percentage discount
     */
    private function calculatePercentageDiscount(float $percentage, float $amount): float
    {
        return $amount * ($percentage / 100);
    }

    /**
     * Calculate fixed amount discount
     */
    private function calculateFixedDiscount(float $discountAmount, float $subtotal): float
    {
        // Fixed discount cannot exceed the subtotal
        return min($discountAmount, $subtotal);
    }

    /**
     * Calculate subtotal from items
     */
    private function calculateSubtotal(array $items): float
    {
        return collect($items)->sum(function ($item) {
            $price    = (float) ($item['price'] ?? $item['average_cost'] ?? 0);
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
