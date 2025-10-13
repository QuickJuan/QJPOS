<?php
namespace App\Http\Resources;

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
            'options'       => $this->whenLoaded('options', function () {
                return $this->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'option_items' => $option->optionItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'price' => $item->price,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
