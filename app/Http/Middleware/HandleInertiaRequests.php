<?php
namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\ModifierService;
use App\Services\GeneralSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // $openSession  = CashierSession::openSession()->with('cashier')->first();

        return array_merge(parent::share($request), [
            'flash'                   => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn()   => $request->session()->get('error'),
                'message' => fn() => $request->session()->get('message'),
                'sessionSummary' => fn() => $request->session()->get('sessionSummary'),
                'shouldLogout' => fn() => $request->session()->get('shouldLogout'),
            ],

            'auth'                    => [
                'user' => $request->user(),
            ],

            // Receipt Configuration
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

        $activeBranch = $this->getActiveBranch($request);
        $openSession  = CashierSession::openSession()->with('cashier')->first();

        $sharedData = [
            // Tenant-specific services
            'active_branch'           => $activeBranch,
            'receipt_headers'         => fn() => $activeBranch['receipt_headers'] ?? [],
            'receipt_footers'         => fn() => $activeBranch['receipt_footer'] ?? [],
            'bill_footer'             => fn() => $activeBranch['receipt_footer'] ?? [],
            'company_info'            => fn() => $this->getCompanyInfo(),
            'current_cashier_session' => $openSession,
            'available_discounts' =>  $this->loadTenantDiscounts(),
            'cart' => $this->getCartByTableId($request),
            'available_modifiers' => $this->loadTenantModifiers(),
            'available_servers' => $this->getAvailableServers(),
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

    /**
     * Get available servers/waiters with daily cache
     * Cache key includes tenant ID to ensure tenant isolation
     */
    private function getAvailableServers(): array
    {
        if (!tenant()) {
            return [];
        }

        $tenantId = tenant()->id;
        $cacheKey = "tenant_{$tenantId}_servers_" . now()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->endOfDay(), function () {
            try {
                // Check if HasRoles trait is available before using role method
                if (!method_exists(User::class, 'role')) {
                    \Log::warning('Spatie Permission not available - returning empty servers list');
                    return [];
                }

                return User::role(['Server', 'Waiter'])
                    ->select('id', 'name', 'employee_code')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                \Log::error('Failed to load servers: ' . $e->getMessage());
                return [];
            }
        });
    }
}
