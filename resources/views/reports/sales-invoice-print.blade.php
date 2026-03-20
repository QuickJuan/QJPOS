<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice Report – {{ $companyName ?? 'QuickJuan POS' }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:#111827;background:#f9fafb}
        .page-wrapper{max-width:1600px;margin:0 auto;padding:28px 24px 48px;overflow-x:auto}
        .toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding:12px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .toolbar-meta{font-size:13px;color:#6b7280}
        .btn-print{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;background:#0f766e;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .btn-print:hover{background:#0d6460}
        .report-header{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .company-name{font-size:20px;font-weight:700;color:#111827;margin-bottom:2px}
        .company-sub{font-size:12px;color:#6b7280;margin-bottom:14px}
        .report-title{font-size:24px;font-weight:800;color:#0f766e;display:flex;align-items:center;gap:10px}
        .report-meta{font-size:12px;color:#9ca3af;margin-top:6px}
        .filter-row{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:10px}
        .filter-item{font-size:12px;color:#374151}
        .filter-label{font-weight:700}
        .summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
        .summary-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .summary-card .label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:4px}
        .summary-card .value{font-size:26px;font-weight:800;color:#111827}
        .summary-card .sub{font-size:11px;color:#9ca3af;margin-top:2px}
        .card-teal .value{color:#0f766e}
        .card-amber .value{color:#d97706}
        .card-blue .value{color:#1d4ed8}
        .invoice-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:14px}
        .invoice-overflow{overflow-x:auto}
        .grand-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)}
        .grand-section-header{background:#0f766e;padding:12px 16px}
        .grand-section-header h3{font-size:14px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.05em}
        .grand-table{width:100%;border-collapse:collapse;font-size:12px}
        .grand-table thead tr{background:#f0fdfa;border-bottom:2px solid #99f6e4}
        .grand-table thead th{padding:10px 12px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#0f766e;white-space:nowrap}
        .grand-table thead th:first-child{text-align:left}
        .grand-table tbody td{padding:12px;color:#374151;font-weight:700;font-size:13px;font-variant-numeric:tabular-nums;white-space:nowrap}
        .grand-table tbody td.r{text-align:right}
        .empty-state{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:48px;text-align:center;color:#6b7280;font-size:14px}
        .footer-note{margin-top:16px;font-size:11px;color:#9ca3af;text-align:right}
        /* Inherit the invoice sub-blade table styles */
        .w-full{width:100%}
        .overflow-x-auto{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:12px}
        th,td{border-color:#e5e7eb}
        .border-b{border-bottom:1px solid #e5e7eb}
        .border-t{border-top:1px solid #e5e7eb}
        .font-medium,.font-semibold{font-weight:600}
        .text-gray-700{color:#374151}
        .text-gray-600{color:#4b5563}
        .text-left{text-align:left}
        .text-right{text-align:right}
        .text-xs{font-size:12px}
        .text-sm{font-size:14px}
        .text-\[11px\]{font-size:11px}
        .whitespace-nowrap{white-space:nowrap}
        .tabular-nums{font-variant-numeric:tabular-nums}
        .align-top{vertical-align:top}
        .py-2{padding-top:8px;padding-bottom:8px}
        .py-3{padding-top:12px;padding-bottom:12px}
        .px-2{padding-left:8px;padding-right:8px}
        thead tr{background:#f9fafb;border-bottom:2px solid #e5e7eb}
        thead th{padding:9px 10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;white-space:nowrap}
        tbody tr{border-bottom:1px solid #f3f4f6}
        tbody tr:last-child{border-bottom:none}
        tbody td{padding:9px 10px;color:#374151}
        .last\:border-b-0:last-child{border-bottom:0}
        @page{size:legal landscape;margin:8mm 10mm}
        @media print{
            body{background:#fff}
            .page-wrapper{max-width:100%;padding:0}
            .toolbar{display:none!important}
            .invoice-card,.grand-section,.summary-card{box-shadow:none;border-radius:0}
            table{font-size:9.5px}
            thead th,tbody td{padding:5px 6px}
            .grand-table thead th,.grand-table tbody td{padding:6px 8px}
            .invoice-card{page-break-inside:avoid;break-inside:avoid;margin-bottom:8px}
            tbody tr:hover{background:transparent}
            .summary-grid{gap:8px;margin-bottom:12px}
            .summary-card{padding:10px 12px}
            .summary-card .value{font-size:20px}
            .report-header{padding:12px 16px;margin-bottom:12px}
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- Toolbar (hidden on print) --}}
    <div class="toolbar">
        <div class="toolbar-meta"><strong>Sales Invoice Report</strong> &mdash; {{ $generatedAt ?? now()->format('M d, Y h:i A') }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    {{-- Report Header --}}
    <div class="report-header">
        @if(!empty($companyName))<div class="company-name">{{ $companyName }}</div>@endif
        @if(!empty($companyAddress))<div class="company-sub">{{ $companyAddress }}</div>@endif
        <div class="report-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Sales Invoice Report
        </div>
        <div class="report-meta">Generated: {{ $generatedAt ?? now()->format('M d, Y h:i A') }}</div>
        @if($filterSummary)
        <div class="filter-row">
            @foreach($filterSummary as $label => $value)
                <span class="filter-item"><span class="filter-label">{{ $label }}:</span> {{ $value }}</span>
            @endforeach
        </div>
        @endif
    </div>

    @php
        $formatCurrency = fn ($value) => number_format((float) $value, 2);
        $formatQty = function ($value): string {
            $formatted = number_format((float) $value, 2, '.', '');
            return rtrim(rtrim($formatted, '0'), '.') ?: '0';
        };
        $totalOrders = $orders->count();
        $totalItems  = $orders->sum(fn ($o) => $o->orderItems->count());
    @endphp

    @if($orders->isEmpty())
        <div class="empty-state">No invoices match the selected filters.</div>
    @else
        {{-- Summary Cards --}}
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Invoices</div>
                <div class="value">{{ number_format($totalOrders) }}</div>
                <div class="sub">orders in this report</div>
            </div>
            <div class="summary-card card-blue">
                <div class="label">Total Line Items</div>
                <div class="value">{{ number_format($totalItems) }}</div>
                <div class="sub">across all invoices</div>
            </div>
            <div class="summary-card card-amber">
                <div class="label">Total Qty Sold</div>
                <div class="value">{{ $formatQty($grandTotals['qty'] ?? 0) }}</div>
                <div class="sub">units sold</div>
            </div>
            <div class="summary-card card-teal">
                <div class="label">Grand Net Sales</div>
                <div class="value">&#8369;{{ $formatCurrency($grandTotals['sales'] ?? 0) }}</div>
                <div class="sub">after discounts &amp; tax</div>
            </div>
        </div>

        {{-- Invoice Blocks – data format retained exactly as required --}}
        @foreach ($orders as $order)
            <div class="invoice-card">
                @include('filament.tables.columns.sales-invoice-items', ['record' => $order])
            </div>
        @endforeach

        {{-- Grand Totals --}}
        @if(!empty($grandTotals))
        <div class="grand-section" style="margin-top:20px">
            <div class="grand-section-header">
                <h3>Grand Totals &mdash; All Invoices</h3>
            </div>
            <table class="grand-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Summary</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Less Tax</th>
                        <th>Discount</th>
                        <th>Net Sales</th>
                        <th>Cost</th>
                        <th>Profit</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $totalOrders }} invoice{{ $totalOrders !== 1 ? 's' : '' }}, {{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}</td>
                        <td class="r num">{{ $formatQty($grandTotals['qty'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['amount'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['less_tax'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['discount'] ?? 0) }}</td>
                        <td class="r num" style="color:#0f766e">&#8369;{{ $formatCurrency($grandTotals['sales'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['cost'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['profit'] ?? 0) }}</td>
                        <td class="r num">&#8369;{{ $formatCurrency($grandTotals['payment'] ?? 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer-note">{{ $companyName ?? '' }} &mdash; Sales Invoice Report &mdash; {{ $generatedAt ?? now()->format('M d, Y h:i A') }}</div>
    @endif

</div>
<script>window.print();</script>
</body>
</html>
