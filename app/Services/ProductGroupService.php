<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductGroupService
{
    protected int $cacheTTL = 3600; // 1 hour

    public function __construct(protected Group $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve all groups that have active products attached.
     */
    public function getGroupsWithProducts()
    {
        $productTable = (new Product())->getTable();

        return Cache::remember('groups_with_products', $this->cacheTTL, function () use ($productTable) {
            return $this->model
                ->with([
                    'media',
                    'products' => fn($query) => $query
                        ->where("{$productTable}.is_active", true)
                        ->select(
                            "{$productTable}.id",
                            "{$productTable}.name",
                            "{$productTable}.description",
                            "{$productTable}.price",
                            "{$productTable}.category_id"
                        )
                        ->with([
                            'category:id,name',
                            'media',
                        ]),
                ])
                ->whereHas('products', fn($query) => $query->where("{$productTable}.is_active", true))
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear the cached groups payload when data changes.
     */
    public function clearCache(): void
    {
        Cache::forget('groups_with_products');
    }
}
