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
                        $currencySymbol = (string) ($currency['currency_symbol'] ?? '');
                        $amount = (float) ($currency['amount'] ?? $currency['total'] ?? 0);
                        $amountInBase = (float) ($currency['amount_in_base'] ?? $currency['total_in_base'] ?? 0);

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
                                $qty = (float) ($denom['quantity'] ?? 0);
                                $value = (float) ($denom['value'] ?? 0);
                                $lineTotal = (float) ($denom['total'] ?? ($qty * $value));
                            @endphp

                            @if ($qty > 0)
                                <div class="denom-line">
                                    <span class="muted">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }} × {{ rtrim(rtrim(number_format($value, 2), '0'), '.') }}</span>
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
        @endphp

        <table class="kv">
            <tr>
                <td>Net Sales</td>
                <td class="right">{{ $meta['base_currency_symbol'] ?? '₱' }} {{ number_format((float) ($metaData['net_sales'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Service Charge</td>
                <td class="right">{{ $meta['base_currency_symbol'] ?? '₱' }} {{ number_format((float) ($metaData['service_charge'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Total Cash (Actual)</td>
                <td class="right bold">{{ $meta['base_currency_symbol'] ?? '₱' }} {{ number_format((float) ($meta['cash_denomination_total'] ?? 0), 2) }}</td>
            </tr>
            @if ((float) ($meta['gift_check_total'] ?? 0) > 0)
                <tr>
                    <td>Gift Checks</td>
                    <td class="right">{{ $meta['base_currency_symbol'] ?? '₱' }} {{ number_format((float) ($meta['gift_check_total'] ?? 0), 2) }}</td>
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
