<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState();

        $payload = is_array($state) && array_key_exists('breakdown', $state)
            ? ($state['breakdown'] ?? [])
            : $state;

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
            <div class="space-y-4">
                @foreach ($currencies as $currency)
                    @php
                        $symbol = $currency['symbol'] ?? '';
                        $currencyName = $currency['currency_name'] ?? ($currency['currency_code'] ?? 'Cash');

                        $amountInCurrency = (float) ($currency['amount_in_currency'] ?? ($currency['total_amount'] ?? 0));
                        $exchangeRate = (float) ($currency['exchange_rate'] ?? 1);
                        $amountInBase = (float) ($currency['amount_in_base'] ?? ($currency['total_in_base'] ?? ($amountInCurrency * $exchangeRate)));

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
