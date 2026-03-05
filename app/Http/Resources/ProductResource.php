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
            'id'                  => $this->id,
            'uuid'                => $this->uuid,
            'name'                => $this->name,
            'average_cost'        => $this->average_cost,
            'receipt_alias'       => $this->receipt_alias,
            'description'         => $this->description,
            'product_type'        => $this->product_type,
            'open_price'          => (bool) $this->open_price,
            'category_id'         => $this->category_id,
            'brand_id'            => $this->brand_id,
            'total_onhand'        => $this->total_onhand ?? '0',
            'price'               => $this->price ?? '0',
            'unit_measure'        => $this->unit_measure,
            'multiple_packaging'  => (bool) $this->multiple_packaging,
            'category'            => $this->whenLoaded('category'),
            'brand'               => $this->whenLoaded('brand'),
            'options'             => OptionResource::collection($this->whenLoaded('options')),
            'product_packagings'  => $this->whenLoaded('productPackagings', function () {
                return $this->productPackagings->map(function ($packaging) {
                    return [
                        'id'                 => $packaging->id,
                        'name'               => $packaging->name,
                        'unit_measure'       => $packaging->unit_measure,
                        'price'              => $packaging->price,
                        'qty'                => $packaging->qty,
                        'cost'               => $packaging->cost,
                        'featured_image_url' => $packaging->getFirstMediaUrl('featured_image'),
                    ];
                });
            }),
            'featured_image_url'  => $this->getFirstMediaUrl('featured_image'),
            'product_images_urls' => $this->getMedia('product_images')->map(fn($media) => $media->getUrl())->toArray(),
        ];
    }
}
