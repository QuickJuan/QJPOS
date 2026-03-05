<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $totalAmount = $this->cartItems->sum('amount');
        $lessDiscount = $this->cartItems->sum('discount_amount') ?? 0;
        $lessTax = $this->cartItems->sum('less_tax');
        $subTotal = $totalAmount - ( $lessDiscount + $lessTax);

        $location = $this->tableRoom?->tableRoomLocation;
        $serviceChargeLabel = $location?->service_charge_label ?? 'Service Charge';
        $serviceChargeType = $location?->service_charge_type ?? null;

        return [
            // Cart Information
            'id'           => $this->id,
            'bill_number'  => str_pad($this->bill_no, 4, '0', STR_PAD_LEFT), // Bill number (generated when printing)
            'cart_date'    => $this->created_at,
            'table_number' => $this->tableRoom?->name,

            // Customer Information
            'customer'     => $this->customer ? [
                'id'            => $this->customer->id,
                'customer_name' => $this->customer->customer_name,
                'name'          => $this->customer->customer_name,
                'phone'         => $this->customer->contact_no,
                'contact_no'    => $this->customer->contact_no,
                'email'         => $this->customer->email,
                'address'       => $this->customer->address,
                'e_wallet'      => $this->customer->eWallet ? [
                    'id'               => $this->customer->eWallet->id,
                    'balance'          => (float) $this->customer->eWallet->balance,
                    'points_balance'   => (float) $this->customer->eWallet->points_balance,
                    'earned_points'    => (float) $this->customer->eWallet->earned_points,
                    'redeemed_points'  => (float) $this->customer->eWallet->redeemed_points,
                ] : null,
            ] : null,

            // Cashier Information
            'cashier'      => [
                'id'   => $this->cashier_id,
                'name' => $this->cashier?->name,
            ],

            // Branch Information & Bill Configuration
            'branch'       => [
                'id'                  => $this->branch?->id,
                'name'                => $this->branch?->name,
                'address'             => $this->branch?->address,
                'phone'               => $this->branch?->phone,
                'tin'                 => $this->branch?->tin,
                'registration_number' => $this->branch?->registration_number,
                'bill_headers'        => $this->branch?->receipt_headers ?? [],
                'bill_footer'         => $this->branch?->receipt_footer ?? [],
            ],



            // Financial Information (calculated from cart items)
            'totals'       => [
                'total_amount'    => (float) $totalAmount,
                'less_tax'        => (float) $lessTax,
                'less_discount'   => (float) $lessDiscount ?? 0,
                'sub_total'        => (float) $subTotal,
                'vatable_sales'   => (float) $this->cartItems->sum('vatable_sales'),
                'vat_amount'      => (float) $this->cartItems->sum('vat_amount'),
                'vat_exempt_sales'=> (float) $this->cartItems->sum('vat_exempt_sales'),
                'non_vat_sales'   => (float) $this->cartItems->sum('non_vat_sales'),
                'service_charge'  => (float) $this->service_charge ?? 0,
                'service_charge_label' => $serviceChargeLabel,
                'service_charge_type'  => $serviceChargeType,
                'total_due'       => (float) $this->service_charge + $subTotal,
            ],

            // Payment Information (not applicable for bills, only for settled orders)
            'payment'      => null,

            // Cart Items (using CartItemResource)
            'cart_items'   => new CartItemResource($this->cartItems),

            // Additional metadata
            'meta'         => [
                'printed_at' => now(),
                'bill_type'  => 'customer_bill',
                'notes'      => $this->notes,
                'table_info' => [
                    'id'       => $this->table_room_id,
                    'name'     => $this->tableRoom?->name,
                    'location' => $this->tableRoom?->tableRoomLocation?->name,
                    'service_charge_label' => $serviceChargeLabel,
                    'service_charge_type'  => $serviceChargeType,
                ],
            ],
        ];
    }
}
