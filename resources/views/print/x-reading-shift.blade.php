<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>X-Reading Shift #{{ $session->id }}</title>

    <style>
        /* Default: Long bond paper (8.5" x 13") */
        @page {
            size: 8.5in 13in;
            margin: 0.5in;
        }

        html, body {
            padding: 0;
            margin: 0;
            color: #111827;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            font-size: 12px;
        }

        .row {
            display: flex;
            gap: 12px;
        }

        .col {
            flex: 1;
            min-width: 0;
        }

        .h1 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .muted {
            color: #6b7280;
        }

        .box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            text-align: left;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }

        .right { text-align: right; }
        .bold { font-weight: 700; }

        .section-title {
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .kv {
            width: 100%;
        }
        .kv td {
            padding: 2px 0;
        }
        .kv td:first-child {
            color: #374151;
            width: 45%;
        }

        .denom-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
        }

        .no-print {
            display: none;
        }

        @media screen {
            body {
                background: #f3f4f6;
                padding: 16px;
            }
            .paper {
                background: #fff;
                max-width: 8.5in;
                margin: 0 auto;
                padding: 0.5in;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
            }
            .no-print {
                display: block;
                margin-bottom: 10px;
            }
            .btn {
                appearance: none;
                border: 1px solid #d1d5db;
                background: #fff;
                border-radius: 8px;
                padding: 8px 10px;
                cursor: pointer;
                font-size: 12px;
            }
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="paper">
    <div class="no-print">
        <button class="btn" onclick="window.print()">Print</button>
    </div>

    <div>
        <p class="h1">X-Reading (Cashier Shift) — Shift #{{ $session->id }}</p>
        <p class="muted" style="margin: 0;">
            Branch: {{ $session->branch?->name ?? '—' }} · Cashier: {{ $session->cashier?->name ?? '—' }} · Business Date: {{ optional($session->business_date)->format('M d, Y') ?? '—' }}
        </p>
        <p class="muted" style="margin: 0;">
            Started: {{ optional($session->started_time)->format('M d, Y h:i A') ?? '—' }} · Closed: {{ optional($session->closing_time)->format('M d, Y h:i A') ?? '—' }}
        </p>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col box">
            <p class="section-title">Financial Summary</p>
            <table>
                <tr>
                    <td>Beginning Cash</td>
                    <td class="right bold">₱ {{ number_format((float) ($session->beginning_cash ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Total Sales</td>
                    <td class="right bold">₱ {{ number_format((float) ($session->total_sales ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Closing Cash</td>
                    <td class="right bold">₱ {{ number_format((float) ($session->closing_cash ?? 0), 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="col box">
            <p class="section-title">Cash Denomination</p>

            @php
                $currencies = [];
                $baseSymbol = (string) ($breakdown['base_currency_symbol'] ?? '₱');

                if (isset($breakdown['currencies']) && is_array($breakdown['currencies'])) {
                    $currencies = $breakdown['currencies'];
                }
            @endphp

            @if (!empty($currencies))
                @foreach ($currencies as $currency)
                    @php
                        $currencyName = (string) ($currency['currency_name'] ?? $currency['currency_code'] ?? 'Currency');
                        $currencySymbol = (string) ($currency['symbol'] ?? ($currency['currency_symbol'] ?? ''));

                        $exchangeRate = (float) ($currency['exchange_rate'] ?? 1);
                        $amount = (float) ($currency['amount_in_currency'] ?? ($currency['total_amount'] ?? ($currency['amount'] ?? ($currency['total'] ?? 0))));
                        $amountInBase = (float) ($currency['amount_in_base'] ?? ($currency['total_in_base'] ?? ($exchangeRate > 0 ? ($amount * $exchangeRate) : 0)));

                        $denoms = [];
                        if (isset($currency['denominations']) && is_array($currency['denominations'])) {
                            $denoms = $currency['denominations'];
                        }

                        $showConversion = round($amount, 2) !== round($amountInBase, 2);
                    @endphp

                    <div style="margin-bottom: 8px;">
                        <div class="denom-line">
                            <span class="bold">{{ $currencyName }}</span>
                            <span class="bold">
                                {{ $currencySymbol }} {{ number_format($amount, 2) }}
                                @if ($showConversion)
                                    <span class="muted">≈ {{ $baseSymbol }} {{ number_format($amountInBase, 2) }}</span>
                                @endif
                            </span>
                        </div>

                        @foreach ($denoms as $denom)
                            @php
                                $qty = (float) ($denom['count'] ?? ($denom['quantity'] ?? 0));
                                $label = $denom['label'] ?? ($denom['denomination_label'] ?? null);
                                $value = (float) ($denom['value'] ?? ($denom['denomination_value'] ?? 0));
                                $lineTotal = (float) ($denom['total'] ?? ($qty * $value));

                                $displayLabel = is_string($label) && $label !== ''
                                    ? $label
                                    : rtrim(rtrim(number_format($value, 2), '0'), '.');
                            @endphp

                            @if ($qty > 0)
                                <div class="denom-line">
                                    <span class="muted">{{ (int) $qty }} × {{ $displayLabel }}</span>
                                    <span>{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            @else
                <p class="muted" style="margin: 0;">No cash denomination data.</p>
            @endif
        </div>
    </div>

    <div class="box">
        <p class="section-title">Session Details</p>

        @php
            $metaData = is_array($meta['meta_data'] ?? null) ? $meta['meta_data'] : [];
            $cashComparison = is_array($meta['cash_comparison'] ?? null) ? $meta['cash_comparison'] : [];
            $otherComparison = is_array($meta['other_payments_comparison'] ?? null) ? $meta['other_payments_comparison'] : [];

            $baseSymbol = (string) ($meta['base_currency_symbol'] ?? '₱');

            $format = function ($value): string {
                $number = is_numeric($value) ? (float) $value : 0.0;
                return number_format($number, 2);
            };

            $positive = fn ($value) => abs((float) $value);

            $grossSales = (float) ($metaData['gross_sales'] ?? 0);
            $discounts = is_array($metaData['discounts'] ?? null) ? $metaData['discounts'] : [];
            $totalDiscount = (float) ($metaData['item_discount'] ?? 0);
            $lessTax = (float) ($metaData['less_tax'] ?? 0);
            $netSales = (float) ($metaData['net_sales'] ?? 0);
            $serviceCharge = (float) ($metaData['service_charge'] ?? 0);

            $vatableSales = (float) ($metaData['vatable_sales'] ?? 0);
            $vatAmount = (float) ($metaData['vat_amount'] ?? 0);
            $vatExemptSales = (float) ($metaData['vat_exempt_sales'] ?? 0);
            $nonVatSales = (float) ($metaData['non_vat_sales'] ?? 0);
            $zeroRatedSales = (float) ($metaData['zero_rated_sales'] ?? 0);
        @endphp

        <table class="kv">
            <tr>
                <td class="bold">Gross Sales</td>
                <td class="right">{{ $baseSymbol }} {{ $format($grossSales) }}</td>
            </tr>

            @if (! empty($discounts))
                <tr>
                    <td colspan="2" class="bold" style="padding-top: 8px;">DISCOUNT BREAKDOWN</td>
                </tr>
                @foreach ($discounts as $discount)
                    @php
                        $name = $discount['discount_name'] ?? 'Discount';
                        $amount = $discount['total_discount'] ?? 0;
                    @endphp
                    <tr>
                        <td class="muted">{{ $name }}:</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($positive($amount)) }}</td>
                    </tr>
                @endforeach
            @endif

            <tr>
                <td class="muted">Total Discount</td>
                <td class="right">{{ $baseSymbol }} {{ $format($positive($totalDiscount)) }}</td>
            </tr>
            <tr>
                <td class="muted">Less Tax</td>
                <td class="right">{{ $baseSymbol }} {{ $format($positive($lessTax)) }}</td>
            </tr>
            <tr>
                <td class="bold">Net Sales</td>
                <td class="right bold">{{ $baseSymbol }} {{ $format($netSales) }}</td>
            </tr>
            <tr>
                <td class="bold">Service Charge</td>
                <td class="right bold">{{ $baseSymbol }} {{ $format($serviceCharge) }}</td>
            </tr>
            <tr>
                <td class="bold">Total</td>
                <td class="right bold">{{ $baseSymbol }} {{ $format($netSales + $serviceCharge) }}</td>
            </tr>

            @if ($vatableSales > 0 || $vatAmount > 0 || $vatExemptSales > 0 || $nonVatSales > 0 || $zeroRatedSales > 0)
                <tr><td colspan="2" style="padding-top: 8px;"></td></tr>
                @if ($vatableSales > 0)
                    <tr>
                        <td class="muted">VATable Sales</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($vatableSales) }}</td>
                    </tr>
                @endif
                @if ($vatAmount > 0)
                    <tr>
                        <td class="muted">VAT Amount</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($vatAmount) }}</td>
                    </tr>
                @endif
                @if ($vatExemptSales > 0)
                    <tr>
                        <td class="muted">VAT Exempt Sales</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($vatExemptSales) }}</td>
                    </tr>
                @endif
                @if ($nonVatSales > 0)
                    <tr>
                        <td class="muted">Non-VAT Sales</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($nonVatSales) }}</td>
                    </tr>
                @endif
                @if ($zeroRatedSales > 0)
                    <tr>
                        <td class="muted">Zero-Rated Sales</td>
                        <td class="right">{{ $baseSymbol }} {{ $format($zeroRatedSales) }}</td>
                    </tr>
                @endif
            @endif

            <tr><td colspan="2" style="padding-top: 8px;"></td></tr>
            <tr>
                <td class="muted">Shift No</td>
                <td class="right">{{ $metaData['shift_no'] ?? ($meta['id'] ?? $session->id ?? '—') }}</td>
            </tr>
            <tr>
                <td class="muted">Invoice Start</td>
                <td class="right">{{ $metaData['min_invoice_no'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="muted">Invoice End</td>
                <td class="right">{{ $metaData['max_invoice_no'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="muted">Bill Start</td>
                <td class="right">{{ $metaData['min_bill_no'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="muted">Bill End</td>
                <td class="right">{{ $metaData['max_bill_no'] ?? 'N/A' }}</td>
            </tr>

            <tr><td colspan="2" style="padding-top: 8px;"></td></tr>
            <tr>
                <td class="muted">Refund Count</td>
                <td class="right">{{ (int) ($metaData['refund_count'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="muted">Refund Amount</td>
                <td class="right">{{ $baseSymbol }} {{ $format($metaData['refund_amount'] ?? 0) }}</td>
            </tr>

            <tr><td colspan="2" style="padding-top: 8px;"></td></tr>
            <tr>
                <td class="muted">Total Orders</td>
                <td class="right">{{ (int) ($metaData['total_orders'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="muted">Total SKU</td>
                <td class="right">{{ (int) ($metaData['total_sku'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="muted">Total Quantity</td>
                <td class="right">{{ $format($metaData['total_quantity'] ?? 0) }}</td>
            </tr>

            <tr><td colspan="2" style="padding-top: 8px;"></td></tr>
            <tr>
                <td class="muted">Beginning Cash</td>
                <td class="right">{{ $baseSymbol }} {{ $format($meta['beginning_cash'] ?? ($session->beginning_cash ?? 0)) }}</td>
            </tr>
            <tr>
                <td class="muted">Cash Denomination</td>
                <td class="right">{{ $baseSymbol }} {{ $format($meta['cash_denomination_total'] ?? 0) }}</td>
            </tr>
            @if ((float) ($meta['gift_check_total'] ?? 0) > 0)
                <tr>
                    <td class="muted">Gift Checks</td>
                    <td class="right">{{ $baseSymbol }} {{ $format($meta['gift_check_total'] ?? 0) }}</td>
                </tr>
            @endif
        </table>

        @if (!empty($cashComparison))
            <div style="margin-top: 10px;">
                <p class="section-title">Cash Comparison</p>
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th class="right">Actual</th>
                            <th class="right">Expected</th>
                            <th class="right">Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cashComparison as $row)
                            @php
                                $method = (string) ($row['payment_method_name'] ?? $row['payment_method'] ?? 'Cash');
                                $base = (string) ($meta['base_currency_symbol'] ?? '₱');
                                $actual = (float) ($row['actual_amount_in_base'] ?? 0);
                                $expected = (float) ($row['expected_amount_in_base'] ?? 0);
                                $variance = (float) ($row['variance_in_base'] ?? ($actual - $expected));
                            @endphp
                            <tr>
                                <td>{{ $method }}</td>
                                <td class="right">{{ $base }} {{ number_format($actual, 2) }}</td>
                                <td class="right">{{ $base }} {{ number_format($expected, 2) }}</td>
                                <td class="right">{{ $base }} {{ number_format($variance, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (!empty($otherComparison))
            <div style="margin-top: 10px;">
                <p class="section-title">Other Payments Comparison</p>
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th class="right">Actual</th>
                            <th class="right">Expected</th>
                            <th class="right">Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($otherComparison as $row)
                            @php
                                $method = (string) ($row['payment_method_name'] ?? $row['payment_method'] ?? 'Payment');
                                $base = (string) ($meta['base_currency_symbol'] ?? '₱');
                                $actual = (float) ($row['actual_amount_in_base'] ?? 0);
                                $expected = (float) ($row['expected_amount_in_base'] ?? 0);
                                $variance = (float) ($row['variance_in_base'] ?? ($actual - $expected));
                            @endphp
                            <tr>
                                <td>{{ $method }}</td>
                                <td class="right">{{ $base }} {{ number_format($actual, 2) }}</td>
                                <td class="right">{{ $base }} {{ number_format($expected, 2) }}</td>
                                <td class="right">{{ $base }} {{ number_format($variance, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    // Trigger the print dialog automatically when opened from Filament.
    window.addEventListener('load', () => {
        setTimeout(() => {
            try { window.print(); } catch (e) {}
        }, 150);
    });
</script>
</body>
</html>
