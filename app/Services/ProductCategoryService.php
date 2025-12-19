<?php
namespace App\Services;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Cache;

class ProductCategoryService
{
    protected int $cacheTTL = 3600; // 1 hour in seconds

    public function __construct(protected Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get only categories (without products) for lightweight initial load
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesOnly()
    {
        return Cache::remember('categories_only', $this->cacheTTL, function () {
            return $this->model
                ->select('id', 'name', 'slug')
                ->with('media')
                ->whereHas('products', fn($query) => $query->where('is_active', true))
                ->get();
        });
    }

    /**
     * Get all categories with active products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesWithProducts()
    {
        // Increase memory limit and execution time for this operation
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        return Cache::remember('categories_with_products', $this->cacheTTL, function () {
            return $this->model->with([
                'media', // Eager load category media
                'products' => fn($query) => $query
                    ->where('is_active', true)
                    ->select('id', 'name', 'average_cost', 'receipt_alias', 'description', 'category_id', 'brand_id', 'total_onhand', 'price', 'unit_measure', 'multiple_packaging', 'uuid')
                    ->with([
                        'productPackagings' => fn($q) => $q->with('media'), // Eager load packaging media
                        'options',
                        'media' // Eager load product media
                    ])
            ])
            ->whereHas('products', fn($query) => $query->where('is_active', true))
            ->get();
        });
    }

    /**
     * Get all categories with active products as resources
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getCategoriesWithProductsAsResources()
    {
        // Don't cache the resource collection - only cache the raw data
        // Resources contain request-specific data and can cause serialization issues
        $categories = $this->getCategoriesWithProducts();
        return CategoryResource::collection($categories);
    }

    /**
     * Get a single category with products by ID
     *
     * @param int $categoryId
     * @return \App\Models\Category|null
     */
    public function getCategoryWithProducts(int $categoryId)
    {
        return $this->model->with([
            'products' => fn($query) => $query
                ->where('is_active', true)
                ->with('productPackagings', 'options')
        ])->find($categoryId);
    }

    /**
     * Get a single category with products by slug
     *
     * @param string $slug
     * @return \App\Models\Category|null
     */
    public function getCategoryWithProductsBySlug(string $slug)
    {
        return $this->model->with([
            'products' => fn($query) => $query
                ->where('is_active', true)
                ->with('productPackagings', 'options')
        ])->where('slug', $slug)->first();
    }

    /**
     * Get products for a specific category with all necessary relations
     *
     * @param string $categorySlug
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductsForCategory($categorySlug)
    {
        $cacheKey = "category_{$categorySlug}_products";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($categorySlug) {
            $category = $this->model->where('slug', $categorySlug)->first();

            if (!$category) {
                return collect([]);
            }

            return $category->products()
                ->where('is_active', true)
                ->select('id', 'name', 'average_cost', 'receipt_alias', 'description', 'category_id', 'brand_id', 'total_onhand', 'price', 'unit_measure', 'multiple_packaging', 'uuid')
                ->with([
                    'productPackagings' => fn($q) => $q->with('media'),
                    'options.optionItems.product.media',
                    'options.optionItems.product.productPackagings',
                    'options.optionItems.productPackaging',
                    'media'
                ])
                ->get();
        });
    }

    /**
     * Clear categories cache
     * Call this method when categories or products are updated
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('categories_with_products');
        Cache::forget('categories_only');

        // Clear individual category product caches (use slug)
        $categories = $this->model->select('slug')->get();
        foreach ($categories as $category) {
            Cache::forget("category_{$category->slug}_products");
        }
    }
}
