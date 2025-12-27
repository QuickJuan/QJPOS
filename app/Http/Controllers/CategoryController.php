<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Services\ProductCategoryService;

class CategoryController extends Controller
{
    public function __construct(
        protected ProductCategoryService $productCategoryService
    ) {}

    /**
     * Display categories page
     */
    public function index(Request $request): Response
    {
        try {
            // Load only category metadata initially for better performance
            $categories = $this->productCategoryService->getCategoriesOnly();

            $currentTable = null;
            if ($request->has('tableId')) {
                $tableId = $request->input('tableId');
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
        } catch (\Exception $e) {
            \Log::error('Failed to load categories: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load categories');
        }
    }

    /**
     * Show products for a specific category
     */
    public function show(Request $request, $categorySlug): Response
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Failed to load category: ' . $e->getMessage(), [
                'categorySlug' => $categorySlug,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load category');
        }
    }
}
