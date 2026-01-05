<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerEWallet;
use App\Models\EWalletTransaction;
use App\Models\PointsTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EWalletService
{
    /**
     * Load money to customer's e-wallet
     */
    public function loadBalance(
        CustomerEWallet $eWallet,
        float $amount,
        string $source,
        ?Order $order = null,
        ?string $description = null,
        ?array $metaData = null
    ): EWalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Load amount must be greater than zero.');
        }

        if (!$eWallet->is_active) {
            throw new \Exception('E-wallet is not active.');
        }

        return DB::transaction(function () use ($eWallet, $amount, $source, $order, $description, $metaData) {
            $balanceBefore = $eWallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update e-wallet
            $eWallet->update([
                'balance' => $balanceAfter,
                'total_loaded' => $eWallet->total_loaded + $amount,
                'last_transaction_at' => now(),
            ]);

            // Create transaction log
            $transaction = EWalletTransaction::create([
                'customer_e_wallet_id' => $eWallet->id,
                'order_id' => $order?->id,
                'transaction_type' => 'load',
                'source' => $source,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => $order ? "ORD-{$order->invoice_no}" : null,
                'description' => $description ?? "Loaded {$amount} from {$source}",
                'meta_data' => $metaData,
                'processed_by' => Auth::id(),
            ]);

            Log::info('E-wallet balance loaded', [
                'customer_id' => $eWallet->customer_id,
                'amount' => $amount,
                'source' => $source,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Deduct money from customer's e-wallet for payment
     */
    public function deductBalance(
        CustomerEWallet $eWallet,
        float $amount,
        Order $order,
        ?string $description = null,
        ?array $metaData = null
    ): EWalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be greater than zero.');
        }

        if (!$eWallet->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient e-wallet balance.');
        }

        return DB::transaction(function () use ($eWallet, $amount, $order, $description, $metaData) {
            $balanceBefore = $eWallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update e-wallet
            $eWallet->update([
                'balance' => $balanceAfter,
                'total_spent' => $eWallet->total_spent + $amount,
                'last_transaction_at' => now(),
            ]);

            // Create transaction log
            $transaction = EWalletTransaction::create([
                'customer_e_wallet_id' => $eWallet->id,
                'order_id' => $order->id,
                'transaction_type' => 'payment',
                'source' => 'payment',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => "ORD-{$order->invoice_no}",
                'description' => $description ?? "Payment for order #{$order->invoice_no}",
                'meta_data' => $metaData,
                'processed_by' => Auth::id(),
            ]);

            Log::info('E-wallet payment processed', [
                'customer_id' => $eWallet->customer_id,
                'amount' => $amount,
                'order_id' => $order->id,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Refund money to customer's e-wallet
     */
    public function refundBalance(
        CustomerEWallet $eWallet,
        float $amount,
        ?Order $order = null,
        ?string $description = null,
        ?array $metaData = null
    ): EWalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be greater than zero.');
        }

        return DB::transaction(function () use ($eWallet, $amount, $order, $description, $metaData) {
            $balanceBefore = $eWallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update e-wallet
            $eWallet->update([
                'balance' => $balanceAfter,
                'total_spent' => max(0, $eWallet->total_spent - $amount),
                'last_transaction_at' => now(),
            ]);

            // Create transaction log
            $transaction = EWalletTransaction::create([
                'customer_e_wallet_id' => $eWallet->id,
                'order_id' => $order?->id,
                'transaction_type' => 'refund',
                'source' => 'refund',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => $order ? "REF-{$order->invoice_no}" : null,
                'description' => $description ?? "Refund for order #{$order->invoice_no}",
                'meta_data' => $metaData,
                'processed_by' => Auth::id(),
            ]);

            Log::info('E-wallet refund processed', [
                'customer_id' => $eWallet->customer_id,
                'amount' => $amount,
                'order_id' => $order?->id,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Award points to customer
     */
    public function earnPoints(
        CustomerEWallet $eWallet,
        float $points,
        Order $order,
        ?string $description = null,
        ?array $metaData = null
    ): PointsTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be greater than zero.');
        }

        return DB::transaction(function () use ($eWallet, $points, $order, $description, $metaData) {
            $balanceBefore = $eWallet->points_balance;
            $balanceAfter = $balanceBefore + $points;

            // Update e-wallet
            $eWallet->update([
                'earned_points' => $eWallet->earned_points + $points,
                'points_balance' => $balanceAfter,
                'last_transaction_at' => now(),
            ]);

            // Create transaction log
            $transaction = PointsTransaction::create([
                'customer_e_wallet_id' => $eWallet->id,
                'order_id' => $order->id,
                'transaction_type' => 'earn',
                'points' => $points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => "ORD-{$order->invoice_no}",
                'description' => $description ?? "Earned {$points} points from order #{$order->invoice_no}",
                'meta_data' => $metaData,
                'processed_by' => Auth::id(),
            ]);

            Log::info('Points earned', [
                'customer_id' => $eWallet->customer_id,
                'points' => $points,
                'order_id' => $order->id,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Redeem points from customer
     */
    public function redeemPoints(
        CustomerEWallet $eWallet,
        float $points,
        Order $order,
        ?string $description = null,
        ?array $metaData = null
    ): PointsTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be greater than zero.');
        }

        if (!$eWallet->hasSufficientPoints($points)) {
            throw new \Exception('Insufficient points balance.');
        }

        return DB::transaction(function () use ($eWallet, $points, $order, $description, $metaData) {
            $balanceBefore = $eWallet->points_balance;
            $balanceAfter = $balanceBefore - $points;

            // Update e-wallet
            $eWallet->update([
                'redeemed_points' => $eWallet->redeemed_points + $points,
                'points_balance' => $balanceAfter,
                'last_transaction_at' => now(),
            ]);

            // Create transaction log
            $transaction = PointsTransaction::create([
                'customer_e_wallet_id' => $eWallet->id,
                'order_id' => $order->id,
                'transaction_type' => 'redeem',
                'points' => -$points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => "ORD-{$order->invoice_no}",
                'description' => $description ?? "Redeemed {$points} points for order #{$order->invoice_no}",
                'meta_data' => $metaData,
                'processed_by' => Auth::id(),
            ]);

            Log::info('Points redeemed', [
                'customer_id' => $eWallet->customer_id,
                'points' => $points,
                'order_id' => $order->id,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Get or create e-wallet for customer
     */
    public function getOrCreateEWallet(Customer $customer): CustomerEWallet
    {
        return $customer->eWallet()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'balance' => 0,
                'total_loaded' => 0,
                'total_spent' => 0,
                'earned_points' => $customer->earned_points ?? 0,
                'redeemed_points' => $customer->redeemed_points ?? 0,
                'points_balance' => $customer->balance ?? 0,
                'is_active' => true,
            ]
        );
    }
}
