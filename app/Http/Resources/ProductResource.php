<?php
namespace App\Http\Resources;

use App\Http\Resources\OptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'name'          => $this->name,
            'average_cost'  => $this->average_cost,
            'receipt_alias' => $this->receipt_alias,
            'description'   => $this->description,
            'category_id'   => $this->category_id,
            'brand_id'      => $this->category_id,
            'category'      => $this->whenLoaded('category'),
            'brand'         => $this->whenLoaded('brand'),
            'options'       => OptionResource::collection($this->options),
            'productImage'  => $this->getFirstMediaUrl('featured_image'),
        ];
    }
}
