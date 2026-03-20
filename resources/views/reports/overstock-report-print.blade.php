<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overstock Report – {{ $companyName }}</title>
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
            color: #d97706;
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
        .card-warning .value { color: #d97706; }
        .card-info    .value { color: #2563eb; }

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
        tbody tr.row-overstock { background: #fffbeb; }
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
        .badge-overstock { background: #fef3c7; color: #78350f; }

        .loc-wrap { font-size: 11px; color: #6b7280; }
        .loc-row  { display: flex; justify-content: space-between; gap: 12px; padding: 1px 0; }
        .loc-name { color: #6b7280; }
        .loc-qty  { font-weight: 600; color: #374151; white-space: nowrap; }

        .text-overstock { color: #d97706; font-weight: 700; }
        .text-normal    { color: #374151; }
        .text-info      { color: #2563eb; font-weight: 600; }

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
            <strong>Overstock Report</strong> &mdash; {{ $generatedAt }}
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Overstock Report
        </div>
        <div class="report-meta">Generated: {{ $generatedAt }}</div>
    </div>

    @php
        $totalExcess = $items->sum('excess');
    @endphp

    @if ($items->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#16a34a" style="margin: 0 auto 12px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3>No overstocked items detected</h3>
            <p>All inventory items are within their overstock thresholds.</p>
        </div>
    @else
        {{-- Summary cards --}}
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Overstocked Items</div>
                <div class="value">{{ $items->count() }}</div>
                <div class="sub">items exceeding threshold</div>
            </div>
            <div class="summary-card card-warning">
                <div class="label">Highest Stock</div>
                <div class="value">{{ rtrim(rtrim(number_format($items->max('total_stock'), 4, '.', ''), '0'), '.') ?: '0' }}</div>
                <div class="sub">units (single item)</div>
            </div>
            <div class="summary-card card-info">
                <div class="label">Total Excess Units</div>
                <div class="value">{{ rtrim(rtrim(number_format($totalExcess, 4, '.', ''), '0'), '.') ?: '0' }}</div>
                <div class="sub">units above threshold (combined)</div>
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
                        <th class="text-right">Overstock Threshold</th>
                        <th class="text-right">Excess Units</th>
                        <th>Status</th>
                        <th class="no-print">Stock by Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        @php
                            $fmt = fn ($n) => rtrim(rtrim(number_format((float)$n, 4, '.', ''), '0'), '.') ?: '0';
                        @endphp
                        <tr class="row-overstock">
                            <td class="text-normal">{{ $i + 1 }}</td>
                            <td>
                                <span class="item-name">{{ $item['name'] }}</span>
                                @if ($item['unit'])
                                    <span class="item-unit">({{ $item['unit'] }})</span>
                                @endif
                            </td>
                            <td class="text-normal">{{ $item['default_location'] }}</td>
                            <td class="text-right num text-info">
                                {{ $fmt($item['total_stock']) }} {{ $item['unit'] }}
                            </td>
                            <td class="text-right num text-normal">
                                {{ number_format($item['overstock_threshold']) }} {{ $item['unit'] }}
                            </td>
                            <td class="text-right num text-overstock">
                                +{{ $fmt($item['excess']) }} {{ $item['unit'] }}
                            </td>
                            <td>
                                <span class="badge badge-overstock">
                                    <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                    Overstocked
                                </span>
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
                        <td colspan="5">Total overstocked items</td>
                        <td class="text-right">{{ $items->count() }} item(s)</td>
                        <td colspan="2" class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer-note no-print">
            {{ $items->count() }} item(s) exceeding overstock threshold &bull; Report generated {{ $generatedAt }}
        </div>
    @endif

</div>
</body>
</html>
