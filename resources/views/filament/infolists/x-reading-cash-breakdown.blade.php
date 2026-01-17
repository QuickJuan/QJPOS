<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState();
        $baseSymbol = is_array($state) ? ($state['base_currency_symbol'] ?? '₱') : '₱';

        $format = function ($value): string {
            $number = is_numeric($value) ? (float) $value : 0.0;
            return number_format($number, 2);
        };

        $isStructured = is_array($state) && array_key_exists('currencies', $state) && is_array($state['currencies']);
        $currencies = $isStructured ? ($state['currencies'] ?? []) : [];
        $totals = $isStructured && is_array($state['totals'] ?? null) ? $state['totals'] : null;

        $giftCheckTotal = 0.0;
        if ($isStructured) {
            $giftCheckTotal = (float) ($state['gift_check_total'] ?? ($totals['gift_check_in_base'] ?? 0) ?? 0);
        }

        $hasTotals = $isStructured && $totals && (array_key_exists('cash_in_base', $totals) || array_key_exists('expected_cash_in_base', $totals) || array_key_exists('variance_in_base', $totals));

        $legacyEntries = (! $isStructured && is_array($state)) ? $state : [];
        $legacyIsAssociative = ! $isStructured && is_array($legacyEntries) && ! array_is_list($legacyEntries);
        if ($legacyIsAssociative) {
            krsort($legacyEntries, SORT_NUMERIC);
        }
    @endphp

    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['w-full space-y-4'])
        }}
    >
        @if ($isStructured)
            <div class="space-y-4">
                @foreach ($currencies as $currency)
                    @php
                        $symbol = $currency['symbol'] ?? '';
                        $currencyName = $currency['currency_name'] ?? ($currency['currency_code'] ?? 'Cash');

                        $amountInCurrency = (float) ($currency['amount_in_currency'] ?? ($currency['total_amount'] ?? 0));
                        $exchangeRate = (float) ($currency['exchange_rate'] ?? 1);
                        $amountInBase = (float) ($currency['amount_in_base'] ?? ($currency['total_in_base'] ?? ($amountInCurrency * $exchangeRate)));

                        $expectedInCurrency = $currency['expected_in_currency'] ?? null;
                        $expectedInBase = $currency['expected_in_base'] ?? null;
                        $varianceInCurrency = $currency['variance_in_currency'] ?? null;
                        $varianceInBase = $currency['variance_in_base'] ?? null;

                        $hasComparison = is_numeric($expectedInCurrency) || is_numeric($expectedInBase) || is_numeric($varianceInCurrency) || is_numeric($varianceInBase);
                        $denominations = is_array($currency['denominations'] ?? null) ? $currency['denominations'] : [];
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                            <span>{{ $currencyName }}</span>
                            <span class="text-right text-gray-900">
                                {{ $symbol }} {{ $format($amountInCurrency) }}
                                <span class="text-xs font-normal text-gray-500">
                                    ≈ {{ $baseSymbol }} {{ $format($amountInBase) }}
                                </span>
                            </span>
                        </div>

                        @if (! empty($denominations))
                            <div class="space-y-1 text-sm">
                                @foreach ($denominations as $denomination)
                                    @php
                                        $count = (int) ($denomination['count'] ?? 0);
                                        $label = $denomination['label'] ?? $denomination['denomination_label'] ?? null;
                                        $value = $denomination['value'] ?? $denomination['denomination_value'] ?? null;
                                        $total = $denomination['total'] ?? null;

                                        $displayLabel = $label;
                                        if (! is_string($displayLabel) || $displayLabel === '') {
                                            $displayLabel = is_numeric($value) ? $format($value) : '';
                                        }

                                        $computedTotal = is_numeric($total)
                                            ? (float) $total
                                            : ((is_numeric($value) ? (float) $value : 0.0) * $count);
                                    @endphp
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">
                                            {{ $count }} x {{ $displayLabel }} =
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            {{ $symbol }} {{ $format($computedTotal) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($hasComparison)
                            @php
                                $varianceClass = 'text-gray-700';
                                if (is_numeric($varianceInBase) && (float) $varianceInBase > 0) {
                                    $varianceClass = 'text-emerald-700';
                                }
                                if (is_numeric($varianceInBase) && (float) $varianceInBase < 0) {
                                    $varianceClass = 'text-rose-700';
                                }

                                $expectedCurrencyValue = is_numeric($expectedInCurrency) ? (float) $expectedInCurrency : 0.0;
                                $expectedBaseValue = is_numeric($expectedInBase) ? (float) $expectedInBase : ($expectedCurrencyValue * $exchangeRate);
                                $varianceCurrencyValue = is_numeric($varianceInCurrency) ? (float) $varianceInCurrency : 0.0;
                                $varianceBaseValue = is_numeric($varianceInBase) ? (float) $varianceInBase : 0.0;
                            @endphp

                            <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-2 text-xs">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Expected</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $symbol }} {{ $format($expectedCurrencyValue) }}
                                        <span class="ml-1 text-[10px] font-normal text-gray-500">
                                            ≈ {{ $baseSymbol }} {{ $format($expectedBaseValue) }}
                                        </span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between font-semibold {{ $varianceClass }}">
                                    <span>Variance</span>
                                    <span>
                                        {{ $symbol }} {{ $format($varianceCurrencyValue) }}
                                        <span class="ml-1 text-[10px] font-normal text-gray-500">
                                            ≈ {{ $baseSymbol }} {{ $format($varianceBaseValue) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($giftCheckTotal > 0)
                    <div class="border-t pt-3 text-sm">
                        <div class="flex items-center justify-between font-semibold text-gray-700">
                            <span>Gift Checks</span>
                            <span class="text-gray-900">{{ $baseSymbol }} {{ $format($giftCheckTotal) }}</span>
                        </div>
                    </div>
                @endif

                @if ($hasTotals)
                    @php
                        $actual = (float) ($totals['cash_in_base'] ?? $totals['actual_cash_in_base'] ?? 0);
                        $expected = (float) ($totals['expected_cash_in_base'] ?? 0);
                        $variance = (float) ($totals['variance_in_base'] ?? ($actual - $expected));

                        $varianceClass = 'text-gray-700';
                        if ($variance > 0) {
                            $varianceClass = 'text-emerald-700';
                        }
                        if ($variance < 0) {
                            $varianceClass = 'text-rose-700';
                        }
                    @endphp
                    <div class="space-y-2 rounded-lg border border-gray-200 bg-white p-3 text-sm">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Actual Cash ({{ trim($baseSymbol) }})</span>
                            <span class="font-semibold text-gray-900">{{ $baseSymbol }} {{ $format($actual) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Expected Cash</span>
                            <span class="font-semibold text-gray-900">{{ $baseSymbol }} {{ $format($expected) }}</span>
                        </div>
                        <div class="flex items-center justify-between font-semibold {{ $varianceClass }}">
                            <span>Variance</span>
                            <span>{{ $baseSymbol }} {{ $format($variance) }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($legacyIsAssociative)
            <div class="space-y-1 text-sm">
                @foreach ($legacyEntries as $denom => $count)
                    @php
                        $denomValue = is_numeric($denom) ? (float) $denom : 0.0;
                        $countValue = is_numeric($count) ? (float) $count : 0.0;
                    @endphp
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ (int) $countValue }} x {{ $format($denomValue) }} =</span>
                        <span class="font-semibold text-gray-900">{{ $format($denomValue * $countValue) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-gray-500">—</div>
        @endif
    </div>
</x-dynamic-component>
