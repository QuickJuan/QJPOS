<?php

namespace App\Filament\Tenant\Resources\CashierSessionResource\Pages;

use App\Filament\Tenant\Resources\CashierSessionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Filament\Actions;

class ViewCashierSession extends ViewRecord
{
    protected static string $resource = CashierSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('x-reading.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    protected function normalizeCashDenominationDetailsState(mixed $state): array
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

        // If the payload is already structured (currencies array), keep as-is.
        if (array_key_exists('currencies', $state) && is_array($state['currencies'])) {
            return $state;
        }

        // If the payload is an array of currency rows (numeric keys), wrap it.
        $isCurrencyRows = ! empty($state) && array_is_list($state) && is_array($state[0] ?? null) && array_key_exists('currency_code', $state[0] ?? []);
        if ($isCurrencyRows) {
            return [
                'base_currency_symbol' => '₱',
                'currencies' => $state,
                'totals' => [],
            ];
        }

        // Legacy case: associative denomination => count
        return $state;
    }

    protected function normalizeXReadingMetaSummaryState(mixed $metaData, mixed $cashDenominationDetails, mixed $cashDenominationTotal, mixed $beginningCash, mixed $recordId = null): array
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

        // Final fallback (older records)
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

        // Prefer payment-based expected totals when available, else fallback.
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

    protected function normalizeKeyValueState(mixed $state): array
    {
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $state = $decoded;
            }
        }

        if (! is_array($state)) {
            return [];
        }

        $normalized = [];
        foreach ($state as $key => $value) {
            if (is_scalar($key)) {
                $keyString = (string) $key;
            } else {
                $encodedKey = json_encode($key);
                $keyString = is_string($encodedKey) ? $encodedKey : '';
            }

            if (is_array($value) || is_object($value)) {
                $encodedValue = json_encode($value);
                $valueString = is_string($encodedValue) ? $encodedValue : '';
            } elseif (is_bool($value)) {
                $valueString = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $valueString = '';
            } else {
                $valueString = (string) $value;
            }

            $normalized[$keyString] = $valueString;
        }

        return $normalized;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $hasCashDenomination = fn ($record) => ! empty($record?->cash_denomination_details) || ! empty($record?->cash_denomination);

        return $infolist
            ->schema([
                Section::make('Cashier Shift Information')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Cashier Shift No.'),
                                TextEntry::make('branch.name')
                                    ->label('Branch'),
                                TextEntry::make('business_date')
                                    ->label('Business Date')
                                    ->date('M d, Y'),
                                TextEntry::make('cashier.name')
                                    ->label('Cashier'),
                            ]),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextEntry::make('started_time')
                                    ->label('Started Time')
                                    ->dateTime('M d, Y h:i A'),
                                TextEntry::make('closing_time')
                                    ->label('Closing Time')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Still Open'),
                            ]),
                    ]),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Financial Summary')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 3,
                                ])
                                    ->schema([
                                        TextEntry::make('beginning_cash')
                                            ->label('Beginning Cash')
                                            ->money('php')
                                            ->color('info'),
                                        TextEntry::make('total_sales')
                                            ->label('Total Sales')
                                            ->money('php')
                                            ->color('success')
                                            ->weight('bold'),
                                        TextEntry::make('closing_cash')
                                            ->label('Closing Cash')
                                            ->money('php')
                                            ->color('warning'),
                                    ]),
                            ])
                            ->columnSpan(fn ($record) => $hasCashDenomination($record) ? 1 : 2),

                        Section::make('Cash Denomination')
                            ->schema([
                                ViewEntry::make('cash_denomination_details')
                                    ->label('Denomination Breakdown')
                                    ->view('filament.infolists.x-reading-cash-breakdown')
                                    ->state(fn ($record) => [
                                        'breakdown' => $this->normalizeCashDenominationDetailsState($record?->cash_denomination_details ?? $record?->cash_denomination),
                                        'cash_comparison' => is_array($record?->meta_data['cash_comparison'] ?? null) ? $record->meta_data['cash_comparison'] : [],
                                        'other_payments_comparison' => is_array($record?->meta_data['other_payments_comparison'] ?? null) ? $record->meta_data['other_payments_comparison'] : [],
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->visible($hasCashDenomination)
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),

                Section::make('Meta Data')
                    ->schema([
                        ViewEntry::make('meta_data')
                            ->label('Session Details')
                            ->view('filament.infolists.x-reading-meta-summary')
                            ->state(fn ($record) => $this->normalizeXReadingMetaSummaryState(
                                $record?->meta_data,
                                $record?->cash_denomination_details,
                                $record?->cash_denomination,
                                $record?->beginning_cash,
                                $record?->id,
                            ))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record->meta_data)),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->meta_data)),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('checkBy.name')
                            ->label('Checked By')
                            ->default('Not Yet Checked'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->default('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
