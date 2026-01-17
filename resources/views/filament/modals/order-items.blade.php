@php
    /** @var \App\Models\Order|null $order */
    $items = $order?->orderItems ?? collect();

    $itemsByParent = $items->groupBy('parent_id');
    $rootItems = ($itemsByParent->get(null, collect())
        ->merge($itemsByParent->get(0, collect())))
        ->unique('id')
        ->sortBy('id')
        ->values();

    $renderRows = function ($item, int $level = 0) use (&$renderRows, $itemsByParent): string {
        $qty = (float) ($item->quantity ?? 0);
        $qtyLabel = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');

        $productName = $item->product?->name ?? 'Item';
        $packagingName = $item->productPackaging?->name;

        $name = $productName;
        if ($packagingName) {
            $name .= ' (' . $packagingName . ')';
        }

        $isChild = $level > 0 || ! empty($item->parent_id);
        $price = (float) ($item->price ?? 0);

        $amount = (float) ($item->sub_total ?? $item->amount ?? 0);
        $subTotal = (float) ($item->sub_total ?? 0);
        $itemDiscount = (float) ($item->discount_amount ?? 0);
        $lessVat = (float) ($item->less_tax ?? 0);
        $netAmount = (float) ($item->amount ?? $subTotal);

        $subTotalLabel = number_format($subTotal, 2);
        $itemDiscountLabel = number_format($itemDiscount, 2);
        $lessVatLabel = number_format($lessVat, 2);
        $amountLabel = number_format($netAmount, 2);

        $paddingRem = max(0, $level) * 1.0;

        $rows = '<tr class="border-b last:border-b-0">'
            . '<td class="py-2 pr-4"><span style="padding-left: ' . e((string) $paddingRem) . 'rem">'
            . ($isChild ? '• ' : '')
            . e($name)
            . '</span></td>'
            . '<td class="py-2 px-4 text-right tabular-nums whitespace-nowrap">' . e($qtyLabel) . '</td>'
            . '<td class="py-2 pl-4 text-right tabular-nums whitespace-nowrap">' . e($amountLabel) . '</td>'
            . '<td class="py-2 px-4 text-right tabular-nums whitespace-nowrap">' . e($itemDiscountLabel) . '</td>'
            . '<td class="py-2 px-4 text-right tabular-nums whitespace-nowrap">' . e($lessVatLabel) . '</td>'
            . '<td class="py-2 px-4 text-right tabular-nums whitespace-nowrap">' . e($subTotalLabel) . '</td>'
            . '</tr>';

        $children = $itemsByParent->get($item->id, collect())->sortBy('id')->values();
        foreach ($children as $child) {
            $rows .= $renderRows($child, $level + 1);
        }

        return $rows;
    };

    $rowsHtml = '';
    foreach ($rootItems as $root) {
        $rowsHtml .= $renderRows($root, 0);
    }

    // Append any orphaned children (shouldn't normally happen, but keeps output stable).
    $renderedIds = collect($rootItems)->pluck('id')->all();
    $orphaned = $items
        ->filter(fn ($item) => ! in_array($item->id, $renderedIds, true) && ! empty($item->parent_id))
        ->sortBy(fn ($item) => [$item->parent_id ?? 0, $item->id ?? 0])
        ->values();
    foreach ($orphaned as $orphan) {
        $rowsHtml .= $renderRows($orphan, 1);
    }
@endphp

<div class="space-y-3">
    @if (! $order)
        <div class="text-sm text-gray-600">
            Unable to load order items.
        </div>
    @elseif ($items->isEmpty())
        <div class="text-sm text-gray-600">
            No items found for this order.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 pr-4 text-left font-medium text-gray-700 w-1/2">Item</th>
                        <th class="py-2 px-4 text-right font-medium text-gray-700 whitespace-nowrap">Qty</th>
                        <th class="py-2 pl-4 text-right font-medium text-gray-700 whitespace-nowrap">Amount</th>
                        <th class="py-2 px-4 text-right font-medium text-gray-700 whitespace-nowrap">Item Discount</th>
                        <th class="py-2 px-4 text-right font-medium text-gray-700 whitespace-nowrap">Less VAT</th>
                        <th class="py-2 px-4 text-right font-medium text-gray-700 whitespace-nowrap">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $rowsHtml !!}
                </tbody>
            </table>
        </div>
    @endif
</div>
