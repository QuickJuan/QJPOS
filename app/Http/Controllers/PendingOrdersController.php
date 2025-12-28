<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendingOrdersController extends Controller
{
    public function index(Request $request)
    {
        // Get branch ID from authenticated user
        $branchId = $request->user()->branch_id;

        // Get all pending orders filtered by the user's branch ID
        $pendingOrders = CartItem::with([
                'product',
                'product.category',
                'cart.tableRoom',
                'children.product',
                'childrenRecursive.product',
            ])
            ->whereHas('cart', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->where('placed_order', true)
            ->where('is_served', false)
            ->where('is_void', false)
            ->whereNull('parent_id')
            ->orderBy('batch_number')
            ->orderBy('placed_order_time')
            ->get()
            ->groupBy('batch_number');

        // Group orders without calculating minutes (will be done on frontend)
        $ordersWithTiming = $pendingOrders->map(function ($items, $batchNumber) {
            $firstItem = $items->first();

            // Convert placed_order_time to ISO8601 string, handling both Carbon and string types
            $placedOrderTime = $firstItem->placed_order_time;
            if ($placedOrderTime) {
                $placedOrderTime = is_string($placedOrderTime)
                    ? $placedOrderTime
                    : $placedOrderTime->toIso8601String();
            }

            return [
                'batch_number' => $batchNumber,
                'table_name' => $firstItem->cart->tableRoom->name ?? 'N/A',
                'placed_order_time' => $placedOrderTime,
                'items' => $items->map(function ($item) {
                    // Get child items (product options) recursively
                    $childItems = $this->getChildItems($item);

                    // Get modifiers from meta_data
                    $modifiers = [];
                    if ($item->meta_data && isset($item->meta_data['modifier'])) {
                        $modifierArray = $item->meta_data['modifier'];
                        if (is_array($modifierArray)) {
                            $modifiers = array_map(function ($modifierName) {
                                return [
                                    'modifier_name' => $modifierName,
                                ];
                            }, $modifierArray);
                        }
                    }

                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? 'Unknown',
                        'quantity' => $item->quantity,
                        'description' => $item->description,
                        'served_time' => $item->served_time,
                        'is_served' => (bool) $item->is_served,
                        'children' => $childItems,
                        'modifiers' => $modifiers,
                    ];
                })->values(),
            ];
        })->values();

        return Inertia::render('Resto/PendingOrders/Index', [
            'pendingOrders' => $ordersWithTiming,
            'branchId' => $branchId,
        ]);
    }

    public function toggleServed(Request $request, $itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);

        if ($request->input('is_served')) {
            $cartItem->update([
                'is_served' => true,
                'served_time' => now(),
                'served_by' => auth()->user()->id,
            ]);
        } else {
            $cartItem->update([
                'is_served' => false,
                'served_time' => null,
                'served_by' => null,
            ]);
        }

        return back()->with('success', 'Order status updated');
    }

    private function getChildItems($item)
    {
        $children = [];

        // First load direct children
        if ($item->children && $item->children->count() > 0) {
            foreach ($item->children as $child) {
                $children[] = [
                    'id' => $child->id,
                    'product_name' => $child->product->name ?? 'Unknown',
                    'quantity' => $child->quantity,
                ];
            }
        }

        return $children;
    }
}
