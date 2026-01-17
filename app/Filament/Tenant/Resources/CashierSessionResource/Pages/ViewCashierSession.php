<?php

namespace App\Filament\Tenant\Resources\CashierSessionResource\Pages;

use App\Filament\Tenant\Resources\CashierSessionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;

class ViewCashierSession extends ViewRecord
{
    protected static string $resource = CashierSessionResource::class;

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

    protected function normalizeXReadingMetaSummaryState(mixed $metaData, mixed $cashDenominationDetails, mixed $cashDenominationTotal, mixed $beginningCash): array
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

        $details = $this->normalizeCashDenominationDetailsState($cashDenominationDetails);
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

        // Fall back to whatever is stored in cash_denomination (could be numeric in older records)
        if ($actualCashInBase === null) {
            $actualCashInBase = is_numeric($cashDenominationTotal) ? (float) $cashDenominationTotal : 0.0;
        }

        $giftCheckTotal = 0.0;
        if (is_array($details)) {
            $giftCheckTotal = (float) ($details['gift_check_total'] ?? ($details['totals']['gift_check_in_base'] ?? 0) ?? 0);
        }

        $netSales = (float) ($metaData['net_sales'] ?? 0);
        $serviceCharge = (float) ($metaData['service_charge'] ?? 0);
        $expectedCash = $netSales + $serviceCharge;
        $variance = $actualCashInBase - $expectedCash;

        return [
            'base_currency_symbol' => $baseSymbol,
            'beginning_cash' => (float) ($beginningCash ?? 0),
            'cash_denomination_total' => (float) $actualCashInBase,
            'gift_check_total' => (float) $giftCheckTotal,
            'expected_cash' => (float) $expectedCash,
            'variance' => (float) $variance,
            'meta_data' => $metaData,
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
        return $infolist
            ->schema([
                Section::make('Session Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch'),
                                TextEntry::make('business_date')
                                    ->label('Business Date')
                                    ->date('M d, Y'),
                                TextEntry::make('cashier.name')
                                    ->label('Cashier'),
                            ]),
                        Grid::make(2)
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

                Section::make('Financial Summary')
                    ->schema([
                        Grid::make(3)
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
                    ]),

                Section::make('Cash Denomination')
                    ->schema([
                        ViewEntry::make('cash_denomination_details')
                            ->label('Denomination Breakdown')
                            ->view('filament.infolists.x-reading-cash-breakdown')
                            ->state(fn ($record) => $this->normalizeCashDenominationDetailsState($record?->cash_denomination_details ?? $record?->cash_denomination))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record->cash_denomination_details) || ! empty($record->cash_denomination)),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => ! empty($record->cash_denomination_details) || ! empty($record->cash_denomination)),

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
