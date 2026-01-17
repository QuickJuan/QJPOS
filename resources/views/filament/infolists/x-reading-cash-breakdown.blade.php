<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState();

        $payload = is_array($state) && array_key_exists('breakdown', $state)
            ? ($state['breakdown'] ?? [])
            : $state;

        $cashComparison = is_array($state) ? ($state['cash_comparison'] ?? []) : [];
        $otherPaymentsComparison = is_array($state) ? ($state['other_payments_comparison'] ?? []) : [];
        $allComparisons = array_merge(is_array($cashComparison) ? $cashComparison : [], is_array($otherPaymentsComparison) ? $otherPaymentsComparison : []);

        $comparisonsByMethod = [];
        foreach ($allComparisons as $row) {
            if (! is_array($row)) {
                continue;
            }

            $methodId = $row['payment_method_id'] ?? null;
            if ($methodId === null) {
                continue;
            }

            $comparisonsByMethod[(string) $methodId] = $row;
        }

        $baseSymbol = is_array($payload) ? ($payload['base_currency_symbol'] ?? '₱') : '₱';

        $format = function ($value): string {
            $number = is_numeric($value) ? (float) $value : 0.0;
            return number_format($number, 2);
        };

        $isStructured = is_array($payload) && array_key_exists('currencies', $payload) && is_array($payload['currencies']);
        $currencies = $isStructured ? ($payload['currencies'] ?? []) : [];
        $totals = $isStructured && is_array($payload['totals'] ?? null) ? $payload['totals'] : null;

        $giftCheckTotal = 0.0;
        if ($isStructured) {
            $giftCheckTotal = (float) ($payload['gift_check_total'] ?? ($totals['gift_check_in_base'] ?? 0) ?? 0);
        }

        $legacyEntries = (! $isStructured && is_array($payload)) ? $payload : [];
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($currencies as $currency)
                    @php
                        $symbol = $currency['symbol'] ?? '';
                        $currencyName = $currency['currency_name'] ?? ($currency['currency_code'] ?? 'Cash');

                        $amountInCurrency = (float) ($currency['amount_in_currency'] ?? ($currency['total_amount'] ?? 0));
                        $exchangeRate = (float) ($currency['exchange_rate'] ?? 1);
                        $amountInBase = (float) ($currency['amount_in_base'] ?? ($currency['total_in_base'] ?? ($amountInCurrency * $exchangeRate)));

                        $paymentMethodId = $currency['payment_method_id'] ?? null;
                        $comparison = $paymentMethodId !== null ? ($comparisonsByMethod[(string) $paymentMethodId] ?? null) : null;

                        $expectedInCurrency = is_array($comparison) ? ($comparison['expected_amount_in_currency'] ?? null) : null;
                        $actualInCurrency = is_array($comparison) ? ($comparison['actual_amount_in_currency'] ?? null) : null;
                        $varianceInCurrency = is_array($comparison) ? ($comparison['variance_in_currency'] ?? null) : null;

                        $expectedInBase = is_array($comparison) ? ($comparison['expected_amount_in_base'] ?? null) : null;
                        $actualInBase = is_array($comparison) ? ($comparison['actual_amount_in_base'] ?? null) : null;
                        $varianceInBase = is_array($comparison) ? ($comparison['variance_in_base'] ?? null) : null;

                        // For non-cash comparisons, currency amounts might not exist. Try derive from base using exchange rate.
                        if (! is_numeric($expectedInCurrency) && is_numeric($expectedInBase) && $exchangeRate > 0) {
                            $expectedInCurrency = ((float) $expectedInBase) / $exchangeRate;
                        }
                        if (! is_numeric($varianceInCurrency) && is_numeric($varianceInBase) && $exchangeRate > 0) {
                            $varianceInCurrency = ((float) $varianceInBase) / $exchangeRate;
                        }

                        $hasComparison = is_numeric($expectedInBase) || is_numeric($varianceInBase) || is_numeric($expectedInCurrency) || is_numeric($varianceInCurrency);

                        $denominations = is_array($currency['denominations'] ?? null) ? $currency['denominations'] : [];
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                            <span>{{ $currencyName }}</span>
                            <span class="text-right text-gray-900">
                                {{ $symbol }} {{ $format($amountInCurrency) }}
                                @if (round((float) $amountInCurrency, 2) !== round((float) $amountInBase, 2))
                                    <span class="text-xs font-normal text-gray-500">
                                        ≈ {{ $baseSymbol }} {{ $format($amountInBase) }}
                                    </span>
                                @endif
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
                                $varianceBaseValue = is_numeric($varianceInBase) ? (float) $varianceInBase : ($varianceCurrencyValue * $exchangeRate);

                                $showExpectedApprox = round($expectedCurrencyValue, 2) !== round($expectedBaseValue, 2);
                                $showVarianceApprox = round($varianceCurrencyValue, 2) !== round($varianceBaseValue, 2);
                            @endphp

                            <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-2 text-xs">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Expected</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $symbol }} {{ $format($expectedCurrencyValue) }}
                                        @if ($showExpectedApprox)
                                            <span class="ml-1 text-[10px] font-normal text-gray-500">
                                                ≈ {{ $baseSymbol }} {{ $format($expectedBaseValue) }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between font-semibold {{ $varianceClass }}">
                                    <span>Variance</span>
                                    <span>
                                        {{ $symbol }} {{ ($varianceCurrencyValue < 0 ? '-' : '') . $format(abs($varianceCurrencyValue)) }}
                                        @if ($showVarianceApprox)
                                            <span class="ml-1 text-[10px] font-normal text-gray-500">
                                                ≈ {{ $baseSymbol }} {{ ($varianceBaseValue < 0 ? '-' : '') . $format(abs($varianceBaseValue)) }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            @if ($giftCheckTotal > 0)
                <div class="border-t pt-3 text-sm">
                    <div class="flex items-center justify-between font-semibold text-gray-700">
                        <span>Gift Checks</span>
                        <span class="text-gray-900">{{ $baseSymbol }} {{ $format($giftCheckTotal) }}</span>
                    </div>
                </div>
            @endif
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
