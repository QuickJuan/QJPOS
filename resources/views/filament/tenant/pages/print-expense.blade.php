<x-filament-panels::page>
    <style>
        @media print {
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .no-print { display: none !important; }
            .fi-main { padding: 0 !important; margin: 0 !important; }
            body { font-size: 12px; }
            .print-only { display: block !important; }
            table { border-collapse: collapse; width: 100%; page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
            th { background-color: #f3f4f6 !important; font-weight: 600; }
            .section-title { font-size: 13px; font-weight: 700; margin: 14px 0 6px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
            .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
            .badge-success { background: #dcfce7; color: #166534; }
            .badge-warning { background: #fef9c3; color: #854d0e; }
            .badge-danger  { background: #fee2e2; color: #991b1b; }
            .badge-info    { background: #dbeafe; color: #1e40af; }
        }
        @media screen {
            .print-only { display: none; }
        }
    </style>

    {{-- Screen toolbar --}}
    <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-gray-500 dark:text-gray-400">Generated: {{ $generatedAt }}</div>
        <button
            onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print
        </button>
    </div>

    {{-- Print header --}}
    <div class="print-only" style="margin-bottom: 16px;">
        <h1 style="font-size: 18px; font-weight: bold; margin-bottom: 2px;">Expense Record</h1>
        <p style="color: #6b7280; font-size: 11px;">Printed: {{ $generatedAt }}</p>
        <hr style="margin: 8px 0; border-color: #d1d5db;">
    </div>

    {{-- ── Expense Summary Card ─────────────────────── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden mb-5">
        <div class="bg-gray-50 dark:bg-gray-800 px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $expense->title }}</h2>
            @php
                $status = $expense->status;
                $statusColors = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'danger'];
                $methodColors = ['cash' => 'success', 'bank' => 'info', 'purchase' => 'warning'];
                $badgeColor = $statusColors[$status->value] ?? 'gray';
                $methodColor = $methodColors[$expense->payment_method->value] ?? 'gray';
            @endphp
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                @if($status->value === 'paid') bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300
                @elseif($status->value === 'partial') bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300
                @else bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300
                @endif">
                {{ $status->label() }}
            </span>
        </div>

        <div class="px-5 py-4 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Expense Date</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $expense->expense_date->format('F d, Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Category</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $expense->category?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Payee / Vendor</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $expense->payee ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Total Amount</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($expense->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Payment Method</p>
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                    @if($expense->payment_method->value === 'cash') bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300
                    @elseif($expense->payment_method->value === 'bank') bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-300
                    @else bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300
                    @endif">
                    {{ $expense->payment_method->label() }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Recorded By</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $expense->recordedBy?->name ?? '—' }}</p>
            </div>
            @if($expense->description)
            <div class="col-span-2 md:col-span-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Description</p>
                <p class="text-gray-800 dark:text-gray-200">{{ $expense->description }}</p>
            </div>
            @endif
            @if($expense->notes)
            <div class="col-span-2 md:col-span-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Internal Notes</p>
                <p class="text-gray-700 dark:text-gray-300 italic">{{ $expense->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Payment Schedule (Installments) ─────────── --}}
    @if($expense->payments->isNotEmpty())
    <div class="mb-5">
        <h3 class="section-title no-print text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
            Payment Schedule
        </h3>
        @php
            $paidTotal     = $expense->payments->where('is_paid', true)->sum('amount');
            $pendingTotal  = $expense->payments->where('is_paid', false)->sum('amount');
        @endphp
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Due Date</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Amount</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date Paid</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Reference #</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Remarks</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Paid By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($expense->payments as $i => $payment)
                    <tr class="{{ $payment->is_paid ? '' : 'bg-danger-50/40 dark:bg-danger-900/10' }}">
                        <td class="px-4 py-2.5 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $payment->due_date->format('M d, Y') }}</td>
                        <td class="px-4 py-2.5 text-sm text-right font-semibold text-gray-800 dark:text-gray-200">₱{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-4 py-2.5">
                            @if($payment->is_paid)
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-100 dark:bg-success-900/30 px-2 py-0.5 text-xs font-semibold text-success-800 dark:text-success-300">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Paid
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-danger-100 dark:bg-danger-900/30 px-2 py-0.5 text-xs font-semibold text-danger-800 dark:text-danger-300">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">{{ $payment->paid_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 font-mono">{{ $payment->reference_number ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">{{ $payment->remarks ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">{{ $payment->paidBy?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="2" class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Total</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold text-gray-800 dark:text-gray-100">₱{{ number_format($expense->payments->sum('amount'), 2) }}</td>
                        <td colspan="5" class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400">
                            Paid: <strong class="text-success-700 dark:text-success-400">₱{{ number_format($paidTotal, 2) }}</strong>
                            &bull; Pending: <strong class="text-danger-700 dark:text-danger-400">₱{{ number_format($pendingTotal, 2) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Attachments ──────────────────────────────── --}}
    @php
        $attachments = $expense->getMedia('expense_attachments');
    @endphp
    @if($attachments->isNotEmpty())
    <div class="mb-5">
        <h3 class="no-print text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
            Attachments ({{ $attachments->count() }})
        </h3>
        <div class="print-only section-title">Attachments</div>

        {{-- Image grid (screen) --}}
        <div class="no-print grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($attachments as $media)
                <a href="{{ $media->getUrl() }}" target="_blank" class="group block rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:border-primary-400 transition">
                    @if(str_starts_with($media->mime_type, 'image/'))
                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="h-28 w-full object-cover" />
                        <p class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 truncate group-hover:text-primary-600">{{ $media->name }}</p>
                    @else
                        <div class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 truncate group-hover:text-primary-600">{{ $media->name }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Attachment list (print) --}}
        <div class="print-only" style="display: none;">
            <ul style="margin: 0; padding-left: 16px; font-size: 11px; color: #374151;">
                @foreach($attachments as $media)
                    <li style="margin-bottom: 3px;">{{ $media->name }} ({{ $media->human_readable_size }})</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <p class="no-print mt-2 text-xs text-gray-400">Expense #{{ $expense->id }} &bull; Printed {{ $generatedAt }}</p>
</x-filament-panels::page>
