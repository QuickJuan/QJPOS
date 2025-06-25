<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CouponCode extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'applicable_products',
        'applicable_categories',
    ];

    protected $casts = [
        'valid_from'            => 'datetime',
        'valid_until'           => 'datetime',
        'is_active'             => 'boolean',
        'applicable_products'   => 'array',
        'applicable_categories' => 'array',
    ];

    /**
     * Get the coupon usages
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Convert value from cents to dollars for display
     */
    protected function displayValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->type === 'percentage') {
                    return $this->value . '%';
                }
                return '$' . number_format($this->value / 100, 2);
            }
        );
    }

    /**
     * Convert minimum amount from cents to dollars
     */
    protected function displayMinimumAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->minimum_amount ? '$' . number_format($this->minimum_amount / 100, 2) : null
        );
    }

    /**
     * Check if coupon is currently valid (within date range)
     */
    public function isWithinValidPeriod(): bool
    {
        $now = Carbon::now();

        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon has reached its usage limit
     */
    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit && $this->used_count >= $this->usage_limit;
    }

    /**
     * Check if user has reached their usage limit for this coupon
     */
    public function hasUserReachedLimit($userId = null, $sessionId = null): bool
    {
        if (! $this->usage_limit_per_user) {
            return false;
        }

        $query = $this->usages();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return false;
        }

        return $query->count() >= $this->usage_limit_per_user;
    }

    /**
     * Calculate discount for the given cart total
     */
    public function calculateDiscount(int $cartTotalInCents): int
    {
        if ($this->type === 'percentage') {
            return (int) round(($cartTotalInCents * $this->value) / 100);
        }

        // Fixed amount - return the coupon value, but not more than cart total
        return min($this->value, $cartTotalInCents);
    }

    /**
     * Check if coupon is applicable to given products
     */
    public function isApplicableToProducts(array $productIds): bool
    {
        if (empty($this->applicable_products)) {
            return true; // Applicable to all products
        }

        return ! empty(array_intersect($this->applicable_products, $productIds));
    }

    /**
     * Check if coupon is applicable to given categories
     */
    public function isApplicableToCategories(array $categoryIds): bool
    {
        if (empty($this->applicable_categories)) {
            return true; // Applicable to all categories
        }

        return ! empty(array_intersect($this->applicable_categories, $categoryIds));
    }

    /**
     * Get the names of applicable products
     */
    protected function applicableProductNames(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->applicable_products)) {
                    return collect();
                }
                return Product::whereIn('id', $this->applicable_products)->pluck('title');
            }
        );
    }

    /**
     * Get the names of applicable categories
     */
    // protected function applicableCategoryNames(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             if (empty($this->applicable_categories)) {
    //                 return collect();
    //             }
    //             return ProductCategory::whereIn('id', $this->applicable_categories)->pluck('title');
    //         }
    //     );
    // }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid coupons (within date range)
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
        });
    }
}
