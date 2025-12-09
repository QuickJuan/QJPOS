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
            'invoice_no'   => $this->invoice_no,
            'bill_number'  => $this->bill_number,
            'order_date'   => $this->created_at,
            'status'       => $this->status,
            'table_number' => $this->table_number,

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
            ],

            // Financial Information
            'totals'       => [
                'subtotal'        => $this->total_amount, // total_amount
                'tax_amount'      => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'total_amount'    => $this->total_due, // gross amount
                'less_tax'        => $this->less_tax ?? 0,
                'less_discount'   => $this->less_discount ?? 0,
                'vatable_sales'   => $this->vatable_sales ?? 0,
                'vat_amount'      => $this->vat_amount ?? 0,
            ],

            // Payment Information
            'payment'      => [
                'method'      => $this->payment_method,
                'amount_paid' => $this->amount_tendered,
                'change'      => $this->amount_tendered - $this->total_due,
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
