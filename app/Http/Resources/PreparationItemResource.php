<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PreparationLocationResource;


class PreparationItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $packaging = $this->product_packaging_id
            ? $this->productPackaging->unit_measure
            : $this->product->unit_measure;

        return [
            'id' => $this->id,
            'description' => $this->product?->name ?? $this->productPackaging->name ?? $this->description,
            'packaging' => $packaging ?? '',
            'qty' => $this->quantity,
            'modifiers' => $this->meta_data["modifier"] ?? [],
            'notes' => $this->notes,
            'orderType' => $this->order_type,
            'preparationLocation' => $this->product?->preparationLocation?->description ?? '',
            'printable' => (bool) $this->product?->preparationLocation?->printable ?? false,
            'showOnScreen' => (bool) $this->product?->preparationLocation?->show_on_screen ?? false,
        ];
    }
}
