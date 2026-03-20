<?php
namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\User;
use App\Models\Branch;
use Inertia\Middleware;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Services\CartService;
use App\Models\CashierSession;
use App\Services\DiscountService;
use App\Services\ModifierService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\GeneralSettingsService;

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

        // Log request for debugging 502 issues
        if (env('APP_DEBUG')) {
            \Log::debug('HandleInertiaRequests.share() called', [
                'url' => $request->url(),
                'method' => $request->method(),
                'tenant' => tenant('id') ?? 'none',
                'user_id' => $request->user()?->id,
            ]);
        }

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
                'timezone'        => $companySettings['timezone'] ?? 'UTC',
            ];
        } catch (\Exception $e) {
            return [
                'company_name'    => '',
                'company_address' => '',
                'company_phone'   => '',
                'company_logo'    => '',
                'hero_image'      => '',
                'timezone'        => 'UTC',
            ];
        }
    }

    /**
     * Get general settings including timezone
     */
    private function getGeneralSettings(): array
    {
        try {
            $settings = app(GeneralSettingsService::class)->getCompanySettings();
            return [
                'company_name'    => $settings['company_name'] ?? '',
                'company_address' => $settings['company_address'] ?? '',
                'company_contact' => $settings['company_contact'] ?? '',
                'company_logo'    => $settings['company_logo'] ?? '',
                'hero_image'      => $settings['hero_image'] ?? '',
                'timezone'        => $settings['timezone'] ?? 'UTC',
            ];
        } catch (\Exception $e) {
            return [
                'company_name'    => '',
                'company_address' => '',
                'company_contact' => '',
                'company_logo'    => '',
                'hero_image'      => '',
                'timezone'        => 'UTC',
            ];
        }
    }

    /**
     * Get cart by table ID from query parameters
     */
    private function getCartByTableId(Request $request): ?array
    {
        $tableId = $request->query('tableId')
            ?? $request->route('tableId')
            ?? $request->input('tableId')
            ?? $request->input('table_id');
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
            $cart->load(['tableRoom.tableRoomLocation', 'customer']);

            // Load cart items with their products separately to avoid deep nesting
            if ($cart->relationLoaded('cartItems') || $cart->cartItems) {
                $cart->cartItems->load(['product', 'productPackaging', 'children.product']);
            } else {
                $cart->load(['cartItems.product', 'cartItems.productPackaging', 'cartItems.children.product']);
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
            'active_branch'           => fn() => $this->debugLoadProperty('active_branch', fn() => $this->getActiveBranch($request)),
            'receipt_headers'         => function() use ($request) {
                return $this->debugLoadProperty('receipt_headers', function() use ($request) {
                    $branch = $this->getActiveBranch($request);
                    return $branch['receipt_headers'] ?? [];
                });
            },
            'receipt_footers'         => function() use ($request) {
                return $this->debugLoadProperty('receipt_footers', function() use ($request) {
                    $branch = $this->getActiveBranch($request);
                    return $branch['receipt_footer'] ?? [];
                });
            },
            'bill_footer'             => function() use ($request) {
                return $this->debugLoadProperty('bill_footer', function() use ($request) {
                    $branch = $this->getActiveBranch($request);
                    return $branch['receipt_footer'] ?? [];
                });
            },
            'company_info'            => fn() => $this->debugLoadProperty('company_info', fn() => $this->getCompanyInfo()),
            'generalSettings'         => fn() => $this->debugLoadProperty('generalSettings', fn() => $this->getGeneralSettings()),
            'current_cashier_session' => function() {
                return $this->debugLoadProperty('current_cashier_session', function() {
                    // Only load if user is authenticated
                    if (!Auth::check()) {
                        return null;
                    }

                    try {
                        $session = CashierSession::where('cashier_id', Auth::id())
                            ->whereNull('closing_time')
                            ->with('cashier:id,name,email')
                            ->select('id', 'cashier_id', 'branch_id', 'started_time', 'closing_time')
                            ->first();
                        return $session?->toArray();
                    } catch (\Exception $e) {
                        \Log::error('Failed to load cashier session: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return null;
                    }
                });
            },
            'available_discounts' => fn() => $this->debugLoadProperty('available_discounts', fn() => $this->loadTenantDiscounts()),
            'cart' => fn() => $this->debugLoadProperty('cart', fn() => $this->getCartByTableId($request)),
            'available_modifiers' => fn() => $this->debugLoadProperty('available_modifiers', fn() => $this->loadTenantModifiers()),
            'available_servers' => fn() => $this->debugLoadProperty('available_servers', fn() => $this->getAvailableServersOptimized()),
            'currencies' => fn() => $this->debugLoadProperty('currencies', fn() => $this->loadTenantCurrencies()),
            'default_currency' => fn() => $this->debugLoadProperty('default_currency', fn() => $this->getDefaultCurrency()),
            'payment_methods' => fn() => $this->debugLoadProperty('payment_methods', fn() => $this->loadTenantPaymentMethods()),
            'unread_notifications_count' => fn() => $this->getUnreadNotificationsCount($request),
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
            // Cache discounts for 10 minutes
            $cacheKey = 'tenant_discounts_' . tenant('id');
            return cache()->remember($cacheKey, now()->addMinutes(10), function () {
                return app(DiscountService::class)->getAvailableDiscounts() ?? [];
            });
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
            // Cache modifiers for 10 minutes
            $cacheKey = 'tenant_modifiers_' . tenant('id');
            return cache()->remember($cacheKey, now()->addMinutes(10), function () {
                return app(ModifierService::class)->getAvailableModifiers() ?? [];
            });
        } catch (\Exception $e) {
            \Log::error('Failed to load modifiers in HandleInertiaRequests: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available servers/waiters
     * Use direct database query to avoid Spatie Permission scope issues
     */
    private function getAvailableServersOptimized(): array
    {
        if (!tenant()) {
            return [];
        }

        try {
            // Cache servers for 5 minutes to avoid repeated queries
            $cacheKey = 'tenant_servers_' . tenant('id');

            return cache()->remember($cacheKey, now()->addMinutes(5), function () {
                return User::distinct()
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->whereIn('roles.name', ['Server', 'Waiter'])
                    ->select('users.id', 'users.name', 'users.employee_code')
                    ->orderBy('users.name')
                    ->get()
                    ->toArray();
            });
        } catch (\Exception $e) {
            \Log::error('Failed to load servers in HandleInertiaRequests: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            // Return empty array on error to prevent 502
            return [];
        }
    }

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
     * Load active currencies for the tenant (from cash payment methods)
     */
    private function loadTenantCurrencies(): array
    {
        if (!tenant()) {
            \Log::warning('loadTenantCurrencies called without tenant context');
            return [];
        }

        try {
            // Cache currencies for 30 minutes
            $cacheKey = 'tenant_currencies_' . tenant('id');
            return cache()->remember($cacheKey, now()->addMinutes(30), function () {
                $paymentMethods = PaymentMethod::active()
                    ->where('payment_type', 'cash')
                    ->with(['denominations' => function ($query) {
                        $query->where('is_active', true)
                            ->select('id', 'payment_method_id', 'value', 'label', 'sort_order', 'is_active')
                            ->orderBy('sort_order')
                            ->orderByDesc('value');
                    }])
                    ->orderByDesc('is_default_cash')
                    ->orderBy('name')
                    ->get(['id', 'currency_code', 'currency_name', 'symbol', 'exchange_rate', 'is_default_cash']);


                return $paymentMethods->map(function (PaymentMethod $paymentMethod) {
                    $currencySymbol = $paymentMethod->symbol ?? '₱';

                    return [
                        'id' => $paymentMethod->id,
                        'code' => $paymentMethod->currency_code,
                        'name' => $paymentMethod->currency_name,
                        'symbol' => $currencySymbol,
                        'exchange_rate' => $paymentMethod->exchange_rate ?? 1,
                        'is_default' => (bool) $paymentMethod->is_default_cash,
                        'denominations' => $paymentMethod->denominations
                            ->map(fn($denom) => [
                                'id' => $denom->id,
                                'value' => (float) $denom->value,
                                'label' => $denom->label ?? sprintf('%s%s', $currencySymbol, number_format((float) $denom->value, 2)),
                                'sort_order' => $denom->sort_order,
                            ])->values()->toArray(),
                    ];
                })->toArray();
            });
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
            $defaultCash = PaymentMethod::defaultCash()
                ->select('id', 'currency_code', 'currency_name', 'symbol', 'exchange_rate', 'is_default_cash')
                ->first();

            if (!$defaultCash) {
                return null;
            }

            return [
                'id' => $defaultCash->id,
                'code' => $defaultCash->currency_code,
                'name' => $defaultCash->currency_name,
                'symbol' => $defaultCash->symbol,
                'exchange_rate' => $defaultCash->exchange_rate,
                'is_default' => true,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to load default currency in HandleInertiaRequests: ' . $e->getMessage());
            return null;
        }
    }

    private function getUnreadNotificationsCount(Request $request): int
    {
        try {
            return $request->user()?->unreadNotifications()->count() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Load active payment methods
     */
    private function loadTenantPaymentMethods(): array
    {
        if (!tenant()) {
            return [];
        }

        try {
            // Cache payment methods for 30 minutes
            $cacheKey = 'tenant_payment_methods_' . tenant('id');
            return cache()->remember($cacheKey, now()->addMinutes(30), function () {
                return PaymentMethod::active()
                    ->ordered()
                    ->get(['id', 'name', 'payment_type', 'currency_code', 'currency_name', 'symbol', 'exchange_rate', 'is_active', 'sort_order'])
                    ->toArray();
            });
        } catch (\Exception $e) {
            \Log::error('Failed to load payment methods in HandleInertiaRequests: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Debug helper to track which property is timing out
     */
    private function debugLoadProperty(string $propertyName, callable $callback): mixed
    {
        $startTime = microtime(true);

        try {
            $result = $callback();
            $duration = microtime(true) - $startTime;

            // Log slow queries (over 1 second)
            if ($duration > 1) {
                \Log::warning("Slow property load in HandleInertiaRequests: {$propertyName}", [
                    'duration_ms' => round($duration * 1000, 2),
                    'tenant_id' => tenant('id') ?? 'none',
                    'url' => request()->url(),
                ]);
            }

            // Log if DEBUG mode is on
            if (env('APP_DEBUG')) {
                \Log::debug("Property loaded: {$propertyName}", [
                    'duration_ms' => round($duration * 1000, 2),
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            \Log::error("CRITICAL: Property load failed: {$propertyName}", [
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id') ?? 'none',
                'url' => request()->url(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return sensible defaults to prevent 502
            return match($propertyName) {
                'active_branch' => null,
                'current_cashier_session' => null,
                'default_currency' => null,
                default => [],
            };
        }
    }
}


