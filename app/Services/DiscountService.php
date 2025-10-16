<?php

namespace App\Services;

use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscountService
{
    /**
     * Cache key for active discounts
     */
    private const CACHE_KEY = 'active_discounts';

    /**
     * Cache duration in minutes (24 hours)
     */
    private const CACHE_DURATION = 1440;

    /**
     * Get all active discounts with caching
     *
     * @return Collection
     */
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
                            'updated_at'
                        ])
                        ->get();

                    return DiscountResource::collection($discounts);
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch active discounts: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get discounts grouped by type
     *
     * @return array
     */
    public function getDiscountsGroupedByType(): array
    {
        $discounts = $this->getActiveDiscounts();

        return [
            'percentage' => $discounts->filter(fn($discount) => $discount['type'] === 'percentage'),
            'fixed' => $discounts->filter(fn($discount) => in_array($discount['type'], ['fixed', 'amount'])),
            'other' => $discounts->filter(fn($discount) => !in_array($discount['type'], ['percentage', 'fixed', 'amount']))
        ];
    }

    /**
     * Get discount by ID
     *
     * @param string|int $discountId
     * @return array|null
     */
    public function getDiscountById($discountId): ?array
    {
        $discounts = $this->getActiveDiscounts();

        return $discounts->firstWhere('id', $discountId);
    }

    /**
     * Calculate discount amount for given items
     *
     * @param string|int $discountId
     * @param array $items
     * @return float
     */
    public function calculateDiscountAmount($discountId, array $items): float
    {
        $discount = $this->getDiscountById($discountId);

        if (!$discount) {
            return 0;
        }

        // Calculate subtotal of items
        $subtotal = collect($items)->sum(function ($item) {
            $price = (float) ($item['price'] ?? $item['average_cost'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 1);
            return $price * $quantity;
        });

        // Calculate vatable amount if tax should be removed
        $baseAmount = $discount['remove_tax'] ? $subtotal / 1.12 : $subtotal;

        return match ($discount['type']) {
            'percentage' => $baseAmount * ($discount['amount'] / 100),
            'fixed', 'amount' => min($discount['amount'], $baseAmount),
            default => 0
        };
    }

    /**
     * Clear the discounts cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Discounts cache cleared');
    }

    /**
     * Refresh the discounts cache
     *
     * @return Collection
     */
    public function refreshCache(): Collection
    {
        $this->clearCache();
        return $this->getActiveDiscounts();
    }

    /**
     * Check if a discount requires customer information
     *
     * @param string|int $discountId
     * @return bool
     */
    public function requiresCustomerInfo($discountId): bool
    {
        $discount = $this->getDiscountById($discountId);

        return $discount['requires_customer_info'] ?? false;
    }

    /**
     * Validate if discount can be applied to given items
     *
     * @param string|int $discountId
     * @param array $items
     * @return bool
     */
    public function canApplyDiscount($discountId, array $items): bool
    {
        $discount = $this->getDiscountById($discountId);

        if (!$discount) {
            return false;
        }

        // Add additional validation logic here
        // For example: minimum order amount, item categories, etc.

        return true;
    }

    /**
     * Get discount statistics for reporting
     *
     * @return array
     */
    public function getDiscountStats(): array
    {
        $discounts = $this->getActiveDiscounts();

        return [
            'total_discounts' => $discounts->count(),
            'percentage_discounts' => $discounts->where('type', 'percentage')->count(),
            'fixed_discounts' => $discounts->whereIn('type', ['fixed', 'amount'])->count(),
            'customer_info_required' => $discounts->where('requires_customer_info', true)->count(),
        ];
    }
}
