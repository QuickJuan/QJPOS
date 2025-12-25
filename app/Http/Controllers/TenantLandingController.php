<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Services\ProductCategoryService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantLandingController extends Controller
{
    public function __construct(
        protected ProductCategoryService $categoryService,
        protected ProductGroupService $groupService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $branches = Branch::orderBy('name')
            ->get([
                'id',
                'name',
                'branch_code',
                'address',
                'phone',
                'email',
                'contact_person',
                'is_active',
            ]);

        $categories = $this->prepareCategoriesPayload();
        $groups = $this->prepareGroupsPayload();

        $selectedCategorySlug = $this->queryString($request, 'category');
        $selectedGroupSlug = $this->queryString($request, 'group');
        $searchQuery = $this->queryString($request, 'search');
        $filterMode = $this->resolveFilterMode($this->queryString($request, 'mode'), $categories, $groups);

        return Inertia::render('TenantLanding', [
            'tenant' => tenant(),
            'branches' => $branches,
            'categories' => $categories,
            'groups' => $groups,
            'selectedCategorySlug' => $selectedCategorySlug,
            'selectedGroupSlug' => $selectedGroupSlug,
            'filterMode' => $filterMode,
            'searchQuery' => $searchQuery,
        ]);
    }

    protected function prepareCategoriesPayload(): array
    {
        return $this->categoryService
            ->getCategoriesWithProducts()
            ->map(function ($category) {
                $products = $category->products
                    ->map(fn($product) => $this->formatProduct($product, $category->name))
                    ->values()
                    ->all();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'featured_image_url' => $category->getFirstMediaUrl('featured_image'),
                    'products' => $products,
                ];
            })
            ->filter(fn($category) => count($category['products']) > 0)
            ->values()
            ->all();
    }

    protected function prepareGroupsPayload(): array
    {
        return $this->groupService
            ->getGroupsWithProducts()
            ->map(function ($group) {
                $products = $group->products
                    ->map(fn($product) => $this->formatProduct($product))
                    ->values()
                    ->all();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'slug' => $group->slug,
                    'featured_image_url' => $group->getFirstMediaUrl('featured_image'),
                    'products' => $products,
                ];
            })
            ->filter(fn($group) => count($group['products']) > 0)
            ->values()
            ->all();
    }

    protected function formatProduct(Product $product, ?string $categoryName = null): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category' => $categoryName ?? optional($product->category)->name,
            'featured_image_url' => $product->featured_image_url,
        ];
    }

    protected function resolveFilterMode(?string $mode, array $categories, array $groups): string
    {
        $mode = strtolower($mode ?? 'category');
        if (!in_array($mode, ['category', 'group'], true)) {
            return 'category';
        }

        if ($mode === 'group' && empty($groups)) {
            return 'category';
        }

        if ($mode === 'category' && empty($categories) && !empty($groups)) {
            return 'group';
        }

        return $mode;
    }

    protected function queryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
