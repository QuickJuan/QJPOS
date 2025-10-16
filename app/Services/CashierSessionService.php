<?php
namespace App\Services;

use Exception;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\CashierSession;
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

    public function addToCart(Request $request): bool|CartItem
    {
        // Get or create cart for current cashier session
        $cashierSession = $this->model
            ->openSession()
            ->first();

        if (! $cashierSession) {
            throw new Exception('No active cashier session found.');
        }

        $cart = Cart::firstOrCreate([
            'cashier_id'         => Auth::id(),
            'cashier_session_id' => $cashierSession->id,
        ]);

        // Check if item already exists in cart
        $existingItem = $cart->cartItems()
            ->where('product_id', $request['product_id'])
            ->first();

        if ($existingItem) {
            // If yes, Update quantity if item exists
            $addToCart = $existingItem->update([
                'quantity'  => $existingItem->quantity + ($request['quantity'] ?? 1),
                'sub_total' => ($existingItem->quantity + ($request['quantity'] ?? 1)) * $existingItem->price,
            ]);
        } else {
            // If no, create new cart item
            $addToCart = $cart->cartItems()->create([
                'product_id'       => $request['product_id'],
                'quantity'         => $request['quantity'] ?? 1,
                'price'            => $request['total_price'] / ($request['quantity'] ?? 1),
                'amount'           => $request['total_price'],
                'sub_total'        => $request['total_price'],
                'selected_options' => $request['selected_options'] ?? [],
            ]);
        }

        return $addToCart;
    }
}
