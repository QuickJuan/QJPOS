<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * used for table listing from the cashiering ui
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'serviceCharge' => $this->service_charge,
            'locationType'    => $this->location_type, // Assuming 'locationType' is a relationship and 'name' is the attribute you want to include'
            'tableRooms'     => TableListingResource::collection($this->whenLoaded('tableRooms')),
            'tableRoomCount' => $this->tableRooms->count()
        ];
    }
}
