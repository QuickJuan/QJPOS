<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
            'discount_name' => $this->discount_name,
            'amount' => $this->amount,
            'type' => $this->type,
            'discount_type' => $this->discount_type,
            'remove_tax' => $this->remove_tax,
            'requires_customer_info' => $this->require_customer_info,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
