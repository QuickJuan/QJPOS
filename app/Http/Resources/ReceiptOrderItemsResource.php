<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptOrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group order items by order_type
        $groupedItems = collect($this->resource)->groupBy('order_type');

        $result = [];

        foreach ($groupedItems as $orderType => $items) {
            // Filter to only include parent items (those without parent_id)
            $parentItems = $items->filter(function ($item) {
                return empty($item->parent_id) || is_null($item->parent_id);
            });

            $result[] = [
                'orderId' => $items->first()->order_id ?? null,
                'orderType' => $this->formatOrderType($orderType),
                'orderItems' => $parentItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'amount' => $item->amount,
                        'lessTax' => $item->less_tax ?? 0,
                        'discount' => $item->discount ?? "N/A",
                        'discount_amount' => $item->discount ?? 0,
                        'sub_total' => $item->sub_total ?? 0,
                        'vatable_sales' => $item->vatable_sales ?? 0,
                        'vat_amount' => $item->vat_amount ?? 0,
                        'orderType' => $orderType,
                        'order_id' => $item->order_id,
                        'sub_items' => $this->getSubItems($item->id),
                        // Add any other order item fields you need
                    ];
                })->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Get sub items (child records) for a given parent item
     */
    private function getSubItems($parentId)
    {
        // Find all items in the resource collection that have this item as parent
        $subItems = collect($this->resource)->filter(function ($item) use ($parentId) {
            return isset($item->parent_id) && $item->parent_id == $parentId;
        });

        return $subItems->map(function ($subItem) {
            return [
                'id' => $subItem->id,
                'name' => $subItem->name,
                'quantity' => $subItem->quantity,
                'price' => $subItem->price,
                'amount' => $subItem->amount,
                'lessTax' => $subItem->less_tax ?? 0,
                'discount' => $subItem->discount ?? "N/A",
                'discount_amount' => $subItem->discount ?? 0,
                'sub_total' => $subItem->sub_total ?? 0,
                'vatable_sales' => $subItem->vatable_sales ?? 0,
                'vat_amount' => $subItem->vat_amount ?? 0,
                'parent_id' => $subItem->parent_id,
                // Recursively get sub-sub items if needed
                'sub_items' => $this->getSubItems($subItem->id),
            ];
        })->toArray();
    }

    /**
     * Format order type for display
     */
    private function formatOrderType(string $orderType): string
    {
        $labels = [
            'dine-in' => 'Dine In',
            'takeout' => 'Takeout',
            'delivery' => 'Delivery',
        ];

        return $labels[$orderType] ?? ucfirst(str_replace('-', ' ', $orderType));
    }
}
