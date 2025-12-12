<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptOrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Order Information
            'id'           => $this->id,
            'invoice_no'   => str_pad($this->invoice_no, 6, '0', STR_PAD_LEFT),
            'bill_number'  => $this->bill_number,
            'order_date'   => $this->created_at,
            'status'       => $this->status,
            'table_number' => $this->tableRoom?->name ?? null,

            // Customer Information
            'customer'     => [
                'id'      => $this->customer_id,
                'name'    => $this->customer?->name,
                'phone'   => $this->customer?->phone,
                'email'   => $this->customer?->email,
                'address' => $this->customer?->address,
            ],

            // Cashier Information
            'cashier'      => [
                'id'   => $this->cashier->id,
                'name' => $this->cashier?->name,
            ],

            // Branch Information & Receipt Configuration
            'branch'       => [
                'id'                  => $this->cashierSession->branch?->id,
                'name'                => $this->cashierSession->branch?->name,
                'address'             => $this->cashierSession->branch?->address,
                'phone'               => $this->cashierSession->branch?->phone,
                'tin'                 => $this->cashierSession->branch?->tin,
                'registration_number' => $this->cashierSession->branch?->registration_number,
                'receipt_headers'     => $this->cashierSession->branch?->receipt_headers ?? [],
                'receipt_footer'      => $this->cashierSession->branch?->receipt_footer ?? [],
                'bir_accreditation_footer' => config('sales.bir_accreditation_footer') ? explode('|', config('sales.bir_accreditation_footer')) : [],
            ],

            // Financial Information
            'totals'       => [
                'total_amount'    => $this->total_amount, // total_amount
                'tax_amount'      => $this->vat_amount,
                'discount_amount' => $this->item_discount,
                'total_due'       => $this->total_due, // gross amount
                'less_tax'        => $this->less_tax ?? 0,
                'less_discount'   => $this->less_discount ?? 0,
                'vatable_sales'   => $this->vatable_sales ?? 0,
                'vat_amount'      => $this->vat_amount ?? 0,
                'vat_exempt_sales'=> $this->vat_exempt_sales ?? 0,
                'zero_rated_sales'=> $this->zero_rated_sales ?? 0,
                'non_vat_sales'   => $this->non_vat_sales ?? 0,
                'service_charge'  => $this->service_charge ?? 0,
            ],

            // Payment Information
            'payment'      => [
                'method'      => $this->payment_method,
                'amount_paid' => (float) $this->amount_tendered,
                'change'      => (float) ($this->amount_tendered - $this->total_due),
                'status'      => $this->payment_status,
            ],

            // Order Items (using ReceiptOrderItemsResource)
            'order_items'  => new ReceiptOrderItemsResource($this->orderItems),

            // Additional metadata
            'meta'         => [
                'printed_at'   => now(),
                'receipt_type' => 'customer_receipt',
            ],
        ];
    }
}
