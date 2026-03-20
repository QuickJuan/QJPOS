<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Sales Item Report – {{ $companyName }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:#111827;background:#f9fafb}
        .page-wrapper{max-width:1400px;margin:0 auto;padding:28px 24px 48px}
        .toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding:12px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .toolbar-meta{font-size:13px;color:#6b7280}
        .btn-print{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;background:#0f766e;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .btn-print:hover{background:#0d7170}
        .report-header{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .company-name{font-size:20px;font-weight:700;color:#111827;margin-bottom:2px}
        .company-sub{font-size:12px;color:#6b7280;margin-bottom:14px}
        .report-title{font-size:24px;font-weight:800;color:#0f766e;display:flex;align-items:center;gap:10px}
        .report-meta{font-size:12px;color:#9ca3af;margin-top:6px}
        .filter-row{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:10px}
        .filter-item{font-size:12px;color:#374151}
        .filter-label{font-weight:700}
        .summary-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px}
        .summary-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .summary-card .label{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:4px}
        .summary-card .value{font-size:20px;font-weight:800;color:#111827}
        .summary-card .sub{font-size:10px;color:#9ca3af;margin-top:2px}
        .card-teal .value{color:#0f766e}
        .card-green .value{color:#16a34a}
        .card-orange .value{color:#d97706}
        .card-red .value{color:#dc2626}
        .card-indigo .value{color:#4f46e5}
        .table-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:11px}
        thead tr{background:#f0fdfa;border-bottom:2px solid #99f6e4}
        thead th{padding:9px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#0f766e;white-space:nowrap}
        thead th.r{text-align:right}
        thead th.c{text-align:center}
        tbody tr{border-bottom:1px solid #f3f4f6}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:#fafafa}
        tbody tr.child-row{background:#fafff9}
        tbody td{padding:9px 10px;vertical-align:middle;color:#374151}
        td.r{text-align:right}
        td.c{text-align:center}
        td.num{font-variant-numeric:tabular-nums;white-space:nowrap}
        .badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:700}
        .badge-settled{background:#dcfce7;color:#166534}
        .badge-refund{background:#fff7ed;color:#ea580c}
        .badge-dine-in{background:#f0fdf4;color:#15803d}
        .badge-takeout{background:#fffbeb;color:#b45309}
        .badge-delivery{background:#eff6ff;color:#1d4ed8}
        .badge-default{background:#f3f4f6;color:#6b7280}
        .invoice-no{font-family:monospace;font-size:10px;color:#4f46e5}
        .child-indicator{color:#9ca3af;padding-left:12px;font-size:10px}
        tfoot td{padding:9px 10px;font-size:11px;font-weight:700;background:#f0fdfa;border-top:2px solid #99f6e4;color:#374151}
        tfoot td.r{text-align:right}
        .footer-note{font-size:11px;color:#9ca3af;text-align:right}
        .empty-state{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:48px;text-align:center;color:#6b7280}
        @media print{
            body{background:#fff}
            .page-wrapper{max-width:100%;padding:5mm 5mm}
            .toolbar{display:none!important}
            .summary-card,.table-wrap{box-shadow:none}
            .no-print{display:none!important}
            table{font-size:9px}
            thead th,tbody td,tfoot td{padding:6px 7px}
            tbody tr:hover{background:transparent}
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="toolbar no-print">
        <div class="toolbar-meta"><strong>Detailed Sales Item Report</strong> &mdash; {{ $generatedAt }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="report-header">
        @if($companyName)<div class="company-name">{{ $companyName }}</div>@endif
        @if($companyAddress)<div class="company-sub">{{ $companyAddress }}</div>@endif
        <div class="report-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Detailed Sales Item Report
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
            <div class="summary-card card-teal">
                <div class="label">Total Line Items</div>
                <div class="value">{{ number_format($items->count()) }}</div>
                <div class="sub">rows in report</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Qty</div>
                <div class="value">{{ number_format($totalQty) }}</div>
                <div class="sub">units sold</div>
            </div>
            <div class="summary-card card-green">
                <div class="label">Total Amount</div>
                <div class="value">₱{{ number_format($totalAmount, 2) }}</div>
                <div class="sub">gross line amounts</div>
            </div>
            <div class="summary-card card-orange">
                <div class="label">Total Discount</div>
                <div class="value">₱{{ number_format($totalDiscount, 2) }}</div>
                <div class="sub">item discounts</div>
            </div>
            <div class="summary-card card-indigo">
                <div class="label">Total Sub Total</div>
                <div class="value">₱{{ number_format($totalSubTotal, 2) }}</div>
                <div class="sub">net line totals</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Branch</th>
                        <th>Order Date</th>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Product</th>
                        <th class="c">Qty</th>
                        <th class="r">Price</th>
                        <th class="r">Amount</th>
                        <th class="r">Discount</th>
                        <th class="r">Less Tax</th>
                        <th class="r">Sub Total</th>
                        <th>Cashier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    @php
                        $isChild = !empty($item->parent_id);
                        $price = (float) ($item->price ?? 0);
                        $priceDisplay = ($isChild && $price <= 0) ? 'Package item' : '₱' . number_format($price, 2);
                        $orderType = $item->order_type;
                        $typeBadgeClass = match($orderType) {
                            'dine-in'  => 'badge-dine-in',
                            'takeout'  => 'badge-takeout',
                            'delivery' => 'badge-delivery',
                            default    => 'badge-default',
                        };
                        $statusClass = match($item->order?->status) {
                            'settled' => 'badge-settled',
                            'refund'  => 'badge-refund',
                            default   => 'badge-default',
                        };
                    @endphp
                    <tr class="{{ $isChild ? 'child-row' : '' }}">
                        <td style="color:#9ca3af">{{ $i + 1 }}</td>
                        <td style="font-weight:600;color:#111827">{{ $item->order?->branch?->name ?? '–' }}</td>
                        <td class="num" style="font-size:10px">{{ $item->order?->created_at ? \Carbon\Carbon::parse($item->order->created_at)->format('M d, Y h:i A') : '–' }}</td>
                        <td><span class="invoice-no">{{ $item->order?->invoice_no ?? '–' }}</span></td>
                        <td>
                            @if($orderType)
                                <span class="badge {{ $typeBadgeClass }}">{{ ucwords(str_replace('-', ' ', $orderType)) }}</span>
                            @else
                                <span style="color:#9ca3af">–</span>
                            @endif
                        </td>
                        <td class="{{ $isChild ? 'child-indicator' : '' }}" style="font-weight:{{ $isChild ? '400' : '500' }}">
                            {{ $isChild ? '↳ ' : '' }}{{ $item->product?->name ?? '–' }}
                        </td>
                        <td class="c num" style="font-weight:600">{{ number_format($item->quantity) }}</td>
                        <td class="r num">{{ $priceDisplay }}</td>
                        <td class="r num">₱{{ number_format($item->amount ?? 0, 2) }}</td>
                        <td class="r num" style="color:#d97706">₱{{ number_format($item->item_discount ?? 0, 2) }}</td>
                        <td class="r num" style="color:#d97706">₱{{ number_format($item->less_tax ?? 0, 2) }}</td>
                        <td class="r num" style="font-weight:700;color:#0f766e">₱{{ number_format($item->sub_total ?? 0, 2) }}</td>
                        <td style="color:#6b7280;font-size:10px">{{ $item->order?->cashier?->name ?? '–' }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ ucfirst($item->order?->status ?? '–') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">Total ({{ $items->count() }} items)</td>
                        <td class="c">{{ number_format($totalQty) }}</td>
                        <td class="r">–</td>
                        <td class="r">₱{{ number_format($totalAmount, 2) }}</td>
                        <td class="r">₱{{ number_format($totalDiscount, 2) }}</td>
                        <td class="r">₱{{ number_format($totalLessTax, 2) }}</td>
                        <td class="r">₱{{ number_format($totalSubTotal, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="footer-note no-print">{{ $items->count() }} item(s) &bull; Report generated {{ $generatedAt }}</div>
    @endif
</div>
</body>
</html>
