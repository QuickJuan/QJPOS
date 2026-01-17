<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TableRoomService;
use App\Http\Controllers\Controller;
use App\Http\Resources\TableListingResource;
use App\Http\Resources\TableLocationResource;

class TableController extends Controller
{


    public function __construct(
        protected readonly TableRoomService $tableRoomService
    ) {
    }
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
                    'status' => $table->status,
                    'cart_id' => $activeCart?->id,
                    'has_orders' => $hasActiveItems
                ];
            });

        return response()->json($tables);
    }

    /**
     * Get tables for a specific branch for waiter (order taking) UI.
     * Returns a stable { data: [...] } shape for frontend consumption.
     */
    public function getWaiterTablesByBranch(int $branchId): JsonResponse
    {
        $branch = Branch::findOrFail($branchId);

        $tables = $branch->tableRooms()
            ->with(['carts' => function ($query) {
                $query->whereHas('cartItems');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($table) {
                $activeCart = $table->carts->first();
                $hasActiveItems = $activeCart && $activeCart->cartItems->count() > 0;

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'table_number' => $table->name,
                    'capacity' => $table->chairs,
                    'status' => $table->status,
                    'cart' => $hasActiveItems ? ['id' => $activeCart?->id] : null,
                    'has_orders' => $hasActiveItems,
                ];
            })
            ->values();

        return response()->json([
            'data' => $tables,
        ]);
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


    public function list(Request $request): JsonResponse
    {
        $branchId = $request->input('branchId');
        $tableRoomLocations = $this->tableRoomService->list($branchId);
        return response()->json(TableLocationResource::collection($tableRoomLocations));
    }
}
