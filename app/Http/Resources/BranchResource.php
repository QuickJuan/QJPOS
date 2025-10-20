<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'branch_code'         => $this->branch_code,
            'name'                => $this->name,
            'address'             => $this->address,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'contact_person'      => $this->contact_person,
            'long_lat'            => $this->long_lat,
            'is_active'           => $this->is_active,
            'tin'                 => $this->tin,
            'registration_number' => $this->registration_number,
            'users'               => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
