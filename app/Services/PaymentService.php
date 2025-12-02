<?php
namespace App\Services;

use App\Enums\TableRoomLocation\LocationType;
use App\Enums\TableRoomStatusType;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function settleBill(Request $request, int $cartId): mixed
    {
        Log::info('Settle bill started', ['cartId' => $cartId, 'request' => $request->all()]);

        try {
            return DB::transaction(function () use ($request, $cartId) {
                $cart = Cart::with(['cartItems', 'tableRoom.tableRoomLocation'])->findOrFail($cartId);

                Log::info('Cart found', ['cart' => $cart->id, 'items' => $cart->cartItems->count()]);

            // Get cart items for processing
            $cartItems = CartItem::where('cart_id', $cart->id)->get();

            // Create the order
            $order = Order::create([
                'invoice_no'         => $this->getNextInvoiceNumber($request->branch_id),
                'cashier_id'         => $cart->cashier_id,
                'cashier_session_id' => $cart->cashier_session_id,
                'table_room_id'      => $cart->table_room_id,
                'customer_id'        => $request->customer_id,
                'discount_id'        => $request->discount_id,
                'coupon_id'          => $request->coupon_id,
                'coupon_code'        => $request->coupon_code,
                'total_amount'       => $request->total_amount,
                'total_discount'     => $request->total_discount ?? 0,
                'item_discount'      => $request->item_discount ?? 0,
                'total_due'          => $request->total_amount,
                'amount_tendered'    => $request->amount_paid,
                'notes'              => $cart->notes,
                'meta_data'          => [
                    'change'     => $request->amount_paid - $request->total_amount,
                    'settled_at' => now(),
                ],
            ]);

            // Use the already fetched cart items for processing

            $orderItemsData = $cartItems->map(function ($cartItem) use ($order) {
                return [
                    'order_id'             => $order->id,
                    'product_id'           => $cartItem->product_id,
                    'product_packaging_id' => $cartItem->product_packaging_id,
                    'quantity'             => $cartItem->quantity,
                    'price'                => $cartItem->price,
                    'discount_amount'      => $cartItem->discount_amount ?? 0,
                    'vatable_sales'        => $cartItem->vatable_sales ?? 0,
                    'vat_exempt_sales'     => $cartItem->vat_exempt_sales ?? 0,
                    'vat_amount'           => $cartItem->vat_amount ?? 0,
                    'non_vat_sales'        => $cartItem->non_vat_sales ?? 0,
                    'less_tax'             => $cartItem->less_tax ?? 0,
                    'amount'               => $cartItem->amount,
                    'order_type'           => $cartItem->order_type,
                    'discount_id'          => $cartItem->discount_id,
                    'coupon_code'          => $cartItem->coupon_code,
                    'sub_total'            => $cartItem->sub_total,
                    'is_served'            => $cartItem->is_served,
                    'placed_order'         => $cartItem->placed_order,
                    'is_void'              => $cartItem->is_void ?? false,
                    'reason'               => $cartItem->reason,
                    'notes'                => $cartItem->notes,
                    'meta_data'            => json_encode($cartItem->meta_data ?? []),
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            })->toArray();

            // Bulk insert - single INSERT query instead of N queries
            OrderItem::insert($orderItemsData);

            // Calculate VAT totals using database aggregation and update the order
            $vatTotals = OrderItem::where('order_id', $order->id)
                ->selectRaw('
                    SUM(vatable_sales) as vatable_sales,
                    SUM(vat_amount) as vat_amount,
                    SUM(vat_exempt_sales) as vat_exempt_sales,
                    SUM(zero_rated_sales) as zero_rated_sales,
                    SUM(non_vat_sales) as non_vat
                ')
                ->first();

            $order->update([
                'vatable_sales'    => $vatTotals->vatable_sales ?? 0,
                'vat_amount'       => $vatTotals->vat_amount ?? 0,
                'vat_exempt_sales' => $vatTotals->vat_exempt_sales ?? 0,
                'zero_rated_sales' => $vatTotals->zero_rated_sales ?? 0,
                'non_vat'          => $vatTotals->non_vat ?? 0,
            ]);

            // Delete cart and its items
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();

            // Update table status if applicable
            if ($cart->table_room_id
                && $cart->tableRoom
                && $cart->tableRoom->tableRoomLocation
                && $cart->tableRoom->tableRoomLocation->location_type != LocationType::TAKEOUT->value) {

                TableRoom::where('id', $cart->table_room_id)->update([
                    'status'        => TableRoomStatusType::AVAILABLE->value,
                    'time_in'       => null,
                    'customer_name' => null,
                    'number_of_pax' => null,
                ]);
            }

            return $order->load('orderItems');
        });
        } catch (\Exception $e) {
            Log::error('Settle bill failed', ['error' => $e->getMessage(), 'cartId' => $cartId]);
            throw $e;
        }
    }



    protected function getNextInvoiceNumber(?int $branchId = null): string
    {
        if ($branchId) {
            $branch = Branch::find($branchId);
            if ($branch) {
                // Increment the or_number atomically
                $branch->increment('or_number');
                $invoiceNumber = $branch->or_number;

                return (string) $invoiceNumber;
            }
        }

        // Fallback: use order count if no branch
        $lastOrder = Order::latest('id')->first();
        return (string) ($lastOrder ? ($lastOrder->id + 1) : 1);
    }

}
