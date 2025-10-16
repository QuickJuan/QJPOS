<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'featured_image_url' => $this->getFirstMediaUrl('featured_image'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'products_count' => $this->when(
                $this->relationLoaded('products'),
                $this->products->count()
            ),
        ];
    }
}
