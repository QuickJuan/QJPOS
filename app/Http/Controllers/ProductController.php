<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($slug)
    {
        $productsJson = file_get_contents(resource_path('data/products.json'));
        $products = json_decode($productsJson, true);

        $product = $products[$slug] ?? null;

        if (!$product) {
            abort(404);
        }

        return Inertia::render('ProductDetail', ['product' => $product]);
    }
}
