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
            'brand_id'      => $this->brand_id,
            'total_onhand'  => $this->total_onhand ?? '0',
            'category'      => $this->whenLoaded('category'),
            'brand'         => $this->whenLoaded('brand'),
            'options'       => OptionResource::collection($this->whenLoaded('options')),
            'featured_image_url' => $this->getFirstMediaUrl('featured_image'),
            'product_images_urls' => $this->getMedia('product_images')->map(fn($media) => $media->getUrl())->toArray(),
        ];
    }
}
