<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use App\Models\Product;
use Illuminate\Http\Request;

class CouponCodeController extends Controller
{
    public function validateGuest(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'session_id' => ['nullable', 'string', 'max:255'],
        ]);

        $coupon = CouponCode::query()
            ->active()
            ->valid()
            ->whereRaw('LOWER(code) = ?', [strtolower($validated['code'])])
            ->first();

        if (! $coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon code not found or is no longer active.',
            ], 404);
        }

        $products = Product::query()
            ->with('category')
            ->whereIn('id', collect($validated['items'])->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        $subtotalInCents = 0;
        $productIds = [];
        $categoryIds = [];

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                continue;
            }

            $productIds[] = $product->id;
            if ($product->category_id) {
                $categoryIds[] = $product->category_id;
            }

            $subtotalInCents += (int) round(((float) $product->price * (float) $item['quantity']) * 100);
        }

        if ($coupon->minimum_amount && $subtotalInCents < $coupon->minimum_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon requires a higher order total.',
            ], 422);
        }

        if (! $coupon->isApplicableToProducts(array_values(array_unique($productIds)))) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon does not apply to the selected products.',
            ], 422);
        }

        if (! $coupon->isApplicableToCategories(array_values(array_unique($categoryIds)))) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon does not apply to the selected categories.',
            ], 422);
        }

        $sessionId = $validated['session_id'] ?? $request->session()->getId();
        if ($coupon->hasUserReachedLimit(null, $sessionId)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon has already been used the maximum number of times for this browser session.',
            ], 422);
        }

        $discountInCents = $coupon->calculateDiscount($subtotalInCents);

        return response()->json([
            'valid' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount_amount' => round($discountInCents / 100, 2),
                'minimum_amount' => $coupon->minimum_amount ? round($coupon->minimum_amount / 100, 2) : null,
            ],
        ]);
    }
}
