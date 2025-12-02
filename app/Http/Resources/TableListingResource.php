<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'customerName' => $this->cart?->customer_name ?? $this->customer_name,
            'status' => $this->status,
            'branch' => $this->branch,
            'tableRoomLocation' => $this->tableRoomLocation,
            'chairs' => $this->chairs,
            'mergedTables' => Self::collection($this->mergedTables),
            'cart' => $this->whenLoaded('cart'),
            'table_x' => $this->table_x ?? 0,
            'table_y' => $this->table_y ?? 0,
            'withTimeFrame' => (bool) $this->with_timeframe,
            'tableWidth' => $this->table_width ?? 150,
            'numberOfPax' => $this->number_of_pax ?? 1,
            'featuredImageUrl' => $this->getFeaturedImageUrl(),
        ];
    }
}
