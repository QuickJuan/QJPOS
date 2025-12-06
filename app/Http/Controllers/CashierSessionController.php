<?php
namespace App\Http\Controllers;

use App\Http\Requests\CashierSessionRequest;
use App\Http\Resources\ProductResource;
use App\Models\Modifier;
use App\Models\Product;
use App\Models\TableRoom;
use App\Models\TableRoomLocation;
use App\Services\CashierSessionService;
use App\Services\GeneralSettingsService;
use App\Services\ProductCategoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService,
        protected ProductCategoryService $productCategoryService
    ) {}

    public function index(Request $request): Response
    {
        // Get categories with active products
        $pendingCashiering = $this->cashierSessionService->model->openSession()->with('cashier')->first();

        $availableModifiers = Modifier::withMappedData();
        $cartData           = $this->cashierSessionService->getCartData($request, $pendingCashiering);
        $cart               = $cartData['cart'];
        $cartItems          = $cartData['cartItems'];
        $totals             = $this->cashierSessionService->calculateTotals($cart, $cartItems);
        $categories         = $this->productCategoryService->getCategoriesWithProductsAsResources();

        if ($request->has('tableId')) {
            $tableId      = $request->input('tableId');
            $currentTable = TableRoom::find($tableId);
        } else {
            $currentTable = null;
        }

        // Cart is now provided by HandleInertiaRequests middleware via shared props
        return Inertia::render('Resto/Index', [
            'total'              => $totals['total'],
            'subTotal'           => $totals['subAmount'],
            'lessTaxTotal'       => $totals['lessTaxTotal'],
            'lessDiscountTotal'  => $totals['lessDiscountTotal'],
            'categories'         => $categories,
            'currentTable'       => $currentTable,
            'availableModifiers' => $availableModifiers,
        ]);
    }

    public function preview(Request $request): Response
    {
        $activeBranch    = $request->user()->cashierSession->branch;
        $generalSettings = app(GeneralSettingsService::class)->getCompanySettings();

        // Check if the current auth user has an open cashier session (closing_time is null)
        $openSession = $this->cashierSessionService->model
            ->openSession()
            ->with('cashier')
            ->first();

        if ($openSession) {
            $sessionSummary = $this->cashierSessionService->getSessionSummary($openSession);
        }

        // dd($sessionSummary);
        return Inertia::render('Resto/Preview', [
            'activeBranch'    => $activeBranch,
            'openSession'     => $openSession,
            'sessionSummary'  => $sessionSummary ?? null,
            'generalSettings' => $generalSettings,
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
