<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'branch_id'              => $this->branch_id,
            'table_room_location_id' => $this->table_room_location_id,
            'name'                   => $this->name,
            'chairs'                 => $this->chairs,
            'with_timeframe'         => $this->with_timeframe,
            'merge_to'               => $this->merge_to,
            'status'                 => $this->status,
            'time_in'                => $this->time_in,
            'time_out'               => $this->time_out,
            'limit_hours'            => $this->limit_hours,
            'table_width'            => $this->table_width,
            'table_height'           => $this->table_height,
            'table_x'                => $this->table_x,
            'table_y'                => $this->table_y,
            'pax_limit'              => $this->pax_limit,
            'screen_position'        => $this->screen_position,
            'dining_start'           => $this->dining_start,
            'dining_end'             => $this->dining_end,
            'notes'                  => $this->notes,
            'featuredImage'          => $this->getFeaturedImageUrl() ?? null,
            'tableReservations'      => TableReservationResource::collection($this->whenLoaded('tableReservations')),
            'branch'                 => new BranchResource($this->whenLoaded('branch')),
            'mergeTo'                => new TableRoomResource($this->whenLoaded('mergeTo')),
            'tableRoomLocation'      => new TableRoomLocationResource($this->whenLoaded('tableRoomLocation')),
        ];
    }
}
