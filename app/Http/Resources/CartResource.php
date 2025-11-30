<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Cart Information
            'id' => $this->id,
            'bill_number' => $this->bill_no, // Bill number (generated when printing)
            'invoice_no' => null, // Only available after settlement
            'cart_date' => $this->created_at,
            'table_number' => $this->tableRoom?->name ?? 'N/A',

            // Customer Information
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->customer?->name,
                'phone' => $this->customer?->phone,
                'email' => $this->customer?->email,
                'address' => $this->customer?->address,
            ],

            // Cashier Information
            'cashier' => [
                'id' => $this->cashier_id,
                'name' => $this->cashier?->name,
            ],

            // Branch Information & Bill Configuration
            'branch' => [
                'id' => $this->cashierSession->branch?->id,
                'name' => $this->cashierSession->branch?->name,
                'address' => $this->cashierSession->branch?->address,
                'phone' => $this->cashierSession->branch?->phone,
                'tin' => $this->cashierSession->branch?->tin,
                'registration_number' => $this->cashierSession->branch?->registration_number,
                'bill_headers' => $this->cashierSession->branch?->bill_headers ?? [],
                'bill_footer' => $this->cashierSession->branch?->bill_footer ?? [],
            ],

            // Financial Information (calculated from cart items)
            'totals' => [
                'subtotal' => $this->cartItems->sum('sub_total'),
                'tax_amount' => $this->cartItems->sum('vat_amount'),
                'discount_amount' => $this->total_discount ?? 0,
                'total_amount' => $this->total_amount,
                'less_tax' => $this->cartItems->sum('less_tax'),
                'less_discount' => $this->item_discount ?? 0,
                'vatable_sales' => $this->cartItems->sum('vatable_sales'),
                'vat_amount' => $this->cartItems->sum('vat_amount'),
            ],

            // Payment Information (not applicable for bills, only for settled orders)
            'payment' => null,

            // Cart Items (using CartItemResource)
            'cart_items' => new CartItemResource($this->cartItems),

            // Additional metadata
            'meta' => [
                'printed_at' => now(),
                'bill_type' => 'customer_bill',
                'notes' => $this->notes,
                'table_info' => [
                    'id' => $this->table_room_id,
                    'name' => $this->tableRoom?->name,
                    'location' => $this->tableRoom?->tableRoomLocation?->name,
                ],
            ],
        ];
    }
}
