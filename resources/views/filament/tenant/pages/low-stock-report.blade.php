<x-filament-panels::page>
    {{-- Print Styles --}}
    <style>
        @media print {
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .no-print {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
                margin: 0 !important;
            }
            body {
                font-size: 12px;
            }
            .print-only {
                display: block !important;
            }
            table {
                page-break-inside: auto;
                border-collapse: collapse;
                width: 100%;
            }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td {
                border: 1px solid #ccc;
                padding: 6px 10px;
                text-align: left;
            }
            th { background-color: #f3f4f6 !important; }
            .badge-danger { color: #dc2626 !important; font-weight: bold; }
            .badge-critical { color: #7f1d1d !important; font-weight: bold; }
        }
    </style>

    {{-- Header / Actions --}}
    <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Generated: {{ $generatedAt }}
            </p>
        </div>
        <div class="flex gap-2">
            <button
                onclick="window.open('{{ route('reports.low-stock.print') }}', '_blank')"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
        </div>
    </div>

    {{-- Print Header (only visible on print) --}}
    <div class="print-only" style="display:none; margin-bottom:16px;">
        <h1 style="font-size:18px; font-weight:bold; margin-bottom:4px;">Low Stock Report</h1>
        <p style="font-size:12px; color:#6b7280;">Generated: {{ $generatedAt }}</p>
        <hr style="margin:8px 0;">
    </div>

    @if ($items->isEmpty())
        {{-- Empty State --}}
        <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/10 dark:border-green-700 p-8 text-center">
            <svg class="mx-auto mb-3 h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-base font-semibold text-green-800 dark:text-green-300">All inventory items are adequately stocked!</h3>
            <p class="mt-1 text-sm text-green-600 dark:text-green-400">
                No items are currently at or below their low stock threshold.
                Make sure you have set <strong>Low Stock Threshold</strong> values on your inventory items.
            </p>
        </div>
    @else
        {{-- Summary Banner --}}
        <div class="mb-4 rounded-lg border border-danger-200 bg-danger-50 dark:bg-danger-900/10 dark:border-danger-700 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-danger-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="font-semibold text-danger-800 dark:text-danger-300">
                        {{ $items->count() }} {{ Str::plural('item', $items->count()) }} {{ $items->count() === 1 ? 'is' : 'are' }} running low
                    </p>
                    <p class="text-sm text-danger-600 dark:text-danger-400">
                        {{ $items->where('is_critical', true)->count() }} critical (zero stock) &bull;
                        {{ $items->where('is_critical', false)->count() }} below threshold
                    </p>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory Item</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Default Location</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Low Threshold</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Shortage</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 no-print">Stock by Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach ($items as $index => $item)
                        <tr class="{{ $item['is_critical'] ? 'bg-danger-50 dark:bg-danger-900/20' : '' }}">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item['name'] }}</span>
                                @if ($item['unit'])
                                    <span class="ml-1 text-xs text-gray-400">({{ $item['unit'] }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item['default_location'] }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $item['is_critical'] ? 'text-danger-700 dark:text-danger-400' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ rtrim(rtrim(number_format($item['total_stock'], 4, '.', ''), '0'), '.') ?: '0' }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($item['low_threshold']) }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-danger-600 dark:text-danger-400">
                                {{ rtrim(rtrim(number_format($item['shortage'], 4, '.', ''), '0'), '.') ?: '0' }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($item['is_critical'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-danger-100 dark:bg-danger-900/30 px-2.5 py-0.5 text-xs font-semibold text-danger-800 dark:text-danger-300">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        Out of Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-warning-100 dark:bg-warning-900/30 px-2.5 py-0.5 text-xs font-semibold text-warning-800 dark:text-warning-300">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Low Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 no-print">
                                <div class="space-y-0.5">
                                    @foreach ($item['location_breakdown'] as $loc)
                                        <div class="flex items-center justify-between gap-4 text-xs text-gray-600 dark:text-gray-400">
                                            <span>{{ $loc['location'] }}</span>
                                            <span class="font-medium">{{ rtrim(rtrim(number_format($loc['stock'], 4, '.', ''), '0'), '.') ?: '0' }} {{ $item['unit'] }}</span>
                                        </div>
                                    @endforeach
                                    @if (empty($item['location_breakdown']))
                                        <span class="text-xs text-gray-400">No stock recorded</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="no-print mt-3 text-xs text-gray-400">
            Showing {{ $items->count() }} item(s) at or below their low stock threshold. Report generated at {{ $generatedAt }}.
        </p>
    @endif
</x-filament-panels::page>
