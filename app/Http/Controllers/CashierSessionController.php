<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\Cart;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Category;
use App\Models\Discount;
use App\Models\CouponCode;
use App\Http\Requests\CartRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\ProductResource;
use App\Services\CashierSessionService;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DiscountResource;
use App\Http\Requests\CashierSessionRequest;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService
    ) {
        $this->cashierSessionService = $cashierSessionService;
    }

    public function index(): Response
    {
        // Check if the current auth user has pending cashiering.
        $pendingCashiering = $this->cashierSessionService->model
            ->openSession()
            ->first();

        // Get categories with products directly (will be cached in browser)
        $categories = CategoryResource::collection(
            Category::with(['products' => function ($query) {
                $query->where('is_active', true);
            }])->get()
        );

        // Get available discounts
        $discounts = DiscountResource::collection(
            Discount::all()
        );

        // Get cart items for current session
        $cartItems = [];
        if ($pendingCashiering) {
            $cart = Cart::where('cashier_id', Auth::id())
                ->where('cashier_session_id', $pendingCashiering->id)
                ->with(['cartItems.product'])
                ->first();

            if ($cart) {
                $cartItems = $cart->cartItems
                    ->map(fn($item) => [
                        'id'               => $item->id,
                        'product_id'       => $item->product_id,
                        'name'             => $item->product->name ?? 'Unknown Product',
                        'quantity'         => $item->quantity,
                        'price'            => $item->price,
                        'amount'           => $item->amount,
                        'sub_total'        => $item->sub_total,
                        'selected_options' => $item->selected_options ?? [],
                        'checked'          => false,
                    ])->toArray();
            }
        }

        return Inertia::render('RetailCashier/Index', [
            'categories'         => $categories,
            'pendingCashiering'  => $pendingCashiering,
            'currentUser'        => Auth::user(),
            'cartItems'          => $cartItems,
            'availableDiscounts' => $discounts,
        ]);
    }

    public function preview(): Response
    {
        $activeBranch = session('active_branch');

        // Check if the current auth user has an open cashier session (closing_time is null)
        $openSession = $this->cashierSessionService->model
            ->openSession()
            ->first();

        return Inertia::render('RetailCashier/Preview', [
            'activeBranch' => $activeBranch,
            'openSession'  => $openSession,
        ]);
    }

    public function startSession(CashierSessionRequest $request): RedirectResponse
    {
        try {
            $this->cashierSessionService->startSession($request);

            return redirect()->route('retail-cashier.index')->with('success', 'New session successfully created');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error while starting new session.');
        }
    }

    public function productOptions(int $productId): Response
    {
        $product = Product::with(['options.optionItems.product.media'])->findOrFail($productId);

        return Inertia::render('RetailCashier/ProductOption', [
            'product' => new ProductResource($product),
        ]);
    }

    public function closeSession(CashierSessionRequest $request): RedirectResponse
    {
        try {
            $this->cashierSessionService->closeSession($request);

            return redirect()->back()->with('success', 'Session closed successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error while closing the session.');
        }
    }

    public function addToCart(CartRequest $request): RedirectResponse
    {
        try {
            $this->cashierSessionService->addToCart($request);

            return redirect()->back()->with('success', 'Item added to cart successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('success', 'There was an error in adding item to cart.');
        }
    }

    /**
     * Refresh cached data for categories and discounts
     */
    public function refreshCache(): RedirectResponse
    {
        try {
            $this->cashierDataService->refreshCache();

            return redirect()->back()->with('success', 'Cache refreshed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error refreshing the cache.');
        }
    }
}
