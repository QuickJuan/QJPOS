<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableRoomLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'service_charge' => $this->service_charge,
            'location_type'  => $this->location_type,
            'tableRooms'     => TableRoomResource::collection($this->whenLoaded('tableRooms')),
        ];
    }
}
