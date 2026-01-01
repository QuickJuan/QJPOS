<?php

namespace App\Services;

use App\Models\CashierCashout;
use Illuminate\Support\Facades\DB;

class CashMovementService
{
    /**
     * Get cash in/out movements for a specific shift
     *
     * @param int $sessionId The cashier session ID
     * @param string $type 'in' for cash in, 'out' for cash out, or 'all' for both
     * @return object Summary of cash movements
     */
    public function getCashMovementsPerShift(int $sessionId, string $type = 'all'): object
    {
        $result = DB::table('cashier_cashouts')
            ->where('cashier_session_id', $sessionId)
            ->where('status', CashierCashout::STATUS_APPROVED)
            ->selectRaw('
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_cash_in,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_cash_out
            ', [
                CashierCashout::TYPE_CASH_IN,
                CashierCashout::TYPE_CASH_OUT,
            ])
            ->first();

        $cashIn = (float) ($result->total_cash_in ?? 0);
        $cashOut = (float) ($result->total_cash_out ?? 0);

        return (object) [
            'total_cash_in' => $cashIn,
            'total_cash_out' => $cashOut,
            'net_movement' => $cashIn - $cashOut,
        ];
    }

    /**
     * Get detailed breakdown of cash movements per shift
     *
     * @param int $sessionId The cashier session ID
     * @return array Array of cash movement records
     */
    public function getCashMovementsBreakdown(int $sessionId): array
    {
        return CashierCashout::where('cashier_session_id', $sessionId)
            ->where('status', CashierCashout::STATUS_APPROVED)
            ->orderBy('created_at')
            ->get()
            ->map(function ($cashout) {
                return [
                    'id' => $cashout->id,
                    'type' => $cashout->type,
                    'amount' => (float) $cashout->amount,
                    'source_name' => $cashout->source_name,
                    'purpose' => $cashout->purpose,
                    'created_at' => $cashout->created_at->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get cash in movements only
     *
     * @param int $sessionId The cashier session ID
     * @return float Total cash in amount
     */
    public function getTotalCashIn(int $sessionId): float
    {
        return (float) CashierCashout::where('cashier_session_id', $sessionId)
            ->where('type', CashierCashout::TYPE_CASH_IN)
            ->where('status', CashierCashout::STATUS_APPROVED)
            ->sum('amount');
    }

    /**
     * Get cash out movements only
     *
     * @param int $sessionId The cashier session ID
     * @return float Total cash out amount
     */
    public function getTotalCashOut(int $sessionId): float
    {
        return (float) CashierCashout::where('cashier_session_id', $sessionId)
            ->where('type', CashierCashout::TYPE_CASH_OUT)
            ->where('status', CashierCashout::STATUS_APPROVED)
            ->sum('amount');
    }

    /**
     * Get net cash movement (cash in - cash out)
     *
     * @param int $sessionId The cashier session ID
     * @return float Net cash movement
     */
    public function getNetCashMovement(int $sessionId): float
    {
        return $this->getTotalCashIn($sessionId) - $this->getTotalCashOut($sessionId);
    }
}
