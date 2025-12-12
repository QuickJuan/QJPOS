<?php
namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\ModifierService;
use App\Services\GeneralSettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $activeBranch = $this->getActiveBranch($request);
        $openSession  = CashierSession::openSession()->with('cashier')->first();

        return array_merge(parent::share($request), [
            'flash'                   => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn()   => $request->session()->get('error'),
            ],
            'active_branch'           => $activeBranch,
            'auth'                    => [
                'user' => $request->user(),
            ],
            'current_cashier_session' => $openSession,
            // Receipt Configuration
            'receipt_headers'         => fn()         => $activeBranch['receipt_headers'] ?? [],
            'receipt_footers'         => fn()         => $activeBranch['receipt_footer'] ?? [],
            'bill_footer'             => fn()             => $activeBranch['receipt_footer'] ?? [],
            // Company Information (Main Site)
            'company_info'            => fn()            => $this->getCompanyInfo(),
            // Tenant-specific data
             ...$this->getTenantSharedData($request),
            // Main site data
             ...$this->getMainSiteSharedData($request),
            // 'tax_rate'            => config('sales.tax_rate'),
        ]);
    }

    /**
     * Get active branch from current user's cashier session
     */
    private function getActiveBranch(Request $request): ?array
    {
        if (! $request->user()) {
            return null;
        }

        // Get the current user's active cashier session
        $branch = Branch::find($request->user()->branch_id);

        if (! $branch) {
            return null;
        }

        return $branch->toArray();
    }

    /**
     * Get company information from general settings
     */
    private function getCompanyInfo(): array
    {
        try {
            $companySettings = app(GeneralSettingsService::class)->getCompanySettings();
            return [
                'company_name'    => $companySettings['company_name'] ?? '',
                'company_address' => $companySettings['company_address'] ?? '',
                'company_phone'   => $companySettings['company_phone'] ?? '',
                'company_logo'    => $companySettings['company_logo'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'company_name'    => '',
                'company_address' => '',
                'company_phone'   => '',
                'company_logo'    => '',
            ];
        }
    }

    /**
     * Get cart by table ID from query parameters
     */
    private function getCartByTableId(Request $request): ?array
    {
        $tableId = $request->query('tableId');
        if (! $tableId) {
            return null;
        }

        try {
            // Always fetch fresh cart data with relationships
            $cart = app(CartService::class)->getCartByTable((int) $tableId);

            if ($cart) {
                // Refresh to ensure we have the latest data from database
                $cart->refresh();
                $cart->load(['cartItems', 'tableRoom']);
            }

            return $cart ? $cart->toArray() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get shared data for tenant context
     * Add all tenant-specific services here
     */
    private function getTenantSharedData(Request $request): array
    {
        // If not in tenant context, return empty array
        if (! tenant()) {
            return [];
        }

        $sharedData = [
            // Tenant-specific services
            'available_discounts' =>  $this->loadTenantDiscounts(),
            'cart' => $this->getCartByTableId($request),
            'availableModifiers' => $this->loadTenantModifiers(),
        ];

        return $sharedData;
    }

    /**
     * Get shared data for main site
     * Add all main-site-specific services here
     */
    private function getMainSiteSharedData(Request $request): array
    {
        // Only return main site data if NOT in tenant context
        if (tenant()) {
            return [];
        }

        return [
            // Main site specific services can be added here
        ];
    }

    /**
     * Load available discounts for tenant
     */
    private function loadTenantDiscounts(): array
    {
        try {
            return app(DiscountService::class)->getAvailableDiscounts() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Load available modifiers for tenant
     */
    public function loadTenantModifiers(): array
    {
        try {

            return app(ModifierService::class)->getAvailableModifiers() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
