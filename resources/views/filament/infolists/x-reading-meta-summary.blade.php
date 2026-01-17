<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState() ?? [];
        $meta = is_array($state) ? ($state['meta_data'] ?? []) : [];
        $baseSymbol = is_array($state) ? ($state['base_currency_symbol'] ?? '₱') : '₱';

        $format = function ($value): string {
            $number = is_numeric($value) ? (float) $value : 0.0;
            return number_format($number, 2);
        };

        $grossSales = (float) ($meta['gross_sales'] ?? 0);
        $discounts = is_array($meta['discounts'] ?? null) ? $meta['discounts'] : [];

        $totalDiscount = (float) ($meta['item_discount'] ?? 0);
        $lessTax = (float) ($meta['less_tax'] ?? 0);
        $netSales = (float) ($meta['net_sales'] ?? 0);
        $serviceCharge = (float) ($meta['service_charge'] ?? 0);

        $beginningCash = (float) ($state['beginning_cash'] ?? 0);
        $cashDenominationTotal = (float) ($state['cash_denomination_total'] ?? 0);
        $giftCheckTotal = (float) ($state['gift_check_total'] ?? 0);
        $expectedCash = (float) ($state['expected_cash'] ?? ($netSales + $serviceCharge));
        $variance = (float) ($state['variance'] ?? ($cashDenominationTotal - $expectedCash));

        $positive = fn ($value) => abs((float) $value);

        $vatableSales = (float) ($meta['vatable_sales'] ?? 0);
        $vatAmount = (float) ($meta['vat_amount'] ?? 0);
        $vatExemptSales = (float) ($meta['vat_exempt_sales'] ?? 0);
        $nonVatSales = (float) ($meta['non_vat_sales'] ?? 0);
        $zeroRatedSales = (float) ($meta['zero_rated_sales'] ?? 0);
    @endphp

    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['w-full max-w-xl space-y-3 text-sm'])
        }}
    >
        <div class="space-y-1">
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Gross Sales:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($grossSales) }}</span>
            </div>

            @if (! empty($discounts))
                <div class="mt-2">
                    <div class="font-semibold text-gray-700">DISCOUNT BREAKDOWN</div>
                    <div class="mt-1 border-y py-2 space-y-1">
                        @foreach ($discounts as $discount)
                            @php
                                $name = $discount['discount_name'] ?? 'Discount';
                                $amount = $discount['total_discount'] ?? 0;
                            @endphp
                            <div class="flex justify-between pl-2">
                                <span class="text-gray-600">{{ $name }}:</span>
                                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($positive($amount)) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-between mt-2">
                <span class="text-gray-600">Total Discount:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($positive($totalDiscount)) }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Less Tax:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($positive($lessTax)) }}</span>
            </div>

            <div class="flex justify-between font-semibold border-t pt-2 mt-2">
                <span class="text-gray-700">Net Sales:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($netSales) }}</span>
            </div>

            <div class="flex justify-between font-semibold border-b pb-2">
                <span class="text-gray-700">Service Charge:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($serviceCharge) }}</span>
            </div>

            <div class="flex justify-between font-semibold border-b pb-2">
                <span class="text-gray-700">Total Cash :</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($netSales + $serviceCharge) }}</span>
            </div>
        </div>

        <div class="border-t pt-3 space-y-1">
            @if ($vatableSales > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">VATable Sales:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($vatableSales) }}</span>
                </div>
            @endif
            @if ($vatAmount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">VAT Amount:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($vatAmount) }}</span>
                </div>
            @endif
            @if ($vatExemptSales > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">VAT Exempt Sales:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($vatExemptSales) }}</span>
                </div>
            @endif
            @if ($nonVatSales > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Non-VAT Sales:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($nonVatSales) }}</span>
                </div>
            @endif
            @if ($zeroRatedSales > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Zero-Rated Sales:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($zeroRatedSales) }}</span>
                </div>
            @endif
        </div>

        <div class="border-t pt-3 space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Shift No:</span>
                <span class="text-gray-900">{{ $meta['shift_no'] ?? ($state['id'] ?? '—') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Invoice Start:</span>
                <span class="text-gray-900">{{ $meta['min_invoice_no'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Invoice End:</span>
                <span class="text-gray-900">{{ $meta['max_invoice_no'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Bill Start:</span>
                <span class="text-gray-900">{{ $meta['min_bill_no'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Bill End:</span>
                <span class="text-gray-900">{{ $meta['max_bill_no'] ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="border-t pt-3 space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Refund Count:</span>
                <span class="text-gray-900">{{ (int) ($meta['refund_count'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Refund Amount:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($meta['refund_amount'] ?? 0) }}</span>
            </div>
        </div>

        <div class="border-t pt-3 space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Total Orders:</span>
                <span class="text-gray-900">{{ (int) ($meta['total_orders'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Total SKU:</span>
                <span class="text-gray-900">{{ (int) ($meta['total_sku'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Total Quantity:</span>
                <span class="text-gray-900">{{ $format($meta['total_quantity'] ?? 0) }}</span>
            </div>
        </div>

        <div class="border-t pt-3 space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Beginning Cash:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($beginningCash) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Cash Denomination:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($cashDenominationTotal) }}</span>
            </div>
            @if ($giftCheckTotal > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Gift Checks:</span>
                    <span class="text-gray-900">{{ $baseSymbol }} {{ $format($giftCheckTotal) }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-600">Expected Cash:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($expectedCash) }}</span>
            </div>
            <div class="flex justify-between font-semibold">
                <span class="text-gray-700">Variance:</span>
                <span class="text-gray-900">{{ $baseSymbol }} {{ $format($variance) }}</span>
            </div>
        </div>
    </div>
</x-dynamic-component>
