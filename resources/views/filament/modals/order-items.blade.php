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
        $amountLabel = ($isChild && $price <= 0)
            ? 'Package item'
            : number_format($amount, 2);

        $paddingRem = max(0, $level) * 1.0;

        $rows = '<tr class="border-b last:border-b-0">'
            . '<td class="py-2 pr-3"><span style="padding-left: ' . e((string) $paddingRem) . 'rem">'
            . ($isChild ? '• ' : '')
            . e($name)
            . '</span></td>'
            . '<td class="py-2 text-right tabular-nums">' . e($qtyLabel) . '</td>'
            . '<td class="py-2 text-right tabular-nums">' . e($amountLabel) . '</td>'
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
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-left font-medium text-gray-700">Item</th>
                        <th class="py-2 text-right font-medium text-gray-700">Qty</th>
                        <th class="py-2 text-right font-medium text-gray-700">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $rowsHtml !!}
                </tbody>
            </table>
        </div>
    @endif
</div>
