<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'table_room_id'    => $this->table_room_id,
            'user_id'          => $this->user_id,
            'reservation_from' => $this->reservation_from,
            'reservation_to'   => $this->reservation_to,
            'status'           => $this->status,
            'notes'            => $this->notes,
            'pax'              => $this->pax,
            'name'             => $this->name,
            'contact_phone'    => $this->contact_phone,
            'contact_email'    => $this->contact_email,
            'tableRoom'        => new TableRoomResource($this->whenLoaded('tableRoom')),
            'user'             => $this->whenLoaded('user'),
        ];
    }
}
