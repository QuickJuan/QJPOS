<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\NavigationItem;
use App\Models\Product;
use App\Services\PageBuilderService;
use App\Settings\GeneralSettings;
use App\Enums\PageType;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(
        private PageBuilderService $pageBuilderService,
        private GeneralSettings $settings,
    ) {}

    /**
     * Display the specified published page.
     */
    public function show(string $slug): Response
    {
        // Try to find the page (published or draft for preview)
        $page = Page::with('seo')
            ->where('slug', $slug)
            ->whereNull('url_prefix')
            ->where('page_type', '!=', PageType::LANDING_PAGE->value) // Exclude landing pages
            ->firstOrFail();

        // Increment view count only for published pages
        if ($page->status === 'published') {
            $this->pageBuilderService->incrementViewCount($page);
        }

        return $this->renderPage($page);
    }

    /**
     * Display the specified published page with prefix.
     */
    public function showWithPrefix(string $prefix, string $slug): Response
    {
        $page = Page::with('seo')
            ->where('url_prefix', $prefix)
            ->where('slug', $slug)
            ->where('page_type', '!=', PageType::LANDING_PAGE->value) // Exclude landing pages
            ->firstOrFail();

        // Increment view count only for published pages
        if ($page->status === 'published') {
            $this->pageBuilderService->incrementViewCount($page);
        }

        return $this->renderPage($page);
    }

    /**
     * Render the page with Inertia
     */
    private function renderPage(Page $page): Response
    {
        // Load blocks with their types
        $blocks = $page->blocks()
            ->with('blockType')
            ->orderBy('order')
            ->get()
            ->map(function ($block) {
                $data = [
                    'id'       => $block->id,
                    'type'     => $block->blockType->slug,
                    'content'  => $block->content,
                    'settings' => $block->settings,
                    'order'    => $block->order,
                ];

                // Inject live products for the products block
                if ($block->blockType->slug === 'products') {
                    $data['products'] = $this->resolveBlockProducts($block->settings ?? []);
                }

                return $data;
            });

        // Load navigation items
        $navigation = NavigationItem::active()
            ->rootItems()
            ->with('children')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->label,
                    'url' => $item->url,
                    'target' => $item->target,
                    'children' => $item->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'label' => $child->label,
                            'url' => $child->url,
                            'target' => $child->target,
                        ];
                    }),
                ];
            });

        return Inertia::render('PublicPage/Show', [
            'navigation' => $navigation,
            'appName' => $this->settings->company_name ?? config('app.name', 'QuickJuan POS'),
            'companyLogo' => $this->settings->company_logo ? tenant_asset($this->settings->company_logo) : null,
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'hide_title' => $page->hide_title,
                'slug' => $page->slug,
                'url_prefix' => $page->url_prefix,
                'description' => $page->description,
                'featured_image' => $page->featured_image,
                'content_json' => $page->content_json,
                'blocks' => $blocks,
                'is_landing_page' => $page->page_type === PageType::LANDING_PAGE,
            ],
            'seo' => $page->seo ? [
                'meta_title' => $page->seo->meta_title,
                'meta_description' => $page->seo->meta_description,
                'meta_keywords' => $page->seo->meta_keywords,
                'canonical_url' => $page->seo->canonical_url,
                'meta_robots' => $page->seo->meta_robots,
                'og_title' => $page->seo->og_title,
                'og_description' => $page->seo->og_description,
                'og_image' => $page->seo->og_image ? tenant_asset($page->seo->og_image) : null,
                'twitter_card' => $page->seo->twitter_card,
                'twitter_title' => $page->seo->twitter_title,
                'twitter_description' => $page->seo->twitter_description,
                'twitter_image' => $page->seo->twitter_image ? tenant_asset($page->seo->twitter_image) : null,
                'schema_type' => $page->seo->schema_type,
                'schema_json' => $page->seo->schema_json,
            ] : null,
        ]);
    }

    /**
     * Resolve products for a products block based on its filter settings.
     */
    private function resolveBlockProducts(array $settings): array
    {
        $productIds  = $settings['product_ids'] ?? [];
        $categoryIds = $settings['category_ids'] ?? [];
        $groupIds    = $settings['group_ids'] ?? [];
        $max         = isset($settings['max_products']) && $settings['max_products'] > 0
            ? (int) $settings['max_products']
            : null;

        $query = Product::query()
            ->where('is_active', true)
            ->with('category');

        if (!empty($productIds)) {
            // Specific products selected — ignore category/group filters
            $query->whereIn('id', $productIds);
        } elseif (!empty($categoryIds) && !empty($groupIds)) {
            $query->where(function ($q) use ($categoryIds, $groupIds) {
                $q->whereIn('category_id', $categoryIds)
                  ->orWhereHas('groups', fn ($g) => $g->whereIn('id', $groupIds));
            });
        } elseif (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        } elseif (!empty($groupIds)) {
            $query->whereHas('groups', fn ($g) => $g->whereIn('id', $groupIds));
        }

        $query->orderBy('name');

        if ($max) {
            $query->limit($max);
        }

        return $query->get()->map(function (Product $product) {
            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'description' => $product->description,
                'price'       => $product->price,
                'category'    => $product->category?->name,
                'image_url'   => $product->featured_image_url,
            ];
        })->values()->all();
    }

    /**
     * Display the landing page for non-authenticated users.
     */
    public function landing(): Response
    {
        // Find the first active landing page
        $page = Page::with('seo')
            ->where('page_type', PageType::LANDING_PAGE->value)
            ->where('status', 'published')
            ->first();

        // If no landing page exists, return a default view
        if (!$page) {
            return Inertia::render('PublicPage/NoLanding', [
                'appName' => $this->settings->company_name ?? config('app.name', 'QuickJuan POS'),
                'companyLogo' => $this->settings->company_logo ? tenant_asset($this->settings->company_logo) : null,
            ]);
        }

        // Increment view count
        $this->pageBuilderService->incrementViewCount($page);

        return $this->renderPage($page);
    }
}
