<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class XReadingResource extends JsonResource
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
            'shift_start' => $this->started_time ? $this->started_time->timezone('Asia/Manila')->format('m/d/Y h:i A') : null,
            'shift_end' => $this->closing_time ? $this->closing_time->timezone('Asia/Manila')->format('m/d/Y h:i A') : null,
            'branch' =>new BranchResource($this->branch),
            'cashier' => $this->cashier->name,
            'beginning_cash' => $this->beginning_cash,
            'total_sales' => $this->total_sales,
            'cash_denomination_total' => $this->cash_denomination,
            'cash_denomination_details' => $this->cash_denomination_details,
            'meta_data' => $this->meta_data,
        ];
    }
}
