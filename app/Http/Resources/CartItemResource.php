<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group cart items by order_type
        $groupedItems = collect($this->resource)->groupBy('order_type');

        $result = [];

        foreach ($groupedItems as $orderType => $items) {
            // Filter to only include parent items (those without parent_id)
            $parentItems = $items->filter(function ($item) {
                return empty($item->parent_id) || is_null($item->parent_id);
            });

            $result[] = [
                'orderType' => $this->formatOrderType($orderType),
                'cartItems' => $parentItems->map(function ($item) use ($orderType) {
                    return [
                        'id'               => $item->id,
                        'orderType'        => $orderType,
                        'cart_id'          => $item->cart_id,
                        'name'             => $item->description ?? $item->product?->name ?? '',
                        'description'      => $item->product?->description ?? $item->product?->name,
                        'quantity'         => $item->quantity,
                        'price'            => $item->price,
                        'amount'           => $item->amount,
                        'lessTax'          => $item->less_tax ?? 0,
                        'discount'         => $item->discount ?? 0,
                        'discount_amount'  => $item->discount_amount ?? 0,
                        'sub_total'        => $item->sub_total ?? 0,
                        'vatable_sales'    => $item->vatable_sales ?? 0,
                        'vat_amount'       => $item->vat_amount ?? 0,
                        'vat_exempt_sales' => $item->vat_exempt_sales ?? 0,
                        'non_vat_sales'    => $item->non_vat_sales ?? 0,
                        'is_served'        => $item->is_served ?? false,
                        'placed_order'     => $item->placed_order ?? false,
                        'served_by'        => $item->servedBy?->name ?? null,
                        'selectedOptions'  => $item->meta_data['selected_options'] ?? [],
                        'modifiers'        => $item->meta_data['modifier'] ?? [],
                        'sub_items'        => $this->getSubItems($item->id),
                        'notes'            => $item->notes,
                        'product'          => $item->product,
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
                'id'               => $subItem->id,
                'name'             => $subItem->description ?? $subItem->product?->name ?? '',
                'description'      => $subItem->description ?? $subItem->product?->name,
                'quantity'         => $subItem->quantity,
                'price'            => $subItem->price,
                'amount'           => $subItem->amount,
                'lessTax'          => $subItem->less_tax ?? 0,
                'discount'         => $subItem->discount ?? 0,
                'discount_amount'  => $subItem->discount_amount ?? 0,
                'sub_total'        => $subItem->sub_total ?? 0,
                'vatable_sales'    => $subItem->vatable_sales ?? 0,
                'vat_amount'       => $subItem->vat_amount ?? 0,
                'vat_exempt_sales' => $subItem->vat_exempt_sales ?? 0,
                'non_vat_sales'    => $subItem->non_vat_sales ?? 0,
                'parent_id'        => $subItem->parent_id,
                'selectedOptions'  => $subItem->meta_data['selected_options'] ?? [],
                'modifiers'        => $subItem->meta_data['modifiers'] ?? [],
                'notes'            => $subItem->notes,
                // Recursively get sub-sub items if needed
                'sub_items'        => $this->getSubItems($subItem->id),
            ];
        })->toArray();
    }

    /**
     * Format order type for display
     */
    private function formatOrderType(string $orderType): string
    {
        $labels = [
            'dine-in'  => 'Dine In',
            'takeout'  => 'Takeout',
            'delivery' => 'Delivery',
        ];

        return $labels[$orderType] ?? ucfirst(str_replace('-', ' ', $orderType));
    }
}
