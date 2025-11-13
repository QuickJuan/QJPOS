<?php
namespace App\Http\Controllers;

use App\Http\Requests\CashierSessionRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DiscountResource;
use App\Http\Resources\ProductResource;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Modifier;
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
        $pendingCashiering = $this->cashierSessionService->model->openSession()->first();

        // Get categories with products directly (will be cached in browser)
        $categoriesQuery = Category::with(['products' => fn($query) => $query->where('is_active', true)->with('productPackagings', 'options')])->get();

        $categories = CategoryResource::collection($categoriesQuery);
        $taxRate    = config('sales.tax_rate');

        // Get available discounts
        // Transfer this into Discount Service
        $discounts = DiscountResource::collection(
            Discount::all()
        );

        // Get available modifiers
        // Transfer this into Modifier Service
        $modifiers = Modifier::all()
            ->map(fn($modifier) => [
                'id'   => $modifier->id,
                'name' => $modifier->name,
                'list' => $modifier->list ?? [],
            ]);

        // Get cart items for current session
        $cartItems = [];
        if ($pendingCashiering) {
            $cartQuery = Cart::query();

            if ($request->has('tableId')) {
                $cartQuery->where('table_room_id', $request->input('tableId'));
                $currentTable = TableRoom::find($request->input('tableId'));
            }

            $cart = $cartQuery->where('cashier_id', Auth::id())
                ->where('cashier_session_id', $pendingCashiering->id)
                ->with(['cartItems.product.productPackagings', 'cartItems.children.product'])
                ->first();

            if ($cart) {
                $cartItems = $cart->cartItems
                    ->map(fn($item) => [
                        'id'                => $item->id,
                        'parent_id'         => $item->parent_id,
                        'product_id'        => $item->product_id,
                        'name'              => $item->product->name ?? 'Unknown Product',
                        'quantity'          => $item->quantity,
                        'price'             => $item->price,
                        'amount'            => $item->amount,
                        'sub_total'         => $item->sub_total,
                        'placed_order'      => (bool) $item->placed_order,
                        'order_type'        => $item->order_type,
                        'selected_options'  => $item->selected_options ?? [],
                        'meta_data'         => $item->meta_data ?? [],
                        'discount'          => $item->discount_amount,
                        'less_tax'          => $item->less_tax,
                        'product_packaging' => $item->product_packaging_id ? $item->product->productPackagings->firstWhere('id', $item->product_packaging_id) : null,
                        'checked'           => false,
                        'children'          => $item->children,
                    ])->toArray();
            }
        }

        $subtotal          = collect($cartItems)->sum('sub_total');
        $lessTaxTotal      = collect($cartItems)->sum('less_tax');
        $lessDiscountTotal = collect($cartItems)->sum('discount');
        $total             = $cart && $cart->cartItems->isNotEmpty()
            ? $cart->cartItems->sum(function ($item) {
            $itemTotal = ($item->sub_total ?? 0) - ($item->discount ?? 0);

            return $itemTotal;
        })
            : null;

        return Inertia::render('RetailCashier/Index', [
            'categories'         => $categories,
            'pendingCashiering'  => $pendingCashiering,
            'currentUser'        => Auth::user(),
            'cart'               => $cart,
            'cartItems'          => $cartItems,
            'availableDiscounts' => $discounts,
            'availableModifiers' => $modifiers,
            'currentTable'       => $currentTable ?? [],
            'subTotal'           => $subtotal,
            'total'              => $total,
            'lessTaxTotal'       => $lessTaxTotal,
            'lessDiscountTotal'  => $lessDiscountTotal,
            'taxRate'            => $taxRate,
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
            'product' => ProductResource::make($product),
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
        $tables = TableRoom::with(['tableRoomLocation', 'mergeTo', 'carts.cartItems.product.productPackagings', 'carts.cartItems.children.product'])
            ->activeBranch()
            ->get()
            ->map(function ($table) {
                $cartItems = [];
                if ($table->merge_to) {
                    $targetTable = TableRoom::with(['carts.cartItems.product.productPackagings', 'carts.cartItems.children.product'])->find($table->merge_to);
                    if ($targetTable && $targetTable->carts) {
                        foreach ($targetTable->carts as $cart) {
                            $cartItems = array_merge($cartItems, $cart->cartItems->map(function ($item) {
                                $itemArray                      = $item->toArray();
                                $itemArray['product_packaging'] = $item->product_packaging_id ?
                                $item->product->productPackagings->firstWhere('id', $item->product_packaging_id) : null;
                                return $itemArray;
                            })->toArray());
                        }
                    }
                } else {
                    if ($table->carts) {
                        foreach ($table->carts as $cart) {
                            $cartItems = array_merge($cartItems, $cart->cartItems->map(function ($item) {
                                $itemArray                      = $item->toArray();
                                $itemArray['product_packaging'] = $item->product_packaging_id ?
                                $item->product->productPackagings->firstWhere('id', $item->product_packaging_id) : null;
                                return $itemArray;
                            })->toArray());
                        }
                    }
                }

                return [
                    'id'                     => $table->id,
                    'name'                   => $table->name,
                    'chairs'                 => $table->chairs,
                    'status'                 => $table->status,
                    'merge_to'               => $table->merge_to,
                    'sort_number'            => $table->sort_number,
                    'table_room_location_id' => $table->table_room_location_id,
                    'featured_image_url'     => $table->getFeaturedImageUrl() ?: null,
                    'current_order'          => null,
                    'number_of_pax'          => $table->number_of_pax,
                    'time_in'                => $table->time_in,
                    'customer_name'          => $table->customer_name,
                    'merged_to'              => $table->mergeTo,
                    'cart_items'             => $cartItems,
                ];
            });

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

            return redirect()->route('retail-cashier.index', ['tableId' => $request->table_id])->with('success', 'Order started successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to start order: ' . $e->getMessage());
        }
    }
}
