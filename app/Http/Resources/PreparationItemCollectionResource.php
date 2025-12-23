<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PreparationItemCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group items by order type
        $grouped = $this->collection->groupBy('order_type');

        return $grouped->map(function ($items, $orderType) {
            $parentItems = $items->filter(function ($item) {
                return empty($item->parent_id);
            })->values();

            return [
                'orderType' => $orderType,
                'items' => PreparationItemResource::collection($parentItems),
                'totalItems' => $items->count(),
            ];
        })->values()->all();
    }
}
