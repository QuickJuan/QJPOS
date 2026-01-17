<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAddOn;
use Illuminate\Http\JsonResponse;

class ProductAddOnController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $addOns = ProductAddOn::query()
            ->where('product_id', $product->id)
            ->with([
                'addonProduct:id,name,receipt_alias,product_type',
                'productPackaging:id,name',
            ])
            ->get()
            ->map(fn(ProductAddOn $addOn) => [
                // This is the ProductAddOn mapping id (not the product id)
                'id' => $addOn->id,
                'name' => $addOn->addonProduct?->receipt_alias
                    ?: $addOn->addonProduct?->name
                    ?: '',
                'add_on_price' => $addOn->add_on_price,
                'packaging_label' => $addOn->productPackaging?->name,
                'product_addon_id' => $addOn->product_addon_id,
                'product_packaging_id' => $addOn->product_packaging_id,
            ])
            ->values();

        return response()->json($addOns);
    }
}
