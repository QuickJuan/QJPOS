<?php

namespace App\Http\Resources;

use App\Models\CashierCashout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CashierCashoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type === CashierCashout::TYPE_CASH_IN ? 'Cash In' : 'Cash Out',
            'amount' => (float) $this->amount,
            'purpose' => $this->purpose,
            'details' => $this->details,
            'status' => $this->status,
            'status_label' => Str::headline((string) $this->status),
            'source_name' => $this->source_name,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'created_at_for_humans' => optional($this->created_at)->diffForHumans(),
            'approved_at' => optional($this->approved_at)->toDateTimeString(),
            'approved_at_for_humans' => optional($this->approved_at)->diffForHumans(),
            'approval_notes' => $this->approval_notes,
            'meta' => $this->meta,
            'cashier' => $this->whenLoaded('cashier', fn () => [
                'id' => $this->cashier->id,
                'name' => $this->cashier->name,
            ]),
            'approver' => $this->whenLoaded('approver', fn () => [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ]),
            'session' => $this->whenLoaded('session', fn () => [
                'id' => $this->session->id,
                'business_date' => optional($this->session->business_date)->toDateString(),
            ]),
        ];
    }
}
