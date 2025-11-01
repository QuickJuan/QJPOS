<?php
namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Requests\CashierSessionRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DiscountResource;
use App\Http\Resources\ProductResource;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\TableRoom;
use App\Models\TableRoomLocation;
use App\Services\CashierSessionService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService
    ) {
        $this->cashierSessionService = $cashierSessionService;
    }

    public function index(Request $request): Response
    {
        // Check if the current auth user has pending cashiering.
        $pendingCashiering = $this->cashierSessionService->model
            ->openSession()
            ->first();

        // Get categories with products directly (will be cached in browser)
        $categoriesQuery = Category::with(['products' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        $categories = CategoryResource::collection($categoriesQuery);

        // Get available discounts
        $discounts = DiscountResource::collection(
            Discount::all()
        );

        // Get cart items for current session
        $cartItems = [];
        if ($pendingCashiering) {
            $cartQuery = Cart::query();

            if ($request->has('tableId')) {
                $cartQuery->where('table_room_id', $request->input('tableId'));
            }

            $cart = $cartQuery->where('cashier_id', Auth::id())
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
                        'is_served'        => (bool) $item->is_served,
                        'selected_options' => $item->selected_options ?? [],
                        'checked'          => false,
                    ])->toArray();
            }
        }

        return Inertia::render('RetailCashier/Index', [
            'categories'         => $categories,
            'pendingCashiering'  => $pendingCashiering,
            'currentUser'        => Auth::user(),
            'cart'               => $cart,
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

    public function tables(): Response
    {
        $tables = TableRoom::with(['tableRoomLocation'])
            ->activeBranch()
            ->get()
            ->map(fn($table) => [
                'id'                     => $table->id,
                'name'                   => $table->name,
                'chairs'                 => $table->chairs,
                'status'                 => $table->status,
                'merge_to'               => $table->merge_to,
                'sort_number'            => $table->sort_number,
                'table_room_location_id' => $table->table_room_location_id,
                'featured_image_url'     => $table->getFeaturedImageUrl() ?: null,
                'current_order'          => null,
            ]);

        // Get locations
        $locations = TableRoomLocation::all()
            ->map(fn($location) => [
                'id'   => $location->id,
                'name' => $location->name,
            ]);

        return Inertia::render('RetailCashier/Tables', [
            'tables'      => $tables,
            'locations'   => $locations,
            'currentUser' => Auth::user(),
        ]);
    }

    public function createOrder(Request $request): RedirectResponse
    {
        try {
            $this->cashierSessionService->createOrder($request);

            return redirect()->route('retail-cashier.index', ['tableId' => (int) $request->table_id])->with('success', 'Order started successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to start order: ' . $e->getMessage());
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
}
