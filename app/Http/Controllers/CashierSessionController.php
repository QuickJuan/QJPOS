<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Branch;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Models\TableRoomLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\ProductResource;
use App\Services\CashierSessionService;
use App\Http\Resources\XReadingResource;
use App\Services\ProductCategoryService;
use App\Http\Requests\CashierSessionRequest;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService,
        protected ProductCategoryService $productCategoryService
    ) {}

    public function index(Request $request): Response
    {
        // Load only category metadata initially for better performance
        $categories = $this->productCategoryService->getCategoriesOnly();

        $currentTable = null;
        if ($request->has('tableId')) {
            $tableId      = $request->input('tableId');
            $currentTable = TableRoom::find($tableId);
        }

        // Cart is now provided by HandleInertiaRequests middleware via shared props
        return Inertia::render('Resto/Index', [
            'categories'             => $categories,
            'currentTable'           => $currentTable,
            'selectedCategorySlug'   => null,
            'products'               => [],
            'categoryName'           => null,
            'tableId'                => $request->input('tableId'),
            'orderType'              => $request->input('orderType', 'dine-in'),
        ]);
    }

    /**
     * Get products for a specific category (API endpoint)
     */
    public function getCategoryProducts(Request $request, $categorySlug): JsonResponse
    {
        try {
            $products = $this->productCategoryService->getProductsForCategory($categorySlug);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show products for a specific category via Inertia
     */
    public function showCategory(Request $request, $categorySlug): Response
    {
        $categories = $this->productCategoryService->getCategoriesOnly();
        $products = $this->productCategoryService->getProductsForCategory($categorySlug);
        $category = $categories->firstWhere('slug', $categorySlug);

        $currentTable = null;
        if ($request->has('tableId')) {
            $tableId = $request->input('tableId');
            $currentTable = TableRoom::find($tableId);
        }

        return Inertia::render('Resto/Index', [
            'categories'           => $categories,
            'currentTable'         => $currentTable,
            'selectedCategorySlug' => $categorySlug,
            'products'             => ProductResource::collection($products),
            'categoryName'         => $category?->name ?? 'Unknown Category',
            'tableId'              => $request->input('tableId'),
            'orderType'            => $request->input('orderType', 'dine-in'),
        ]);
    }
    public function reviewXTransactions(Request $request): Response
    {
        $activeBranch = Branch::find($request->user()->branch->id);

        $query = $this->cashierSessionService->model
            ->with('cashier')
            // ->where('branch_id', $request->user()->branch->id)
            ->whereNotNull('closing_time');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('cashier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('closing_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('closing_time', '<=', $request->date_to);
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        // Status is always closed, so no need to filter

        // Get per_page from request, default to 10
        $perPage = $request->input('per_page', 10);

        // Order by closing_time descending (latest first)
        $sessions = $query->orderBy('closing_time', 'desc')->paginate($perPage);

        $cashiers = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Resto/ReviewXReadings', [
            'sessions' => XReadingResource::collection($sessions),
            'cashiers' => $cashiers,
            'filters'  => $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id', 'per_page']),
        ]);
    }

    public function preview(): Response
    {
        // Check if the current auth user has an open cashier session (closing_time is null)
        $openSession = $this->cashierSessionService->model
            ->openSession()
            ->with('cashier')
            ->first();

        return Inertia::render('Resto/Preview', [
            'openSession' => $openSession,
            'timestamp' => now()->timestamp, // Force fresh render
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
        $product = Product::with(['options.optionItems.productPackaging', 'options.optionItems.product.media', 'options.optionItems.product.productPackagings'])->findOrFail($productId);

        return Inertia::render('Resto/ProductOption', [
            'product' => ProductResource::make($product),
        ]);
    }

    public function closeShift(CashierSessionRequest $request)
    {
        try {
            // Close the shift and get the session
            $session = $this->cashierSessionService->closeShift($request);
            // Return back with session to show modal first
            // The frontend will handle logout after user closes the modal

            return response()->json([
                'message' => 'Cashier Shift closed successfully.',
                'session' => new XReadingResource($session),
                'success' => true,
            ]);

        } catch (Exception $e) {
               return response()->json([
                'message' => 'There was an error while closing the shift: ' . $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function getSessionSummary(): JsonResponse
    {
        $session = $this->cashierSessionService->model->openSession()->with('cashier')->first();

        if (! $session) {
            // If no open session, get the latest session (just closed)
            $session = $this->cashierSessionService->model->where('cashier_id', Auth::id())->latest()->first();
        }

        if ($session) {
            $sessionSummary = $this->cashierSessionService->getSessionSummary($session);
            return response()->json($sessionSummary);
        }

        return response()->json(null);
    }

    public function getSessionSummaryById(int $sessionId): JsonResponse
    {
        $session = $this->cashierSessionService->model->with('cashier')->find($sessionId);

        if ($session) {
            $sessionSummary = $this->cashierSessionService->getSessionSummary($session);
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
