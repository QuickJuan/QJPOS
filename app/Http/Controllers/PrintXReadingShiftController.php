<?php

namespace App\Http\Controllers;

use App\Models\CashierSession;
use Illuminate\Http\Request;

class PrintXReadingShiftController extends Controller
{
    public function __invoke(Request $request, CashierSession $cashierSession)
    {
        $cashierSession->loadMissing(['branch', 'cashier']);

        $breakdown = $this->normalizeCashDenominationDetailsState(
            $cashierSession->cash_denomination_details ?? $cashierSession->cash_denomination
        );

        $metaState = $this->normalizeXReadingMetaSummaryState(
            $cashierSession->meta_data,
            $cashierSession->cash_denomination_details,
            $cashierSession->cash_denomination,
            $cashierSession->beginning_cash,
            $cashierSession->id,
        );

        return response()
            ->view('print.x-reading-shift', [
                'session' => $cashierSession,
                'breakdown' => $breakdown,
                'meta' => $metaState,
            ]);
    }

    private function normalizeCashDenominationDetailsState(mixed $state): array
    {
        if (! $state) {
            return [];
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $state = $decoded;
            }
        }

        if (is_object($state)) {
            $state = (array) $state;
        }

        if (! is_array($state)) {
            return [];
        }

        if (array_key_exists('currencies', $state) && is_array($state['currencies'])) {
            return $state;
        }

        $isCurrencyRows = ! empty($state)
            && array_is_list($state)
            && is_array($state[0] ?? null)
            && array_key_exists('currency_code', $state[0] ?? []);

        if ($isCurrencyRows) {
            return [
                'base_currency_symbol' => '₱',
                'currencies' => $state,
                'totals' => [],
            ];
        }

        return $state;
    }

    private function normalizeXReadingMetaSummaryState(mixed $metaData, mixed $cashDenominationDetails, mixed $cashDenominationTotal, mixed $beginningCash, mixed $recordId = null): array
    {
        if (is_string($metaData)) {
            $decoded = json_decode($metaData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $metaData = $decoded;
            }
        }

        if (is_object($metaData)) {
            $metaData = (array) $metaData;
        }

        $metaData = is_array($metaData) ? $metaData : [];

        $details = $this->normalizeCashDenominationDetailsState($cashDenominationDetails ?: $cashDenominationTotal);
        $baseSymbol = (string) ($details['base_currency_symbol'] ?? '₱');

        $actualCashInBase = null;
        if (is_array($details) && isset($details['totals']) && is_array($details['totals'])) {
            $actualCashInBase = $details['totals']['cash_in_base']
                ?? $details['totals']['actual_cash_in_base']
                ?? null;
        }

        if ($actualCashInBase === null && isset($details['currencies']) && is_array($details['currencies'])) {
            $actualCashInBase = array_reduce(
                $details['currencies'],
                fn ($carry, $currency) => $carry + (float) (($currency['amount_in_base'] ?? $currency['total_in_base'] ?? 0) ?: 0),
                0.0
            );
        }

        if ($actualCashInBase === null) {
            $actualCashInBase = is_numeric($cashDenominationTotal) ? (float) $cashDenominationTotal : 0.0;
        }

        $giftCheckTotal = 0.0;
        if (is_array($details)) {
            $giftCheckTotal = (float) ($details['gift_check_total'] ?? ($details['totals']['gift_check_in_base'] ?? 0) ?? 0);
        }

        $netSales = (float) ($metaData['net_sales'] ?? 0);
        $serviceCharge = (float) ($metaData['service_charge'] ?? 0);

        $cashComparison = is_array($metaData['cash_comparison'] ?? null) ? $metaData['cash_comparison'] : [];
        $otherPaymentsComparison = is_array($metaData['other_payments_comparison'] ?? null) ? $metaData['other_payments_comparison'] : [];
        $allComparisons = array_merge($cashComparison, $otherPaymentsComparison);

        $expectedFromPayments = array_reduce(
            $allComparisons,
            fn ($carry, $row) => $carry + (float) (is_array($row) ? ($row['expected_amount_in_base'] ?? 0) : 0),
            0.0
        );

        $expectedCash = $expectedFromPayments > 0 ? $expectedFromPayments : ($netSales + $serviceCharge);
        $variance = $actualCashInBase - $expectedCash;

        return [
            'id' => is_numeric($recordId) ? (int) $recordId : null,
            'base_currency_symbol' => $baseSymbol,
            'beginning_cash' => (float) ($beginningCash ?? 0),
            'cash_denomination_total' => (float) $actualCashInBase,
            'gift_check_total' => (float) $giftCheckTotal,
            'expected_cash' => (float) $expectedCash,
            'variance' => (float) $variance,
            'meta_data' => $metaData,
            'cash_comparison' => $cashComparison,
            'other_payments_comparison' => $otherPaymentsComparison,
        ];
    }
}
