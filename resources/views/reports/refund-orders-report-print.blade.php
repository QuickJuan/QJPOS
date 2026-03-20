<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Orders Report – {{ $companyName }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:#111827;background:#f9fafb}
        .page-wrapper{max-width:1200px;margin:0 auto;padding:28px 24px 48px}
        .toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding:12px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .toolbar-meta{font-size:13px;color:#6b7280}
        .btn-print{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;background:#ea580c;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .btn-print:hover{background:#c2410c}
        .report-header{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .company-name{font-size:20px;font-weight:700;color:#111827;margin-bottom:2px}
        .company-sub{font-size:12px;color:#6b7280;margin-bottom:14px}
        .report-title{font-size:24px;font-weight:800;color:#ea580c;display:flex;align-items:center;gap:10px}
        .report-meta{font-size:12px;color:#9ca3af;margin-top:6px}
        .filter-row{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:10px}
        .filter-item{font-size:12px;color:#374151}
        .filter-label{font-weight:700}
        .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
        .summary-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .summary-card .label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:4px}
        .summary-card .value{font-size:26px;font-weight:800;color:#111827}
        .summary-card .sub{font-size:11px;color:#9ca3af;margin-top:2px}
        .card-orange .value{color:#ea580c}
        .card-red .value{color:#dc2626}
        .table-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px}
        table{width:100%;border-collapse:collapse;font-size:12px}
        thead tr{background:#fff7ed;border-bottom:2px solid #fed7aa}
        thead th{padding:10px 12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9a3412;white-space:nowrap}
        thead th.r{text-align:right}
        tbody tr{border-bottom:1px solid #f3f4f6}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:#fafafa}
        tbody td{padding:10px 12px;vertical-align:middle;color:#374151}
        td.r{text-align:right}
        td.num{font-variant-numeric:tabular-nums;white-space:nowrap}
        .badge-refund{display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:700;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa}
        .invoice-no{font-family:monospace;font-size:12px;color:#4f46e5;font-weight:600}
        tfoot td{padding:11px 12px;font-size:12px;font-weight:700;background:#fff7ed;border-top:2px solid #fed7aa;color:#374151}
        tfoot td.r{text-align:right}
        .footer-note{font-size:11px;color:#9ca3af;text-align:right}
        .empty-state{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:48px;text-align:center;color:#6b7280}
        @media print{
            body{background:#fff}
            .page-wrapper{max-width:100%;padding:8mm 6mm}
            .toolbar{display:none!important}
            .summary-card,.table-wrap{box-shadow:none}
            .no-print{display:none!important}
            table{font-size:10px}
            thead th,tbody td,tfoot td{padding:7px 8px}
            tbody tr:hover{background:transparent}
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="toolbar no-print">
        <div class="toolbar-meta"><strong>Refund Orders Report</strong> &mdash; {{ $generatedAt }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="report-header">
        @if($companyName)<div class="company-name">{{ $companyName }}</div>@endif
        @if($companyAddress)<div class="company-sub">{{ $companyAddress }}</div>@endif
        <div class="report-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            Refund Orders Report
        </div>
        <div class="report-meta">Generated: {{ $generatedAt }}</div>
        @if($filterSummary)
        <div class="filter-row">
            @foreach($filterSummary as $label => $value)
                <span class="filter-item"><span class="filter-label">{{ $label }}:</span> {{ $value }}</span>
            @endforeach
        </div>
        @endif
    </div>

    @if($orders->isEmpty())
        <div class="empty-state">No refund orders found for the selected filters.</div>
    @else
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Refund Orders</div>
                <div class="value">{{ number_format($orders->count()) }}</div>
                <div class="sub">orders refunded</div>
            </div>
            <div class="summary-card card-orange">
                <div class="label">Total Sub Total</div>
                <div class="value">₱{{ number_format($totalSubTotal, 2) }}</div>
                <div class="sub">before deductions</div>
            </div>
            <div class="summary-card card-red">
                <div class="label">Total Refunded</div>
                <div class="value">₱{{ number_format($totalDue, 2) }}</div>
                <div class="sub">final refund amounts</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Date</th>
                        <th>Invoice No.</th>
                        <th>Branch</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th class="r">Sub Total</th>
                        <th class="r">Discount / Less</th>
                        <th class="r">Svc Charge</th>
                        <th class="r">Total Refund</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $i => $order)
                    @php
                        $deductions = ($order->item_discount ?? 0) + ($order->less_tax ?? 0);
                        $totalRefund = ($order->total_due ?? 0) + ($order->service_charge ?? 0);
                    @endphp
                    <tr>
                        <td style="color:#9ca3af;font-size:11px">{{ $i + 1 }}</td>
                        <td class="num" style="font-size:12px">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                        <td><span class="invoice-no">{{ $order->invoice_no }}</span></td>
                        <td style="font-weight:600;color:#111827">{{ $order->branch?->name ?? '–' }}</td>
                        <td>{{ $order->customer?->name ?? 'Walk-in' }}</td>
                        <td style="color:#6b7280;font-size:12px">{{ $order->cashier?->name ?? '–' }}</td>
                        <td class="r num">₱{{ number_format($order->total_amount, 2) }}</td>
                        <td class="r num" style="color:#d97706">₱{{ number_format($deductions, 2) }}</td>
                        <td class="r num">₱{{ number_format($order->service_charge ?? 0, 2) }}</td>
                        <td class="r num" style="font-weight:700;color:#dc2626">₱{{ number_format($totalRefund, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">Total</td>
                        <td class="r">₱{{ number_format($totalSubTotal, 2) }}</td>
                        <td class="r">₱{{ number_format($totalDeductions, 2) }}</td>
                        <td class="r">₱{{ number_format($totalServiceCharge, 2) }}</td>
                        <td class="r">₱{{ number_format($totalDue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="footer-note no-print">{{ $orders->count() }} refund order(s) &bull; Report generated {{ $generatedAt }}</div>
    @endif
</div>
</body>
</html>
