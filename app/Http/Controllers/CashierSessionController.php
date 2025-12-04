<?php
namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use App\Models\Category;
use App\Models\Modifier;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Models\TableRoomLocation;
use App\Services\DiscountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\ProductResource;
use App\Services\CashierSessionService;
use App\Http\Resources\CategoryResource;
use App\Services\GeneralSettingsService;
use App\Http\Requests\CashierSessionRequest;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService,
        protected DiscountService $discountService
    ) {
        $this->cashierSessionService = $cashierSessionService;
        $this->discountService       = $discountService;
    }

    public function index(Request $request, ?string $categorySlug = null): Response
    {
        // Check if the current auth user has pending cashiering.
        $pendingCashiering = $this->cashierSessionService->model->openSession()->with('cashier')->first();

        // Get categories with products directly (will be cached in browser)
        $categoriesQuery = Category::with(['products' => fn($query) => $query->where('is_active', true)->with('productPackagings', 'options')])->get();
        $categories      = CategoryResource::collection($categoriesQuery);
        $taxRate         = config('sales.tax_rate');

        // Get available discounts
        $discounts = $this->discountService->getDiscounts();

        // Get available modifiers
        $modifiers = Modifier::withMappedData();

        // Get cart and cart items for current session
        $cartData     = $this->cashierSessionService->getCartData($request, $pendingCashiering);
        $cart         = $cartData['cart'];
        $cartItems    = $cartData['cartItems'];
        $currentTable = $cartData['currentTable'];

        // Calculate totals
        $totals        = $this->cashierSessionService->calculateTotals($cart, $cartItems);

        $billNumber    = $this->cashierSessionService->getBillNo(session('active_branch')->id);
        $receiptNumber = $this->cashierSessionService->getReceiptNo(session('active_branch')->id);

        // Get the General Settings
        $generalSettings = app(GeneralSettingsService::class)->getCompanySettings();

        // Prepare view data
        $viewData = $this->cashierSessionService->prepareViewData(
            $categories,
            $pendingCashiering,
            $cart,
            $cartItems,
            $discounts,
            $modifiers,
            $currentTable,
            $taxRate,
            $totals,
            $billNumber,
            $receiptNumber,
            $generalSettings
        );

        // Add selected category slug to view data
        $viewData['selectedCategorySlug'] = $categorySlug;

        return Inertia::render('Resto/Index', $viewData);
    }

    public function preview(Request $request): Response
    {
        $activeBranch = session('active_branch');

        // Check if the current auth user has an open cashier session (closing_time is null)
        $openSession = $this->cashierSessionService->model
            ->openSession()
            ->with('cashier')
            ->first();

        if ($openSession) {
            $sessionSummary = $this->cashierSessionService->getSessionSummary($openSession);
        }

        return Inertia::render('Resto/Preview', [
            'activeBranch'   => $activeBranch,
            'openSession'    => $openSession,
            'sessionSummary' => $sessionSummary ?? null,
        ]);
    }

    public function startSession(CashierSessionRequest $request): RedirectResponse
    {
        try {
            $this->cashierSessionService->startSession($request);

            return redirect()->route('resto.index')->with('success', 'New session successfully created');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error while starting new session.');
        }
    }

    public function productOptions(int $productId): Response
    {
        $product = Product::with(['options.products', 'options.optionItems.productPackaging', 'options.optionItems.product.media', 'options.optionItems.product.productPackagings'])->findOrFail($productId);

        return Inertia::render('Resto/ProductOption', [
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

    public function getSessionSummary(Request $request)
    {
        $openSession = $this->cashierSessionService->model->openSession()->with('cashier')->first();

        if ($openSession) {
            $sessionSummary = $this->cashierSessionService->getSessionSummary($openSession);
            return response()->json($sessionSummary);
        }

        return response()->json(null);
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
                    'tableRoomLocation'      => $table->tableRoomLocation,
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
                'id'            => $location->id,
                'name'          => $location->name,
                'location_type' => $location->location_type,
            ]);

        return Inertia::render('Resto/Tables', [
            'tables'      => $tables,
            'locations'   => $locations,
            'currentUser' => Auth::user(),
        ]);
    }

    public function createOrder(Request $request): RedirectResponse
    {
        try {
            [$table, $cart] = $this->cashierSessionService->createOrder($request);

            return redirect()->route('resto.index', ['tableId' => $request->table_id, 'locationType' => $table->tableRoomLocation->location_type])->with('success', 'Order started successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to start order: ' . $e->getMessage());
        }
    }

    public function updateBillNo(int $branchId): RedirectResponse
    {
        try {
            $this->cashierSessionService->updateBillNo($branchId);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'There was an error while updating the bill number.');
        }
    }
}
