<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CouponCode;
use App\Models\CouponUsage;
use App\Models\Product;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuestOrderController extends Controller
{
    public function cart()
    {
        return Inertia::render('GuestCart/Index', [
            'appName' => config('app.name'),
        ]);
    }

    public function checkout()
    {
        return Inertia::render('GuestCart/Checkout', [
            'appName' => config('app.name'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'phone'       => 'required|string|max:30',
            'address'     => 'required|string|max:500',
            'email'       => 'nullable|email|max:150',
            'order_type'  => 'required|in:pickup,delivery',
            'notes'       => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:255',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:100',
        ]);

        $branch = $this->resolvePublicBranch();

        if (! $branch) {
            return back()->withErrors(['items' => 'No branch is available to receive online orders yet.']);
        }

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

        $couponData = $this->resolveCouponData(
            $validated['coupon_code'] ?? null,
            $validated['items'],
            $products,
            $request->session()->getId(),
        );

        $cart = DB::transaction(function () use ($validated, $products, $branch, $couponData, $request) {
            $referenceNo = $this->generateReferenceNo();
            $internalOrderType = $this->mapGuestOrderTypeToCartOrderType($validated['order_type']);

            $cartAttributes = [
                'branch_id'     => $branch->id,
                'coupon_id'     => $couponData['coupon']?->id,
                'coupon_code'   => $couponData['coupon']?->code,
                'total_discount' => $couponData['discount_amount'],
                'notes'         => $validated['notes'] ?? null,
                'meta_data'     => [
                    'guest_checkout' => [
                        'reference_no' => $referenceNo,
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'email' => $validated['email'] ?? null,
                        'status' => 'pending',
                        'order_type' => $validated['order_type'],
                        'internal_order_type' => $internalOrderType,
                        'notes' => $validated['notes'] ?? null,
                        'submitted_at' => now()->toIso8601String(),
                        'coupon' => $couponData['coupon'] ? [
                            'id' => $couponData['coupon']->id,
                            'code' => $couponData['coupon']->code,
                            'name' => $couponData['coupon']->name,
                            'discount_amount' => $couponData['discount_amount'],
                        ] : null,
                    ],
                ],
            ];

            if ($this->cartColumnExists('source')) {
                $cartAttributes['source'] = 'customer';
            }

            if ($this->cartColumnExists('reference_no')) {
                $cartAttributes['reference_no'] = $referenceNo;
            }

            if ($this->cartColumnExists('submitted_at')) {
                $cartAttributes['submitted_at'] = now();
            }

            $cart = Cart::create($cartAttributes);

            foreach ($validated['items'] as $item) {
                $product  = $products[$item['product_id']];
                $this->createCartItem($cart, $product, (int) $item['quantity'], $internalOrderType);
            }

            if ($couponData['coupon']) {
                CouponUsage::create([
                    'coupon_id' => $couponData['coupon']->id,
                    'discount_amount' => (int) round($couponData['discount_amount'] * 100),
                    'session_id' => $request->session()->getId(),
                    'ip_address' => $request->ip(),
                    'cart_data' => [
                        'reference_no' => $referenceNo,
                        'items' => $validated['items'],
                    ],
                ]);

                $couponData['coupon']->increment('used_count');
            }

            return $cart;
        });

        $this->notifyStaffOfOnlineOrder($cart);

        return redirect()->route('guest.order.confirmation', $referenceNo = $this->getCartReferenceNo($cart));
    }

    public function confirmation(string $reference)
    {
        $cart = Cart::with(['cartItems.product'])
            ->when(
                $this->cartColumnExists('reference_no'),
                fn ($query) => $query->where('reference_no', $reference),
                fn ($query) => $query->where('meta_data->guest_checkout->reference_no', $reference)
            )
            ->firstOrFail();

        return Inertia::render('GuestCart/Confirmation', [
            'order' => [
                'reference_no' => $this->getCartReferenceNo($cart),
                'name'         => data_get($cart->meta_data, 'guest_checkout.name'),
                'phone'        => data_get($cart->meta_data, 'guest_checkout.phone'),
                'email'        => data_get($cart->meta_data, 'guest_checkout.email'),
                'address'      => data_get($cart->meta_data, 'guest_checkout.address'),
                'order_type'   => data_get($cart->meta_data, 'guest_checkout.order_type'),
                'notes'        => data_get($cart->meta_data, 'guest_checkout.notes'),
                'status'       => $this->getCartProcessedAt($cart) ? 'processed' : 'pending',
                'discount_amount' => (float) ($cart->total_discount ?? 0),
                'total_amount' => (float) max(0, $cart->cartItems->sum('amount') - ($cart->total_discount ?? 0)),
                'created_at'   => $cart->created_at->toIso8601String(),
                'items'        => $cart->cartItems->map(fn ($item) => [
                    'product_name' => $item->product?->name ?? $item->description,
                    'price'        => (float) $item->price,
                    'quantity'     => (float) $item->quantity,
                    'subtotal'     => (float) $item->amount,
                ])->values(),
                'confirmation_message' => 'We will inform you once your order is received, confirmed, and ready.',
            ],
            'appName' => config('app.name'),
        ]);
    }

    private function resolveCouponData(?string $couponCode, array $items, $products, string $sessionId): array
    {
        if (! $couponCode) {
            return [
                'coupon' => null,
                'discount_amount' => 0,
            ];
        }

        $coupon = CouponCode::query()
            ->active()
            ->valid()
            ->whereRaw('LOWER(code) = ?', [strtolower($couponCode)])
            ->first();

        if (! $coupon) {
            abort(422, 'Coupon code is invalid or inactive.');
        }

        $subtotalInCents = 0;
        $productIds = [];
        $categoryIds = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                continue;
            }

            $productIds[] = $product->id;
            if ($product->category_id) {
                $categoryIds[] = $product->category_id;
            }

            $subtotalInCents += (int) round(((float) $product->price * (int) $item['quantity']) * 100);
        }

        if ($coupon->minimum_amount && $subtotalInCents < $coupon->minimum_amount) {
            abort(422, 'Coupon minimum amount has not been met.');
        }

        if (! $coupon->isApplicableToProducts(array_values(array_unique($productIds)))) {
            abort(422, 'Coupon does not apply to the selected products.');
        }

        if (! $coupon->isApplicableToCategories(array_values(array_unique($categoryIds)))) {
            abort(422, 'Coupon does not apply to the selected categories.');
        }

        if ($coupon->hasUserReachedLimit(null, $sessionId)) {
            abort(422, 'Coupon usage limit has been reached for this session.');
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => round($coupon->calculateDiscount($subtotalInCents) / 100, 2),
        ];
    }

    private function resolvePublicBranch(): ?Branch
    {
        return Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first()
            ?? Branch::query()->orderBy('id')->first();
    }

    private function generateReferenceNo(): string
    {
        do {
            $referenceNo = 'QJ-' . now()->format('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (
            $this->cartColumnExists('reference_no')
                ? Cart::where('reference_no', $referenceNo)->exists()
                : false
        );

        return $referenceNo;
    }

    private function mapGuestOrderTypeToCartOrderType(string $orderType): string
    {
        return $orderType === 'pickup' ? 'takeout' : 'delivery';
    }

    private function cartColumnExists(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= Schema::hasColumn('carts', $column);
    }

    private function getCartReferenceNo(Cart $cart): ?string
    {
        return $this->cartColumnExists('reference_no')
            ? $cart->reference_no
            : data_get($cart->meta_data, 'guest_checkout.reference_no');
    }

    private function getCartProcessedAt(Cart $cart): mixed
    {
        return $this->cartColumnExists('processed_at')
            ? $cart->processed_at
            : data_get($cart->meta_data, 'guest_checkout.processed_at');
    }

    private function createCartItem(Cart $cart, Product $product, int $quantity, string $orderType): void
    {
        $unitPrice = round((float) $product->price, 2);
        $grossAmount = round($unitPrice * $quantity, 2);
        $vatRate = (float) ($product->vat_rate ?: 12);

        $vatableSales = 0;
        $vatAmount = 0;
        $nonVatSales = 0;

        if (($product->vat_type ?? null) === 'vat') {
            if ($product->vat_inclusive) {
                $divisor = 1 + ($vatRate / 100);
                $vatableSales = round($grossAmount / $divisor, 2);
                $vatAmount = round($grossAmount - $vatableSales, 2);
            } else {
                $vatableSales = $grossAmount;
                $vatAmount = round($grossAmount * ($vatRate / 100), 2);
            }
        } else {
            $nonVatSales = $grossAmount;
        }

        CartItem::create([
            'cart_id'          => $cart->id,
            'product_id'       => $product->id,
            'product_type'     => $product->product_type,
            'description'      => $product->receipt_alias ?: $product->name,
            'quantity'         => $quantity,
            'price'            => $unitPrice,
            'amount'           => $grossAmount,
            'cost'             => round(((float) $product->cost) * $quantity, 2),
            'profit'           => round($grossAmount - (((float) $product->cost) * $quantity), 2),
            'order_type'       => $orderType,
            'sub_total'        => $grossAmount,
            'vatable_sales'    => $vatableSales,
            'vat_amount'       => $vatAmount,
            'vat_exempt_sales' => 0,
            'non_vat_sales'    => $nonVatSales,
            'less_tax'         => 0,
            'placed_order'     => false,
            'is_served'        => false,
        ]);
    }

    private function notifyStaffOfOnlineOrder(Cart $cart): void
    {
        $cart->loadMissing('branch');

        $users = User::query()
            ->where('branch_id', $cart->branch_id)
            ->where(function ($query) {
                $query->whereNull('user_type')
                    ->orWhere('user_type', '!=', 'customer');
            })
            ->get();

        $viewUrl = '/admin/carts/' . $cart->id . '/view';
        $customerName = data_get($cart->meta_data, 'guest_checkout.name', 'Customer');
        $referenceNo = $this->getCartReferenceNo($cart);

        foreach ($users as $user) {
            Notification::make()
                ->title('New online customer order')
                ->body("{$customerName} submitted order {$referenceNo}.")
                ->icon('heroicon-o-shopping-bag')
                ->iconColor('warning')
                ->actions([
                    Action::make('view')
                        ->label('View Cart')
                        ->url($viewUrl),
                ])
                ->sendToDatabase($user);
        }
    }
}
