<?php

namespace App\Services;

use App\Models\Customer;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;

class PointsService
{
    public function __construct(
        protected GeneralSettings $settings
    ) {}

    /**
     * Calculate points earned from a given amount
     */
    public function calculatePointsFromAmount(float $amount): float
    {
        if ($this->settings->points_earning_rate <= 0) {
            return 0;
        }

        return floor($amount / $this->settings->points_earning_rate * 100) / 100;
    }

    /**
     * Award points to a customer based on amount spent
     */
    public function earnPoints(Customer $customer, float $amount): float
    {
        $pointsEarned = $this->calculatePointsFromAmount($amount);

        if ($pointsEarned > 0) {
            DB::transaction(function () use ($customer, $pointsEarned) {
                $customer->increment('earned_points', $pointsEarned);
                $customer->increment('balance', $pointsEarned);
            });
        }

        return $pointsEarned;
    }

    /**
     * Redeem points from a customer's balance
     */
    public function redeemPoints(Customer $customer, float $points): bool
    {
        if ($points <= 0) {
            return false;
        }

        if ($customer->balance < $points) {
            return false;
        }

        DB::transaction(function () use ($customer, $points) {
            $customer->increment('redeemed_points', $points);
            $customer->decrement('balance', $points);
        });

        return true;
    }

    /**
     * Check if customer has sufficient points
     */
    public function hasSufficientPoints(Customer $customer, float $requiredPoints): bool
    {
        return $customer->balance >= $requiredPoints;
    }

    /**
     * Convert points to amount (1 point = 1 currency unit)
     */
    public function convertPointsToAmount(float $points): float
    {
        return $points;
    }

    /**
     * Convert amount to points (1 currency unit = 1 point)
     */
    public function convertAmountToPoints(float $amount): float
    {
        return $amount;
    }
}
