<x-filament-panels::page>
    <style>
        @media print {
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .no-print { display: none !important; }
            .fi-main { padding: 0 !important; margin: 0 !important; }
            body { font-size: 11px; }
            .print-only { display: block !important; }
            table { border-collapse: collapse; width: 100%; page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
            th { background-color: #f3f4f6 !important; font-weight: 600; }
        }
        @media screen {
            .print-only { display: none; }
        }
    </style>

    {{-- Screen toolbar --}}
    <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Generated: {{ $generatedAt }} &bull; {{ $expenses->count() }} {{ Str::plural('expense', $expenses->count()) }}
        </div>
        <button
            onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print List
        </button>
    </div>

    {{-- Print header --}}
    <div class="print-only" style="display:none; margin-bottom: 16px;">
        <h1 style="font-size: 18px; font-weight: bold; margin-bottom: 2px;">Expenses List</h1>
        <p style="color: #6b7280; font-size: 11px;">Printed: {{ $generatedAt }} &bull; {{ $expenses->count() }} record(s)</p>
        <hr style="margin: 8px 0; border-color: #d1d5db;">
    </div>

    {{-- ── Summary Stats ────────────────────────────── --}}
    @php
        $totalAmount = $expenses->sum('amount');
        $byCash      = $expenses->where('payment_method.value', 'cash')->sum('amount');
        $byBank      = $expenses->where('payment_method.value', 'bank')->sum('amount');
        $byPurchase  = $expenses->where('payment_method.value', 'purchase')->sum('amount');
        $paidCount   = $expenses->where('status.value', 'paid')->count();
        $partialCount= $expenses->where('status.value', 'partial')->count();
        $unpaidCount = $expenses->where('status.value', 'unpaid')->count();
    @endphp

    <div class="no-print grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Expenses</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">₱{{ number_format($totalAmount, 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $expenses->count() }} records</p>
        </div>
        <div class="rounded-xl border border-success-200 dark:border-success-700/40 bg-success-50 dark:bg-success-900/10 px-4 py-3 shadow-sm">
            <p class="text-xs text-success-600 dark:text-success-400">Paid</p>
            <p class="text-xl font-bold text-success-700 dark:text-success-400 mt-1">{{ $paidCount }}</p>
            <p class="text-xs text-success-500 mt-0.5">records fully paid</p>
        </div>
        <div class="rounded-xl border border-warning-200 dark:border-warning-700/40 bg-warning-50 dark:bg-warning-900/10 px-4 py-3 shadow-sm">
            <p class="text-xs text-warning-600 dark:text-warning-400">Partially Paid</p>
            <p class="text-xl font-bold text-warning-700 dark:text-warning-400 mt-1">{{ $partialCount }}</p>
            <p class="text-xs text-warning-500 mt-0.5">records partially paid</p>
        </div>
        <div class="rounded-xl border border-danger-200 dark:border-danger-700/40 bg-danger-50 dark:bg-danger-900/10 px-4 py-3 shadow-sm">
            <p class="text-xs text-danger-600 dark:text-danger-400">Unpaid</p>
            <p class="text-xl font-bold text-danger-700 dark:text-danger-400 mt-1">{{ $unpaidCount }}</p>
            <p class="text-xs text-danger-500 mt-0.5">records unpaid</p>
        </div>
    </div>

    {{-- ── Expenses Table ───────────────────────────── --}}
    @if($expenses->isEmpty())
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center text-gray-400">
            No expenses have been recorded yet.
        </div>
    @else
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Payee</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 no-print">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 no-print">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($expenses as $i => $expense)
                        @php
                            $paidAmt = $expense->payments->where('is_paid', true)->sum('amount');
                            $balance = $expense->amount - $paidAmt;
                        @endphp
                        <tr>
                            <td class="px-4 py-2.5 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $expense->title }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">{{ $expense->category?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">{{ $expense->payee ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-sm font-semibold text-right text-gray-800 dark:text-gray-200">₱{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-xs">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 font-semibold
                                    @if($expense->payment_method->value === 'cash') bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300
                                    @elseif($expense->payment_method->value === 'bank') bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-300
                                    @else bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300
                                    @endif">
                                    {{ $expense->payment_method->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-xs">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 font-semibold
                                    @if($expense->status->value === 'paid') bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300
                                    @elseif($expense->status->value === 'partial') bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300
                                    @else bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300
                                    @endif">
                                    {{ $expense->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-right text-success-700 dark:text-success-400 no-print">
                                @if($paidAmt > 0) ₱{{ number_format($paidAmt, 2) }} @else — @endif
                            </td>
                            <td class="px-4 py-2.5 text-sm text-right no-print
                                @if($balance > 0) text-danger-600 dark:text-danger-400 font-semibold
                                @else text-gray-400
                                @endif">
                                @if($balance > 0) ₱{{ number_format($balance, 2) }} @else — @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-xs font-bold uppercase text-gray-600 dark:text-gray-300">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($totalAmount, 2) }}</td>
                        <td colspan="2" class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-success-700 dark:text-success-400 no-print">
                            ₱{{ number_format($expenses->map(fn($e) => $e->payments->where('is_paid', true)->sum('amount'))->sum(), 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-danger-700 dark:text-danger-400 no-print">
                            ₱{{ number_format($expenses->map(fn($e) => max(0, $e->amount - $e->payments->where('is_paid', true)->sum('amount')))->sum(), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="no-print mt-2 text-xs text-gray-400">
            {{ $expenses->count() }} record(s) &bull; Total: ₱{{ number_format($totalAmount, 2) }} &bull; Printed {{ $generatedAt }}
        </p>
    @endif
</x-filament-panels::page>
