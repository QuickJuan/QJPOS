<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'option_name' => $this->option_name,
            'optionItems' => $this->whenLoaded('optionItems'),
            'optionImage' => $this->getFirstMediaUrl('featured_image'),
        ];
    }
}
