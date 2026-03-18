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
            .badge-warning { color: #d97706 !important; font-weight: bold; }
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
                onclick="window.print()"
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
        <h1 style="font-size:18px; font-weight:bold; margin-bottom:4px;">Overstock Report</h1>
        <p style="font-size:12px; color:#6b7280;">Generated: {{ $generatedAt }}</p>
        <hr style="margin:8px 0;">
    </div>

    @if ($items->isEmpty())
        {{-- Empty State --}}
        <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/10 dark:border-green-700 p-8 text-center">
            <svg class="mx-auto mb-3 h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-base font-semibold text-green-800 dark:text-green-300">No overstock detected!</h3>
            <p class="mt-1 text-sm text-green-600 dark:text-green-400">
                No items are currently exceeding their overstock threshold.
                Make sure you have set <strong>Overstock Threshold</strong> values on your inventory items.
            </p>
        </div>
    @else
        {{-- Summary Banner --}}
        <div class="mb-4 rounded-lg border border-warning-200 bg-warning-50 dark:bg-warning-900/10 dark:border-warning-700 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-warning-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <div>
                    <p class="font-semibold text-warning-800 dark:text-warning-300">
                        {{ $items->count() }} {{ Str::plural('item', $items->count()) }} {{ $items->count() === 1 ? 'is' : 'are' }} overstocked
                    </p>
                    <p class="text-sm text-warning-600 dark:text-warning-400">
                        Total excess stock across all overstocked items — consider redistributing or pausing replenishment.
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
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Overstock Threshold</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Excess</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 no-print">Stock by Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach ($items as $index => $item)
                        <tr class="bg-warning-50/40 dark:bg-warning-900/10">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item['name'] }}</span>
                                    @if ($item['unit'])
                                        <span class="text-xs text-gray-400">({{ $item['unit'] }})</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1 rounded-full bg-warning-100 dark:bg-warning-900/40 px-2 py-0.5 text-xs font-semibold text-warning-800 dark:text-warning-300">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                                        Overstock
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item['default_location'] }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-warning-700 dark:text-warning-400">
                                {{ rtrim(rtrim(number_format($item['total_stock'], 4, '.', ''), '0'), '.') ?: '0' }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($item['overstock_threshold']) }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-warning-600 dark:text-warning-400">
                                +{{ rtrim(rtrim(number_format($item['excess'], 4, '.', ''), '0'), '.') ?: '0' }}
                                {{ $item['unit'] }}
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
            Showing {{ $items->count() }} item(s) exceeding their overstock threshold. Report generated at {{ $generatedAt }}.
        </p>
    @endif
</x-filament-panels::page>
