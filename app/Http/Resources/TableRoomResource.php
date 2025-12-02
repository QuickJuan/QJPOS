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
            'id'                     => $this->id,
            'branch_id'              => $this->branch_id,
            'table_room_location_id' => $this->table_room_location_id,
            'name'                   => $this->name,
            'chairs'                 => $this->chairs,
            'status'                 => $this->status,
            'customerName'           => $this->cart?->customer_name ?? $this->customer_name,
            'branchId'               => $this->branch_id,
            'with_timeframe'         => (bool) $this->with_timeframe,
            'merge_to'               => $this->merge_to,
            'time_in'                => $this->time_in,
            'time_out'               => $this->time_out,
            'limit_hours'            => $this->limit_hours,
            'table_width'            => $this->table_width ?? 0,
            'table_height'           => $this->table_height ?? 0,
            'table_x'                => $this->table_x ?? 0,
            'table_y'                => $this->table_y ?? 0,
            'numberOfPax'            => $this->number_of_pax ?? 1,
            'withTimeFrame'          => (bool) $this->with_timeframe,
            'tableWidth'             => $this->table_width ?? 0,
            'pax_limit'              => $this->pax_limit,
            'screen_position'        => $this->screen_position,
            'dining_start'           => $this->dining_start,
            'dining_end'             => $this->dining_end,
            'notes'                  => $this->notes,
            'featuredImageUrl'       => $this->getFeaturedImageUrl() ?? '',
            'cart'                   => $this->whenLoaded('cart'),
            'mergedTables'           => TableRoomResource::collection($this->whenLoaded('mergedTables')),
            'tableReservations'      => TableReservationResource::collection($this->whenLoaded('tableReservations')),
            'branch'                 => new BranchResource($this->whenLoaded('branch')),
            'mergeTo'                => new TableRoomResource($this->whenLoaded('mergeTo')),
            'tableRoomLocation'      => new TableRoomLocationResource($this->whenLoaded('tableRoomLocation')),
        ];
    }
}
