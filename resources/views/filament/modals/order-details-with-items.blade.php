@php
    /** @var \App\Models\Order|null $order */
@endphp

<div class="space-y-4">
    @if (! $order)
        <div class="text-sm text-gray-600">
            Unable to load order details.
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
            <div>
                <div class="text-gray-600">Receipt #</div>
                <div class="font-medium">{{ $order->invoice_no ?: $order->id }}</div>
            </div>
            <div>
                <div class="text-gray-600">Date</div>
                <div class="font-medium">{{ optional($order->created_at)->format('M d, Y h:i A') }}</div>
            </div>
            <div>
                <div class="text-gray-600">Branch</div>
                <div class="font-medium">{{ $order->branch?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-600">Cashier</div>
                <div class="font-medium">{{ $order->cashier?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-600">Customer</div>
                <div class="font-medium">{{ $order->customer?->name ?? 'Walk-in' }}</div>
            </div>
            <div>
                <div class="text-gray-600">Status</div>
                <div class="font-medium">{{ ucfirst((string) ($order->status ?? '-')) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Sub Total</div>
                <div class="font-medium">₱{{ number_format((float) ($order->total_amount ?? 0), 2) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Item Discount</div>
                <div class="font-medium">₱{{ number_format((float) ($order->item_discount ?? 0), 2) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Less VAT</div>
                <div class="font-medium">₱{{ number_format((float) ($order->less_tax ?? 0), 2) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Service Charge</div>
                <div class="font-medium">₱{{ number_format((float) ($order->service_charge ?? 0), 2) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Total Due</div>
                <div class="font-medium">₱{{ number_format((float) (($order->total_due ?? 0) + ($order->service_charge ?? 0)), 2) }}</div>
            </div>
        </div>

        <div class="border-t pt-4">
            @include('filament.modals.order-items', ['order' => $order])
        </div>
    @endif
</div>
