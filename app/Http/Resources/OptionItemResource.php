<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'option_id'            => $this->option_id,
            'product_id'           => $this->product_id,
            'product_packaging_id' => $this->product_packaging_id,
            'price'                => $this->price,
            'quantity'             => $this->quantity,
            'option'               => $this->whenLoaded('option'),
            'product'              => $this->whenLoaded('product'),
        ];
    }
}
