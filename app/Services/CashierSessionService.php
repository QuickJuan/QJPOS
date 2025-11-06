<?php
namespace App\Services;

use App\Enums\TableRoomStatusType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CashierSession;
use App\Models\TableRoom;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionService
{
    public function __construct(public CashierSession $model)
    {
        $this->model = $model;
    }

    public function startSession(Request $request): CashierSession
    {
        // Check if user already has an open session
        $existingSession = $this->model
            ->openSession()
            ->first();

        if ($existingSession) {
            throw new Exception('You already have an open session. Please continue or close it first before starting new one.');
        }

        $session = $this->model->create([
            'business_date'  => now()->toDateString(),
            'cashier_id'     => Auth::id(),
            'started_time'   => now(),
            'beginning_cash' => $request['beginning_cash'],
            'total_sales'    => 0,
            'closing_cash'   => 0,
        ]);

        return $session;
    }

    public function closeSession(Request $request)
    {
        $session = $this->model
            ->openSession()
            ->first();

        if (! $session) {
            throw new Exception('No open session found');
        }

        $closeSession = $session->update([
            'closing_time'      => now(),
            'closing_cash'      => $request->closing_cash,
            'cash_denomination' => $request->cash_denomination,
        ]);

        return $closeSession;
    }

    public function createOrder(Request $request)
    {
        $table = TableRoom::findOrFail($request->table_id);
        $table->update([
            'status'        => TableRoomStatusType::OCCUPIED->value,
            'time_in'       => Carbon::now(),
            'customer_name' => $request->guest_name,
            'number_of_pax' => $request->pax,
        ]);

        $cashierSession = $this->model->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::firstOrCreate([
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
            'table_room_id'      => $table->id,
        ]);

        if (! $cart) {
            throw new Exception('There was an error in creating cart.');
        }

        return $cart;
    }

    public function addToCart(Request $request): bool | CartItem
    {
        // Get or create cart for current cashier session
        $cashierSession = $this->model->openSession()->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::firstOrCreate([
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
        ]);

        $addToCart = $cart->cartItems()->create([
            'product_id'           => $request['product_id'],
            'product_packaging_id' => $request['product_packaging_id'],
            'quantity'             => $request['quantity'] ?? 1,
            'price'                => $request['total_price'] / ($request['quantity'] ?? 1),
            'amount'               => $request['total_price'],
            'sub_total'            => $request['total_price'],
            'selected_options'     => $request['selected_options'] ?? [],
            'order_type'           => $request['order_type'],
        ]);

        return $addToCart;
    }
}
