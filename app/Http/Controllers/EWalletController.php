<?php

namespace App\Http\Controllers;

use App\Services\EWalletService;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class EWalletController extends Controller
{
    public function __construct(
        protected EWalletService $eWalletService
    ) {}

    /**
     * Load change to customer's e-wallet
     */
    public function loadChange(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $customer = Customer::findOrFail($validated['customer_id']);
            $order = Order::findOrFail($validated['order_id']);
            $eWallet = $this->eWalletService->getOrCreateEWallet($customer);

            $transaction = $this->eWalletService->loadBalance(
                $eWallet,
                $validated['amount'],
                'change_loading',
                $order,
                "Change loaded from order #{$order->invoice_no}",
                [
                    'order_invoice' => $order->invoice_no,
                    'loaded_by' => auth()->id(),
                    'order_total' => $order->total_due,
                ]
            );

            // Update order to mark change as loaded
            $order->update([
                'change_loaded_to_ewallet' => $validated['amount'],
                'is_change_loaded_to_ewallet' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Change loaded to e-wallet successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'new_balance' => $eWallet->fresh()->balance,
                    'formatted_balance' => $eWallet->fresh()->formatted_balance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get customer's e-wallet balance
     */
    public function getBalance($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $eWallet = $this->eWalletService->getOrCreateEWallet($customer);

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $eWallet->balance,
                    'formatted_balance' => $eWallet->formatted_balance,
                    'points_balance' => $eWallet->points_balance,
                    'formatted_points_balance' => $eWallet->formatted_points_balance,
                    'total_loaded' => $eWallet->total_loaded,
                    'total_spent' => $eWallet->total_spent,
                    'is_active' => $eWallet->is_active,
                    'last_transaction_at' => $eWallet->last_transaction_at?->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get customer's e-wallet transaction history
     */
    public function getTransactions(Request $request, $customerId)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:load,payment,refund,adjustment',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        try {
            $customer = Customer::findOrFail($customerId);
            $eWallet = $this->eWalletService->getOrCreateEWallet($customer);

            $query = $eWallet->transactions()
                ->with(['order', 'processedBy'])
                ->orderBy('created_at', 'desc');

            if (isset($validated['type'])) {
                $query->where('transaction_type', $validated['type']);
            }

            $limit = $validated['limit'] ?? 50;
            $offset = $validated['offset'] ?? 0;

            $transactions = $query->offset($offset)->limit($limit)->get();
            $total = $query->count();

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'meta' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get customer's points transaction history
     */
    public function getPointsTransactions(Request $request, $customerId)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:earn,redeem,adjustment,expire',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        try {
            $customer = Customer::findOrFail($customerId);
            $eWallet = $this->eWalletService->getOrCreateEWallet($customer);

            $query = $eWallet->pointsTransactions()
                ->with(['order', 'processedBy'])
                ->orderBy('created_at', 'desc');

            if (isset($validated['type'])) {
                $query->where('transaction_type', $validated['type']);
            }

            $limit = $validated['limit'] ?? 50;
            $offset = $validated['offset'] ?? 0;

            $transactions = $query->offset($offset)->limit($limit)->get();
            $total = $query->count();

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'meta' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Make payment using e-wallet
     */
    public function makePayment(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $customer = Customer::findOrFail($validated['customer_id']);
            $order = Order::findOrFail($validated['order_id']);
            $eWallet = $this->eWalletService->getOrCreateEWallet($customer);

            if (!$eWallet->hasSufficientBalance($validated['amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient e-wallet balance',
                    'current_balance' => $eWallet->balance,
                    'required_amount' => $validated['amount'],
                ], 422);
            }

            $transaction = $this->eWalletService->deductBalance(
                $eWallet,
                $validated['amount'],
                $order,
                "Payment for order #{$order->invoice_no}",
                [
                    'order_invoice' => $order->invoice_no,
                    'order_total' => $order->total_due,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount_paid' => $validated['amount'],
                    'new_balance' => $eWallet->fresh()->balance,
                    'formatted_balance' => $eWallet->fresh()->formatted_balance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
