@php
    /** @var \App\Models\Order|null $record */
    $order = null;

    if (isset($record) && ! $record instanceof \Closure) {
        $order = $record;
    }

    if (! $order && isset($state) && ! $state instanceof \Closure) {
        $order = $state;
    }

    if (! $order && isset($getRecord) && is_callable($getRecord)) {
        $order = $getRecord();
    }

    $items = $order?->orderItems ?? collect();
    $rowspan = $items->count() > 0 ? $items->count() + 1 : 1; // include totals row

    $formatQty = function ($value): string {
        $formatted = number_format((float) $value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    };

    $formatCurrency = fn ($value) => number_format((float) $value, 2);

    $meta = [
        'date' => optional($order?->created_at)->format('M d, Y h:i A') ?? '-',
        'receipt' => $order?->invoice_no ?: $order?->id ?: '-',
        'branch' => $order?->branch?->name ?? '-',
        'cashier' => $order?->cashier?->name ?? '-',
        'session' => $order?->cashierSession?->id ? ('Shift #' . $order->cashierSession->id) : '-',
        'customer' => $order?->customer?->name ?? 'Walk-in',
        'status' => ucfirst((string) ($order->status ?? '-')),
    ];

    $totals = [
        'qty' => (float) $items->sum(fn ($item) => (float) ($item->quantity ?? 0)),
        'amount' => (float) $items->sum(fn ($item) => (float) ($item->amount ?? (($item->price ?? 0) * ($item->quantity ?? 0)))),
        'less_tax' => (float) $items->sum(fn ($item) => (float) ($item->less_tax ?? 0)),
        'discount' => (float) $items->sum(fn ($item) => (float) ($item->discount_amount ?? $item->item_discount ?? 0)),
        'sales' => (float) $items->sum(fn ($item) => (float) ($item->sub_total ?? $item->amount ?? 0)),
        'cost' => (float) $items->sum(fn ($item) => (float) ($item->cost ?? 0)),
        'profit' => (float) $items->sum(fn ($item) => (float) ($item->profit ?? 0)),
    ];

    $paymentsTotal = $order?->payments?->sum(fn ($payment) => (float) ($payment->amount_applied ?? $payment->amount ?? 0)) ?? 0.0;
@endphp

<div class="w-full">
    @if (! $order)
        <div class="text-sm text-gray-600">Unable to load invoice.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Date</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Receipt #</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Branch</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Cashier</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Shift #</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Customer</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Status</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 whitespace-nowrap text-[11px]">Item Code</th>
                        <th class="py-2 px-2 text-left font-medium text-gray-700 text-[11px]">Description</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Price</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Qty</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Amount</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Less Tax</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Discount</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Sales</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Cost</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Profit</th>
                        <th class="py-2 px-2 text-right font-medium text-gray-700 whitespace-nowrap text-[11px]">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($items->isEmpty())
                        <tr>
                            <td class="py-3 px-2 text-sm text-gray-600" colspan="14">No items found for this invoice.</td>
                        </tr>
                    @else
                        @php $first = true; @endphp
                        @foreach ($items as $item)
                            @php
                                $qty = $formatQty($item->quantity ?? 0);
                                $productName = $item->product?->receipt_alias ?: $item->product?->name ?: 'Item';
                                if (is_array($productName)) {
                                    $productName = implode(' ', array_map('strval', array_filter($productName, fn ($v) => is_scalar($v) || (is_object($v) && method_exists($v, '__toString')))));
                                }

                                $packagingName = $item->productPackaging?->name;
                                if (is_array($packagingName)) {
                                    $packagingName = implode(' ', array_map('strval', array_filter($packagingName, fn ($v) => is_scalar($v) || (is_object($v) && method_exists($v, '__toString')))));
                                }

                                $description = (string) $productName;
                                if ($packagingName) {
                                    $description .= ' (' . (string) $packagingName . ')';
                                }

                                $code = $item->productPackaging?->barcode
                                    ?? $item->product?->barcode
                                    ?? ($item->product_id ? ('PRD-' . $item->product_id) : 'N/A');

                                $lessTax = $formatCurrency($item->less_tax ?? 0);
                                $price = $formatCurrency($item->price ?? 0);
                                $amount = $formatCurrency($item->amount ?? ($item->price ?? 0) * ($item->quantity ?? 0));
                                $discount = $formatCurrency($item->discount_amount ?? $item->item_discount ?? 0);
                                $sales = $formatCurrency($item->sub_total ?? $item->amount ?? 0);
                                $cost = $formatCurrency($item->cost ?? 0);
                                $profit = $formatCurrency($item->profit ?? 0);
                            @endphp

                            <tr class="border-b last:border-b-0">
                                @if ($first)
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['date'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['receipt'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['branch'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['cashier'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['session'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['customer'] }}</td>
                                    <td class="py-2 px-2 align-top text-[11px]" rowspan="{{ $rowspan }}">{{ $meta['status'] }}</td>
                                    @php $first = false; @endphp
                                @endif

                                <td class="py-2 px-2 text-left align-top whitespace-nowrap text-[11px]">{{ $code }}</td>
                                <td class="py-2 px-2 text-left align-top text-[11px]">{{ $description }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $price }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $qty }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $amount }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $lessTax }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $discount }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $sales }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $cost }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $profit }}</td>
                                <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]"></td>
                            </tr>
                        @endforeach

                        <tr class="border-t font-semibold">
                            <td class="py-2 px-2 text-[11px] ">Totals</td>
                            <td class="py-2 px-2 text-[11px]"></td>
                            <td class="py-2 px-2 text-[11px]"></td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatQty($totals['qty']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['amount']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['less_tax']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['discount']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['sales']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['cost']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($totals['profit']) }}</td>
                            <td class="py-2 px-2 text-right tabular-nums whitespace-nowrap text-[11px]">{{ $formatCurrency($paymentsTotal) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
