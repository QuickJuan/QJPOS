<?php
namespace App\Services;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

class ProductCategoryService
{
    public function __construct(protected Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get all categories with active products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesWithProducts()
    {
        return $this->model->with([
            'products' => fn($query) => $query
                ->where('is_active', true)
                ->with('productPackagings', 'options')
        ])
        ->whereHas('products', fn($query) => $query->where('is_active', true))
        ->get();
    }

    /**
     * Get all categories with active products as resources
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getCategoriesWithProductsAsResources()
    {
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
}
