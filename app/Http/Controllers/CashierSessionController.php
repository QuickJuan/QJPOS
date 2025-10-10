<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Services\CashierSessionService;

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
            ->first();

        $products   = Product::with(['options.optionItems'])->get();
        $categories = Category::query()->get();

        return Inertia::render('RetailCashier/Index', [
            'products'          => ProductResource::collection($products),
            'categories'        => $categories,
            'pendingCashiering' => $pendingCashiering,
            'currentUser'       => Auth::user(),
        ]);
    }
}
