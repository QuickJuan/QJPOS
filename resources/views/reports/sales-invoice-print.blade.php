<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #111827; background: #ffffff; padding: 16px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .generated { font-size: 12px; color: #4b5563; margin-bottom: 8px; }
        .filter-list { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; color: #111827; margin-bottom: 12px; }
        .filter-label { font-weight: 600; color: #374151; }
        .filter-value { color: #111827; }
        .report-header { margin-bottom: 12px; }
        .invoice-block { margin-bottom: 16px; }
        .grand-table { margin-top: 16px; }
        .w-full { width: 100%; }
        .overflow-x-auto { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border-color: #e5e7eb; }
        .border-b { border-bottom: 1px solid #e5e7eb; }
        .border-t { border-top: 1px solid #e5e7eb; }
        .font-medium, .font-semibold { font-weight: 600; }
        .text-gray-700 { color: #374151; }
        .text-gray-600 { color: #4b5563; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-xs { font-size: 12px; }
        .text-sm { font-size: 14px; }
        .text-[11px] { font-size: 11px; }
        .whitespace-nowrap { white-space: nowrap; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .align-top { vertical-align: top; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-3 { padding-top: 12px; padding-bottom: 12px; }
        .px-2 { padding-left: 8px; padding-right: 8px; }
        .text-sm.text-gray-600 { font-size: 13px; }
        .border-b.last\:border-b-0:last-child { border-bottom: 0; }
        @media print {
            body { padding: 6mm; }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Sales Invoice Report</h1>
        <div class="generated">Generated: {{ now()->format('M d, Y h:i A') }}</div>
        @if ($filterSummary)
            <div class="filter-list">
                @foreach ($filterSummary as $label => $value)
                    <div class="filter-item"><span class="filter-label">{{ $label }}:</span> <span class="filter-value">{{ $value }}</span></div>
                @endforeach
            </div>
        @endif
    </div>

    @forelse ($orders as $order)
        <div class="invoice-block">
            @include('filament.tables.columns.sales-invoice-items', ['record' => $order])
        </div>
    @empty
        <p>No invoices match the selected filters.</p>
    @endforelse

    @if (! empty($grandTotals))
        @php
            $formatCurrency = fn ($value) => number_format((float) $value, 2);
            $formatQty = function ($value): string {
                $formatted = number_format((float) $value, 2, '.', '');
                return rtrim(rtrim($formatted, '0'), '.') ?: '0';
            };
        @endphp
        <div class="grand-table overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 text-[11px]"></th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]"></th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Qty</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Amount</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Less Tax</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Discount</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Sales</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Cost</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Profit</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t font-semibold">
                        <td class="py-2 px-2 text-[11px]">Grand Totals</td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-[11px]"></td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatQty($grandTotals['qty'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['amount'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['less_tax'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['discount'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['sales'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['cost'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['profit'] ?? 0) }}</td>
                        <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($grandTotals['payment'] ?? 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <script>
        window.print();
    </script>
</body>
</html>
