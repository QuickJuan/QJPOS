<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Seller Report – {{ $companyName }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:#111827;background:#f9fafb}
        .page-wrapper{max-width:1000px;margin:0 auto;padding:28px 24px 48px}
        .toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding:12px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .toolbar-meta{font-size:13px;color:#6b7280}
        .btn-print{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;background:#d97706;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .btn-print:hover{background:#b45309}
        .report-header{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .company-name{font-size:20px;font-weight:700;color:#111827;margin-bottom:2px}
        .company-sub{font-size:12px;color:#6b7280;margin-bottom:14px}
        .report-title{font-size:24px;font-weight:800;color:#d97706;display:flex;align-items:center;gap:10px}
        .report-meta{font-size:12px;color:#9ca3af;margin-top:6px}
        .filter-row{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:10px}
        .filter-item{font-size:12px;color:#374151}<br>.filter-label{font-weight:700}
        .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
        .summary-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .summary-card .label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:4px}
        .summary-card .value{font-size:26px;font-weight:800;color:#111827}
        .summary-card .sub{font-size:11px;color:#9ca3af;margin-top:2px}
        .card-success .value{color:#16a34a}
        .card-amber .value{color:#d97706}
        .table-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        thead tr{background:#f3f4f6;border-bottom:2px solid #e5e7eb}
        thead th{padding:11px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap}
        thead th.r{text-align:right}
        tbody tr{border-bottom:1px solid #f3f4f6}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:#fafafa}
        tbody td{padding:11px 14px;vertical-align:middle;color:#374151}
        td.r{text-align:right}
        td.num{font-variant-numeric:tabular-nums;white-space:nowrap}
        .rank{font-size:12px;color:#9ca3af;font-weight:600}
        .badge-rank{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:9999px;font-size:11px;font-weight:800}
        .rank-1{background:#fef9c3;color:#713f12}
        .rank-2{background:#f3f4f6;color:#374151}
        .rank-3{background:#fed7aa;color:#7c2d12}
        tfoot td{padding:12px 14px;font-size:12px;font-weight:700;background:#f9fafb;border-top:2px solid #e5e7eb;color:#374151}
        tfoot td.r{text-align:right}
        .footer-note{font-size:11px;color:#9ca3af;text-align:right}
        .empty-state{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:48px;text-align:center;color:#6b7280}
        @media print{
            body{background:#fff}
            .page-wrapper{max-width:100%;padding:8mm 10mm}
            .toolbar{display:none!important}
            .summary-card,.table-wrap{box-shadow:none}
            .no-print{display:none!important}
            table{font-size:11px}
            thead th,tbody td,tfoot td{padding:8px 10px}
            tbody tr:hover{background:transparent}
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="toolbar no-print">
        <div class="toolbar-meta"><strong>Best Seller Report</strong> &mdash; {{ $generatedAt }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="report-header">
        @if($companyName)<div class="company-name">{{ $companyName }}</div>@endif
        @if($companyAddress)<div class="company-sub">{{ $companyAddress }}</div>@endif
        <div class="report-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            Best Seller Report
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

    @if($items->isEmpty())
        <div class="empty-state">No records found for the selected filters.</div>
    @else
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Products</div>
                <div class="value">{{ $items->count() }}</div>
                <div class="sub">unique products ranked</div>
            </div>
            <div class="summary-card card-amber">
                <div class="label">Total Qty Sold</div>
                <div class="value">{{ number_format($totalQtySold) }}</div>
                <div class="sub">units across all products</div>
            </div>
            <div class="summary-card card-success">
                <div class="label">Total Net Sales</div>
                <div class="value">₱{{ number_format($totalNetSales, 2) }}</div>
                <div class="sub">combined net revenue</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px">Rank</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th class="r">Qty Sold</th>
                        <th class="r">Net Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    <tr>
                        <td>
                            @if($i < 3)
                                <span class="badge-rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : 'rank-3') }}">{{ $i + 1 }}</span>
                            @else
                                <span class="rank">{{ $i + 1 }}</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color:#111827">{{ $item->product_name }}</td>
                        <td style="color:#6b7280;font-size:12px">{{ $item->branch?->name ?? 'N/A' }}</td>
                        <td class="r num" style="font-weight:700;color:#d97706">{{ number_format($item->qty_sold) }}</td>
                        <td class="r num" style="font-weight:600;color:#16a34a">₱{{ number_format($item->net_sales, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="r">{{ number_format($totalQtySold) }}</td>
                        <td class="r">₱{{ number_format($totalNetSales, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="footer-note no-print">{{ $items->count() }} product(s) &bull; Report generated {{ $generatedAt }}</div>
    @endif
</div>
</body>
</html>
