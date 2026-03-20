<?php

namespace App\Http\Controllers;

use App\Models\GuestOrder;
use App\Models\GuestOrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuestOrderController extends Controller
{
    /**
     * Show the cart page (renders the Vue page; cart state lives in localStorage).
     */
    public function cart()
    {
        return Inertia::render('GuestCart/Index');
    }

    /**
     * Show the checkout page.
     */
    public function checkout()
    {
        return Inertia::render('GuestCart/Checkout');
    }

    /**
     * Process the checkout and create the guest order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'phone'       => 'required|string|max:30',
            'email'       => 'nullable|email|max:150',
            'address'     => 'nullable|string|max:500',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'order_type'  => 'required|in:advance,delivery',
            'notes'       => 'nullable|string|max:1000',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:100',
        ]);

        // Fetch products from DB to get authoritative prices (never trust client-side prices)
        $productIds = collect($validated['items'])->pluck('product_id')->unique();
        $products   = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Validate all products exist and are active
        foreach ($validated['items'] as $item) {
            if (! $products->has($item['product_id'])) {
                return back()->withErrors(['items' => 'One or more products are no longer available.']);
            }
        }

        $order = DB::transaction(function () use ($validated, $products) {
            $total = 0;

            $order = GuestOrder::create([
                'reference_no' => GuestOrder::generateReference(),
                'first_name'   => $validated['first_name'],
                'last_name'    => $validated['last_name'],
                'phone'        => $validated['phone'],
                'email'        => $validated['email'] ?? null,
                'address'      => $validated['address'] ?? null,
                'latitude'     => $validated['latitude'] ?? null,
                'longitude'    => $validated['longitude'] ?? null,
                'order_type'   => $validated['order_type'],
                'notes'        => $validated['notes'] ?? null,
                'status'       => 'pending',
                'total_amount' => 0, // updated below
            ]);

            foreach ($validated['items'] as $item) {
                $product  = $products[$item['product_id']];
                $subtotal = $product->price * $item['quantity'];
                $total   += $subtotal;

                GuestOrderItem::create([
                    'guest_order_id' => $order->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'price'          => $product->price,
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            return $order;
        });

        return redirect()->route('guest.order.confirmation', $order->reference_no);
    }

    /**
     * Show the order confirmation page.
     */
    public function confirmation(string $reference)
    {
        $order = GuestOrder::with('items')
            ->where('reference_no', $reference)
            ->firstOrFail();

        return Inertia::render('GuestCart/Confirmation', [
            'order' => [
                'reference_no' => $order->reference_no,
                'first_name'   => $order->first_name,
                'last_name'    => $order->last_name,
                'phone'        => $order->phone,
                'email'        => $order->email,
                'address'      => $order->address,
                'order_type'   => $order->order_type,
                'notes'        => $order->notes,
                'status'       => $order->status,
                'total_amount' => $order->total_amount,
                'created_at'   => $order->created_at->toIso8601String(),
                'items'        => $order->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'price'        => $i->price,
                    'quantity'     => $i->quantity,
                    'subtotal'     => $i->subtotal,
                ]),
            ],
        ]);
    }
}
