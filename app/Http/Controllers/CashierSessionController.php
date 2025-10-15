<?php
namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Requests\CashierSessionRequest;
use App\Http\Resources\ProductResource;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Services\CashierSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CashierSessionController extends Controller
{
    public function __construct(protected CashierSessionService $cashierSessionService)
    {
        $this->cashierSessionService = $cashierSessionService;
    }

    public function index()
    {
        // Check if the current auth user has pending cashiering.
        $pendingCashiering = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        $products   = Product::with(['options.optionItems', 'media'])->get();
        $categories = Category::query()->get();

        // Get cart items for current session
        $cartItems = [];
        if ($pendingCashiering) {
            $cart = Cart::where('cashier_id', Auth::id())
                ->where('cashier_session_id', $pendingCashiering->id)
                ->with(['cartItems.product'])
                ->first();

            if ($cart) {
                $cartItems = $cart->cartItems
                    ->map(function ($item) {
                        return [
                            'id'               => $item->id,
                            'product_id'       => $item->product_id,
                            'name'             => $item->product->name ?? 'Unknown Product',
                            'quantity'         => $item->quantity,
                            'price'            => $item->price,
                            'amount'           => $item->amount,
                            'sub_total'        => $item->sub_total,
                            'selected_options' => $item->selected_options ?? [],
                            'checked'          => false,
                        ];
                    })->toArray();

            }
        }

        return Inertia::render('RetailCashier/Index', [
            'products'          => ProductResource::collection($products),
            'categories'        => $categories,
            'pendingCashiering' => $pendingCashiering,
            'currentUser'       => Auth::user(),
            'cartItems'         => $cartItems,
        ]);
    }

    public function preview()
    {
        $activeBranch = session('active_branch');

        // Check if the current auth user has an open cashier session (closing_time is null)
        $openSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        return Inertia::render('RetailCashier/Preview', [
            'activeBranch' => $activeBranch,
            'openSession'  => $openSession,
        ]);
    }

    public function startSession(CashierSessionRequest $request)
    {
        // Check if user already has an open session
        $existingSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if ($existingSession) {
            return redirect()->back()->with('error', 'You already have an open session. Please continue or close it first before starting new one.');
        }

        $session = $this->cashierSessionService->model->create([
            'business_date'  => now()->toDateString(),
            'cashier_id'     => Auth::id(),
            'started_time'   => now(),
            'beginning_cash' => $request['beginning_cash'],
            'total_sales'    => 0,
            'closing_cash'   => 0,
        ]);

        return redirect()->route('retail-cashier.index')->with('success', 'New session successfully created');
    }

    public function productOptions(Request $request, $productId)
    {
        $product = Product::with(['options.optionItems.product.media'])->findOrFail($productId);

        return Inertia::render('RetailCashier/ProductOption', [
            'product' => new ProductResource($product),
        ]);
    }

    public function closeSession(Request $request)
    {
        $request->validate([
            'cash_denomination' => 'required|array',
            'closing_cash'      => 'required|numeric|min:0',
        ]);

        $session = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if (! $session) {
            return redirect()->back()->with('error', 'No open session found');
        }

        $session->update([
            'closing_time'      => now(),
            'closing_cash'      => $request->closing_cash,
            'cash_denomination' => $request->cash_denomination,
        ]);

        return redirect()->back()->with('error', 'Session closed successfully');
    }

    public function addToCart(CartRequest $request): RedirectResponse
    {
        // Get or create cart for current cashier session
        $cashierSession = $this->cashierSessionService->model
            ->where('cashier_id', Auth::id())
            ->whereNull('closing_time')
            ->first();

        if (! $cashierSession) {
            return redirect()->back()->with('error', 'No active cashier session found.');
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
            $existingItem->update([
                'quantity'  => $existingItem->quantity + ($request['quantity'] ?? 1),
                'sub_total' => ($existingItem->quantity + ($request['quantity'] ?? 1)) * $existingItem->price,
            ]);
        } else {
            // If no, create new cart item
            $cart->cartItems()->create([
                'product_id'       => $request['product_id'],
                'quantity'         => $request['quantity'] ?? 1,
                'price'            => $request['total_price'] / ($request['quantity'] ?? 1),
                'amount'           => $request['total_price'],
                'sub_total'        => $request['total_price'],
                'selected_options' => $request['selected_options'] ?? [],
            ]);
        }
        return redirect()->back()->with('success', 'Item added to cart successfully.');
    }
}
