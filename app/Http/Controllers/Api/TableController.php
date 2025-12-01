<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableRoom;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    /**
     * Get tables for a specific branch
     */
    public function getTablesByBranch(int $branchId): JsonResponse
    {
        $branch = Branch::findOrFail($branchId);

        $tables = $branch->tableRooms()
            ->with(['carts' => function($query) {
                $query->whereHas('cartItems'); // Only get carts that have items
            }, 'carts.cartItems.product'])
            ->orderBy('name')
            ->get()
            ->map(function ($table) {
                $activeCart = $table->carts->first(); // Get the first active cart with items
                $hasActiveItems = $activeCart && $activeCart->cartItems->count() > 0;

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->chairs,
                    'status' => $hasActiveItems ? 'occupied' : 'available',
                    'cart_id' => $activeCart?->id,
                    'has_orders' => $hasActiveItems
                ];
            });

        return response()->json($tables);
    }

    /**
     * Get table with associated cart and items
     */
    public function getTableWithCart(int $tableId): JsonResponse
    {
        $table = TableRoom::findOrFail($tableId);

        $table->load([
            'carts.cartItems.product.category'
        ]);

        $activeCart = $table->carts->first(); // Get the first active cart

        return response()->json([
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->chairs,
                'status' => $activeCart ? 'occupied' : 'available',
                'number_of_pax' => $table->number_of_pax,
                'customer_name' => $table->customer_name,
            ],
            'cart' => $activeCart ? [
                'id' => $activeCart->id,
                'subtotal' => $activeCart->subtotal,
                'tax_amount' => $activeCart->tax_amount,
                'discount_amount' => $activeCart->discount_amount,
                'total_amount' => $activeCart->total_amount,
                'items' => $activeCart->cartItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'selected_options' => $item->selected_options ?? []
                    ];
                })
            ] : null
        ]);
    }
}
