<?php
namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CashierSession;
use App\Models\Currency;
use App\Models\PaymentMethod;
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
        try {
            if (! $request->user()) {
                return null;
            }

            // Get the current user's active cashier session
            $branch = Branch::find($request->user()->branch_id);

            if (! $branch) {
                return null;
            }

            return $branch->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load active branch in HandleInertiaRequests: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'line' => $e->getLine(),
            ]);
            return null;
        }
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
                'hero_image'      => $companySettings['hero_image'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'company_name'    => '',
                'company_address' => '',
                'company_phone'   => '',
                'company_logo'    => '',
                'hero_image'      => '',
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

            if (! $cart) {
                return null;
            }

            // Refresh to get latest data
            $cart->refresh();

            // Load only essential relationships to avoid N+1 and timeout issues
            $cart->load(['tableRoom']);

            // Load cart items with their products separately to avoid deep nesting
            if ($cart->relationLoaded('cartItems') || $cart->cartItems) {
                $cart->cartItems->load(['product', 'productPackaging']);
            } else {
                $cart->load(['cartItems.product', 'cartItems.productPackaging']);
            }

            return $cart->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load cart in HandleInertiaRequests: ' . $e->getMessage(), [
                'tableId' => $tableId,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return null instead of throwing to prevent 502
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
            // Tenant-specific services - wrap in closures to lazy load
            'active_branch'           => fn() => $this->getActiveBranch($request),
            'receipt_headers'         => function() use ($request) {
                $branch = $this->getActiveBranch($request);
                return $branch['receipt_headers'] ?? [];
            },
            'receipt_footers'         => function() use ($request) {
                $branch = $this->getActiveBranch($request);
                return $branch['receipt_footer'] ?? [];
            },
            'bill_footer'             => function() use ($request) {
                $branch = $this->getActiveBranch($request);
                return $branch['receipt_footer'] ?? [];
            },
            'company_info'            => fn() => $this->getCompanyInfo(),
            'current_cashier_session' => function() {
                try {
                    $session = CashierSession::openSession()->with('cashier')->first();
                    return $session?->toArray();
                } catch (\Exception $e) {
                    \Log::error('Failed to load cashier session: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return null;
                }
            },
            'available_discounts' => fn() => $this->loadTenantDiscounts(),
            'cart' => fn() => $this->getCartByTableId($request),
            'available_modifiers' => fn() => $this->loadTenantModifiers(),
            'available_servers' => fn() => $this->getAvailableServers(),
            'currencies' => fn() => $this->loadTenantCurrencies(),
            'default_currency' => fn() => $this->getDefaultCurrency(),
            'payment_methods' => fn() => $this->loadTenantPaymentMethods(),
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
            \Log::error('Failed to load discounts in HandleInertiaRequests: ' . $e->getMessage());
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
            \Log::error('Failed to load modifiers in HandleInertiaRequests: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available servers/waiters
     * Use direct database query to avoid Spatie Permission scope issues
     */
    private function getAvailableServers(): array
    {
        if (!tenant()) {
            return [];
        }

        try {
            return User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Server', 'Waiter']);
                })
                ->select('id', 'name', 'employee_code')
                ->orderBy('name')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load servers in HandleInertiaRequests: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            // Return empty array on error to prevent 502
            return [];
        }
    }

    /**
     * Load active currencies for the tenant
     */
    private function loadTenantCurrencies(): array
    {
        if (!tenant()) {
            \Log::warning('loadTenantCurrencies called without tenant context');
            return [];
        }

        try {
            $currencies = Currency::active()
                ->with(['activeDenominations' => function ($query) {
                    $query->select('id', 'currency_id', 'value', 'label', 'sort_order', 'is_active');
                }])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'symbol', 'exchange_rate', 'is_default']);

            \Log::info('Loaded tenant currencies', [
                'tenant_id' => tenant('id'),
                'count' => $currencies->count(),
            ]);

            return $currencies->map(function (Currency $currency) {
                $currencySymbol = $currency->symbol ?? 'PHP ';

                return [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->name,
                    'symbol' => $currencySymbol,
                    'exchange_rate' => $currency->exchange_rate ?? 1,
                    'is_default' => (bool) $currency->is_default,
                    'denominations' => $currency->activeDenominations
                        ->filter(fn($denom) => $denom->is_active)
                        ->map(fn($denom) => [
                            'id' => $denom->id,
                            'value' => (float) $denom->value,
                            'label' => $denom->label ?? sprintf('%s%s', $currencySymbol, number_format((float) $denom->value, 2)),
                            'sort_order' => $denom->sort_order,
                        ])->values()->toArray(),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load currencies in HandleInertiaRequests: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve the default currency for display and change computation
     */
    private function getDefaultCurrency(): ?array
    {
        if (!tenant()) {
            return null;
        }

        try {
            return Currency::default()
                ->select('id', 'code', 'name', 'symbol', 'exchange_rate', 'is_default')
                ->first()?->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load default currency in HandleInertiaRequests: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Load active payment methods with their currencies
     */
    private function loadTenantPaymentMethods(): array
    {
        if (!tenant()) {
            return [];
        }

        try {
            return PaymentMethod::active()
                ->ordered()
                ->with('currency:id,code,name,symbol,exchange_rate,is_default')
                ->get(['id', 'name', 'payment_type', 'currency_id', 'is_active', 'sort_order'])
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load payment methods in HandleInertiaRequests: ' . $e->getMessage());
            return [];
        }
    }
}
