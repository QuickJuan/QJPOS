<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Report – {{ $companyName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #111827;
            background: #f9fafb;
            padding: 0;
        }

        /* ── Screen wrapper ── */
        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 28px 24px 48px;
        }

        /* ── Toolbar (hidden on print) ── */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .toolbar-meta { font-size: 13px; color: #6b7280; }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: #d97706;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-print:hover { background: #b45309; }

        /* ── Report Header ── */
        .report-header {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }
        .company-sub {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 14px;
        }
        .report-title {
            font-size: 24px;
            font-weight: 800;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .report-title svg { flex-shrink: 0; }
        .report-meta {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 6px;
        }

        /* ── Summary cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .summary-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
        .summary-card .value { font-size: 26px; font-weight: 800; color: #111827; }
        .summary-card .sub   { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .card-danger  .value { color: #dc2626; }
        .card-warning .value { color: #d97706; }
        .card-ok      .value { color: #16a34a; }

        /* ── Empty state ── */
        .empty-state {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 48px;
            text-align: center;
        }
        .empty-state h3 { font-size: 16px; font-weight: 700; color: #166534; margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; color: #15803d; }

        /* ── Table ── */
        .table-wrap {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        thead tr {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        thead th {
            padding: 11px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            white-space: nowrap;
        }
        thead th.text-right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr.row-critical { background: #fff5f5; }
        tbody tr:hover { background: #fafafa; }
        tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            color: #374151;
        }
        td.text-right { text-align: right; }
        td.num { font-variant-numeric: tabular-nums; white-space: nowrap; }

        .item-name { font-weight: 600; color: #111827; }
        .item-unit { font-size: 11px; color: #9ca3af; margin-left: 4px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-low      { background: #fef3c7; color: #92400e; }

        .loc-wrap { font-size: 11px; color: #6b7280; }
        .loc-row  { display: flex; justify-content: space-between; gap: 12px; padding: 1px 0; }
        .loc-name { color: #6b7280; }
        .loc-qty  { font-weight: 600; color: #374151; white-space: nowrap; }

        .text-critical { color: #dc2626; font-weight: 700; }
        .text-warning  { color: #d97706; font-weight: 600; }
        .text-normal   { color: #374151; }

        tfoot td {
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 700;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            color: #374151;
        }
        tfoot td.text-right { text-align: right; }

        .footer-note {
            font-size: 11px;
            color: #9ca3af;
            text-align: right;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; }
            .page-wrapper { max-width: 100%; padding: 8mm 10mm; }
            .toolbar { display: none !important; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
            .summary-card { box-shadow: none; }
            .table-wrap { box-shadow: none; }
            .no-print { display: none !important; }
            table { font-size: 11px; }
            thead th { padding: 8px 10px; }
            tbody td { padding: 8px 10px; }
            tfoot td { padding: 8px 10px; }
            tbody tr:hover { background: transparent; }
            .loc-wrap { font-size: 10px; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- Toolbar (screen only) --}}
    <div class="toolbar no-print">
        <div class="toolbar-meta">
            <strong>Low Stock Report</strong> &mdash; {{ $generatedAt }}
        </div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print / Save PDF
        </button>
    </div>

    {{-- Report header --}}
    <div class="report-header">
        @if ($companyName)
            <div class="company-name">{{ $companyName }}</div>
        @endif
        @if ($companyAddress)
            <div class="company-sub">{{ $companyAddress }}</div>
        @endif
        <div class="report-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Low Stock Report
        </div>
        <div class="report-meta">Generated: {{ $generatedAt }}</div>
    </div>

    @php
        $critical = $items->where('is_critical', true)->count();
        $belowThreshold = $items->where('is_critical', false)->count();
    @endphp

    @if ($items->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#16a34a" style="margin: 0 auto 12px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3>All inventory items are adequately stocked</h3>
            <p>No items are currently at or below their low stock threshold.</p>
        </div>
    @else
        {{-- Summary cards --}}
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Low Items</div>
                <div class="value">{{ $items->count() }}</div>
                <div class="sub">items needing attention</div>
            </div>
            <div class="summary-card card-danger">
                <div class="label">Critical (Zero Stock)</div>
                <div class="value">{{ $critical }}</div>
                <div class="sub">completely out of stock</div>
            </div>
            <div class="summary-card card-warning">
                <div class="label">Below Threshold</div>
                <div class="value">{{ $belowThreshold }}</div>
                <div class="sub">stock below minimum level</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Inventory Item</th>
                        <th>Default Location</th>
                        <th class="text-right">Total Stock</th>
                        <th class="text-right">Low Threshold</th>
                        <th class="text-right">Shortage</th>
                        <th>Status</th>
                        <th class="no-print">Stock by Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        @php
                            $fmt = fn ($n) => rtrim(rtrim(number_format((float)$n, 4, '.', ''), '0'), '.') ?: '0';
                        @endphp
                        <tr class="{{ $item['is_critical'] ? 'row-critical' : '' }}">
                            <td class="text-normal">{{ $i + 1 }}</td>
                            <td>
                                <span class="item-name">{{ $item['name'] }}</span>
                                @if ($item['unit'])
                                    <span class="item-unit">({{ $item['unit'] }})</span>
                                @endif
                            </td>
                            <td class="text-normal">{{ $item['default_location'] }}</td>
                            <td class="text-right num {{ $item['is_critical'] ? 'text-critical' : 'text-normal' }}">
                                {{ $fmt($item['total_stock']) }} {{ $item['unit'] }}
                            </td>
                            <td class="text-right num text-normal">
                                {{ number_format($item['low_threshold']) }} {{ $item['unit'] }}
                            </td>
                            <td class="text-right num text-critical">
                                {{ $fmt($item['shortage']) }} {{ $item['unit'] }}
                            </td>
                            <td>
                                @if ($item['is_critical'])
                                    <span class="badge badge-critical">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        Out of Stock
                                    </span>
                                @else
                                    <span class="badge badge-low">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Low Stock
                                    </span>
                                @endif
                            </td>
                            <td class="no-print">
                                @if (!empty($item['location_breakdown']))
                                    <div class="loc-wrap">
                                        @foreach ($item['location_breakdown'] as $loc)
                                            <div class="loc-row">
                                                <span class="loc-name">{{ $loc['location'] }}</span>
                                                <span class="loc-qty">{{ $fmt($loc['stock']) }} {{ $item['unit'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:11px;color:#9ca3af;">No stock recorded</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">Total items at or below threshold</td>
                        <td class="text-right">{{ $items->count() }} item(s)</td>
                        <td colspan="2" class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer-note no-print">
            {{ $items->count() }} item(s) at or below low stock threshold &bull; Report generated {{ $generatedAt }}
        </div>
    @endif

</div>
</body>
</html>
