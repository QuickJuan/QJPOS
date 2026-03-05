<?php

declare (strict_types = 1);

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\EWalletController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAddOnController;
use App\Http\Controllers\TableRoomController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CashierSessionController;
use App\Http\Controllers\CashierCashoutController;
use App\Http\Controllers\PrintXReadingShiftController;
use App\Http\Controllers\PrintSalesInvoiceReportController;
use App\Http\Controllers\TableManagementController;
use App\Http\Controllers\TenantLandingController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\Waiter\AuthController as WaiterAuthController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Only register tenant routes if NOT on central domain
if (!isCentralDomain()) {
    require __DIR__.'/jetstream.php';

    Route::middleware([
        InitializeTenancyBySubdomain::class,
        PreventAccessFromCentralDomains::class,
        'web',

])->group(function () {

    // PUBLIC STATIC FILE ROUTES (no auth required)
    Route::get('/manifest.json', function () {
        $manifestPath = public_path('manifest.json');
        if (file_exists($manifestPath)) {
            return response()->file($manifestPath, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        abort(404);
    })->name('manifest');

    // ROUTE FOR AUTHENTICATION
    Route::controller(AuthController::class)
        ->group(function () {
            // With 'guest' middleware
            Route::middleware('guest')
                ->group(function () {
                    Route::get('/login', 'index')->name('login');
                    Route::post('/login', 'login')
                        ->name('login.post')
                        ->middleware('throttle:login');
                    Route::get('/branches/validate/{id}', 'checkBranch')->name('branches.validate');
                });

            // With 'auth' middleware
            Route::middleware('auth')
                ->group(function () {
                    Route::post('/logout', 'logout')->name('logout');
                });
        });

    // WAITER AUTHENTICATION ROUTES
    Route::prefix('waiter')->name('waiter.')->group(function () {
        Route::controller(WaiterAuthController::class)->group(function () {
            // Guest routes
            Route::middleware('guest')->group(function () {
                Route::get('/login', 'index')->name('login');
                Route::get('/branches/{branchId}/users', 'getBranchUsers')->name('branch-users');
                Route::post('/verify-otp', 'verifyOtp')
                    ->name('verify-otp')
                    ->middleware('throttle:5,1');
            });

            // Auth routes
            Route::middleware('auth')->group(function () {
                Route::post('/logout', 'logout')->name('logout');
            });
        });
    });

    // ROUTE FOR FORGOT PASSWORD
    Route::controller(PasswordResetLinkController::class)
        ->middleware('guest')
        ->group(function () {
            Route::get('forgot-password', 'create')->name('password.request');
            Route::post('forgot-password', 'store')->name('password.email');
        });

    // ROUTE FOR RESET PASSWORD
    Route::controller(NewPasswordController::class)
        ->middleware('guest')
        ->group(function () {
            Route::get('reset-password/{token}', 'create')->name('password.reset');
            Route::post('reset-password', 'store')->name('password.update');
        });

    // PASSWORD CONFIRMATION ROUTES
    Route::middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

            Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
                ->name('password.confirmation');

            Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->name('password.confirm.store');
        });

    // ROUTE FOR PUBLIC LANDING PAGE (for guests and authenticated users)
    Route::get('/', function (Illuminate\Http\Request $request) {
        // Check if there's a page builder landing page
        $hasLandingPage = \App\Models\Page::where('page_type', \App\Enums\PageType::LANDING_PAGE->value)
            ->where('status', 'published')
            ->exists();

        if ($hasLandingPage) {
            // Show page builder landing page for everyone
            return app(PublicPageController::class)->landing();
        } else {
            // Fall back to default tenant landing if no page builder landing exists
            return app(TenantLandingController::class)($request);
        }
    })->name('landing');

    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

    // Serve tenant storage files (receipts, etc.)
    Route::get('/storage/{path}', function ($path) {
        $filePath = storage_path('app/public/' . $path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    })->where('path', '.*')->name('tenant.storage');

    Route::get('/test-landing', function () {
        return Inertia::render('TestLanding', [
            'tenant' => tenant(),
        ]);
    })->name('test-landing');

    // Print view for X-Reading (Cashier Shift) on bond paper
    Route::middleware('auth')
        ->get('/print/x-reading/{cashierSession}', PrintXReadingShiftController::class)
        ->name('x-reading.print');

    Route::middleware('auth')
        ->get('/reports/sales-invoice/print', PrintSalesInvoiceReportController::class)
        ->name('sales-invoice-report.print');

    // ROUTES FOR AUTHENTICATED USER
    Route::middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('/home', [HomeController::class, 'index'])->name('home');

            // Route::get('/dashboard', function () {
            //     return Inertia::render('Dashboard', [
            //         'tenant' => tenant(),
            //     ]);
            // })->name('dashboard');

            // Receipt Preview Route (for testing)
            Route::get('/receipt-preview', function () {
                return Inertia::render('ReceiptPreview');
            })->name('receipt-preview');

            // Receipt Route
            Route::get('/receipt/{id}', function ($id) {
                return Inertia::render('Receipt', [
                    'receiptId' => $id,
                ]);
            })->name('receipt');

            // TWO-FACTOR AUTHENTICATION ROUTES
            Route::middleware(['password.confirm'])
                ->group(function () {
                    Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
                        ->name('two-factor.enable');

                    Route::post('/user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                        ->name('two-factor.confirm');

                    Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                        ->name('two-factor.disable');

                    Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
                        ->name('two-factor.qr-code');

                    Route::get('/user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
                        ->name('two-factor.secret-key');

                    Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
                        ->name('two-factor.recovery-codes');

                    Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store']);
                });

            // ROUTE FOR ATTENDANCE
            Route::as('attendance.')
                ->prefix('/attendance')
                ->controller(AttendanceController::class)
                ->group(function () {
                    Route::get('/attendance', 'index')->name('index');
                    Route::get('/attendance/today', 'today')->name('today');
                    Route::get('/attendance/history', 'history')->name('history');
                    Route::post('/attendance/check-status', 'checkStatus')->name('check-status');
                    Route::post('/attendance/toggle', 'toggle')->name('toggle');
                });

            // ROUTE FOR RESTO CASHIER
            Route::as('resto.')
                ->prefix('/resto')
                ->group(function () {
                    // Shared POS ordering routes (cashiering + order_taking)
                    // MIDDLEWARE REMOVED FOR TESTING
                    // Route::middleware('role:cashiering,order_taking')
                        // ->group(function () {
                            // Product options (used by order-taking flow too)
                            Route::get('/product/{product}/options', [CashierSessionController::class, 'productOptions'])->name('product.options');

                            // Product add-ons
                            Route::get('/product/{product}/add-ons', [ProductAddOnController::class, 'index'])->name('product.add-ons');

                            Route::controller(CartController::class)
                                ->group(function () {
                                    Route::post('/cart/create-order', 'create')->name('cart.create-order');
                                    Route::post('/cart/add', 'addToCart')->name('cart.add');
                                    Route::post('/cart/search-barcode', 'searchBarcode')->name('cart.search-barcode');
                                    Route::post('/cart/update-customer', 'updateCustomer')->name('cart.update-customer');
                                    Route::put('/cart/{cartId}', 'updateCart')->name('cart.update');
                                    Route::put('/cart/{cartId}/service-charge', 'updateServiceCharge')->name('cart.update-service-charge');
                                    Route::post('/cart/merge', 'mergeCart')->name('cart.merge');
                                    Route::post('/cart/place-order', 'placeOrder')->name('cart.place-order');
                                    Route::post('/cart/claim-order/{tableId}', 'claimOrder')->name('cart.claim-order');
                                    Route::put('/cart/item/discount/', 'applyDiscountToCartItem')->name('cart.apply-discount');
                                    Route::put('/cart/item/modifier/', 'applyModifierToCartItem')->name('cart.apply-modifier');
                                    Route::put('/cart/item/modifier/remove', 'removeModifierFromCartItem')->name('cart.remove-modifier');
                                    Route::put('/cart/item/add-on', 'applyAddOnToCartItem')->name('cart.apply-add-on');
                                    Route::put('/cart/item/{cartItemId}', 'updateCartItem')->name('cart.update-item');
                                    Route::put('/cart/item/clear-discount/{cartItemId}', 'clearDiscountToCartItem')->name('cart.clear-discount');
                                    Route::delete('/cart/item/{cartItemId}', 'deleteCartItem')->name('cart.delete');
                                });

                            // Cashier session routes (must come before category routes to avoid slug conflicts)
                            Route::controller(CashierSessionController::class)
                                ->group(function () {
                                    Route::get('/start-cashiering', 'preview')->name('start-cashiering');
                                    Route::get('/close-shift', 'showCloseShift')->name('close-shift');
                                    Route::get('/review/x-readings', 'reviewXTransactions')->name('review-x-readings');
                                    Route::post('/session/start', 'startSession')->name('session.start');
                                    Route::post('/session/close', 'closeShift')->name('session.close');
                                    Route::get('/session-summary/{shiftNo}', 'getSessionSummaryById')->name('api.session-summary-by-id');
                                    Route::put('/update-bill-no/{branchId}', 'updateBillNo')->name('update-bill-no');
                                });

                            Route::controller(CashierCashoutController::class)
                                ->prefix('/cashier-cashouts')
                                ->as('cashier-cashouts.')
                                ->group(function () {
                                    Route::get('/', 'index')->name('index');
                                    Route::get('/create', 'create')->name('create');
                                    Route::post('/', 'store')->name('store');
                                });

                            Route::controller(CartController::class)
                                ->group(function () {
                                    Route::get('/cart/{cart}/settle-payment', 'showSettlePayment')->name('cart.settle-payment');
                                    Route::get('/cart/reprint-order/{batchNumber}', 'reprintPlacedOrder')->name('cart.reprint-order');
                                    Route::post('/cart/settle-bill', 'settleBill')->name('cart.settle-bill');
                                    Route::post('/cart/transfer-items}', 'transferItems')->name('cart.transfer-items');
                                    Route::put('/cart/update-bill-number/{cartId}', 'updateBillNumber')->name('cart.update-bill-number');
                                    Route::post('/order/transfer/{tableId}', 'transferOrder')->name('order.transfer');
                                    Route::put('/cart/item/void/{cartItemId}', 'voidCartItem')->name('cart.void-cart');
                                    Route::post('/cart/item/delete-with-approval', 'deleteCartItemWithApproval')->name('cart.delete-with-approval');
                                    Route::get('/cart/item/{cartItemId}/approvers', 'getApproversForItem')->name('cart.get-approvers');
                                });

                            // Pending orders (must be before category wildcard)
                            Route::controller(\App\Http\Controllers\PendingOrdersController::class)
                                ->prefix('/pending-orders')
                                ->as('pending-orders.')
                                ->group(function () {
                                    Route::get('/list', 'index')->name('index');
                                    Route::put('/item/{itemId}/toggle-served', 'toggleServed')->name('toggle-served');
                                });

                            // Category routes (wildcard routes should come last)
                            Route::controller(\App\Http\Controllers\CategoryController::class)
                                ->group(function () {
                                    Route::get('/', 'index')->name('index');
                                    Route::get('/{categorySlug}', 'show')->name('category');
                                });
                        // });
                });

            Route::as('table-management.')
                ->prefix('/table-management')
                ->controller(TableManagementController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/locations', 'storeLocation')->name('store-location');
                    Route::put('/locations/{location}', 'updateLocation')->name('update-location');
                    Route::delete('/locations/{location}', 'destroyLocation')->name('destroy-location');
                });

            // E-Wallet Routes
            Route::as('ewallet.')
                ->prefix('/ewallet')
                ->controller(EWalletController::class)
                ->group(function () {
                    Route::post('/load-change', 'loadChange')->name('load-change');
                    Route::get('/balance/{customerId}', 'getBalance')->name('balance');
                    Route::get('/transactions/{customerId}', 'getTransactions')->name('transactions');
                    Route::get('/points-transactions/{customerId}', 'getPointsTransactions')->name('points-transactions');
                    Route::post('/payment', 'makePayment')->name('payment');
                });

            Route::as('table-rooms.')
                ->prefix('/table-rooms')
                // MIDDLEWARE REMOVED FOR TESTING
                // ->middleware('role:cashiering,order_taking')
                ->controller(TableRoomController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/list', 'list')->name('list');
                    Route::get('/get-all-tables', 'getAllTables')->name('get-all-tables');
                    Route::post('/tables', 'store')->name('store');
                    Route::post('/tables/bulk-update-positions', 'bulkUpdatePositions')->name('bulk-update-positions');
                    Route::post('/tables/reserve', 'reserveTable')->name('reserve');
                    Route::post('/tables/{tableId}/vacant', 'vacantTable')->name('vacant');
                    Route::put('/tables/{tableId}', 'update')->name('update');
                    Route::put('/tables/{tableId}/unmerge', 'unmergeTable')->name('unmerge');
                    Route::put('/tables/{tableId}/unmerge-all', 'unmergeAllTables')->name('unmerge-all');
                    Route::put('/tables/{tableId}/merge', 'mergeTable')->name('merge');
                    Route::delete('/tables/{tableId}', 'destroy')->name('destroy');
                });

            // ROUTES FOR TRANSACTIONS/ORDERS
            Route::as('transactions.')
                ->prefix('/transactions')
                ->controller(\App\Http\Controllers\OrderController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/api/orders', 'index')->name('api.orders');
                    Route::get('/api/orders/{order}', 'show')->name('api.orders.show');
                    Route::post('/api/orders/{order}/refund', 'refund')->name('api.orders.refund');
                    Route::post('/api/orders/{order}/send-receipt-email', 'sendReceiptEmail')->name('api.send-receipt-email');
                    Route::get('/api/approvers', 'getApprovers')->name('api.approvers');
                    Route::get('/download-receipt/{order}', 'downloadReceipt')->name('download-receipt');
                });

            // ROUTES FOR CUSTOMERS
            Route::as('customers.')
                ->prefix('/customers')
                ->controller(\App\Http\Controllers\CustomerController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::put('/{customer}', 'update')->name('update');
                    Route::delete('/{customer}', 'destroy')->name('destroy');
                    Route::get('/search', 'search')->name('search');
                });

            // ROUTES FOR PRINTER CONFIGURATION
            Route::as('printer-config.')
                ->prefix('/printer-config')
                ->controller(\App\Http\Controllers\PrinterConfigController::class)
                ->group(function () {
                    Route::get('/{tableId?}', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::put('/{printerConfig}', 'update')->name('update');
                    Route::delete('/{printerConfig}', 'destroy')->name('destroy');
                    Route::post('/{printerConfig}/test', 'testPrinter')->name('test');
                });

            // API ROUTES FOR PRINTER CONFIGURATION
            Route::as('api.printer-config.')
                ->prefix('/api/printer-config')
                ->controller(\App\Http\Controllers\PrinterConfigController::class)
                ->group(function () {
                    Route::get('/{type}', 'getConfig')->name('get-config');
                });

            // API ROUTES FOR TABLES
            Route::as('api.tables.')
                ->prefix('/api')
                ->controller(\App\Http\Controllers\Api\TableController::class)
                ->group(function () {
                    Route::get('/branches/{branchId}/tables', 'getTablesByBranch')->name('branch-tables');
                    Route::get('/tables/{tableId}/with-cart', 'getTableWithCart')->name('table-with-cart');
                });

            // NOTE: Page builder now managed through Filament admin (/admin/pages)
            // Frontend pages displayed via public routes (/{slug})

            // ROUTES FOR REPORTS
            // Route::as('reports.')
            //     ->prefix('/reports')
            //     ->group(function () {
            //         Route::get('/hourly-sales', [\App\Http\Controllers\HourlySalesReportController::class, 'index'])->name('hourly-sales');
            //     });

        });

    // Public pages routes with optional prefix
    // Note: Landing pages (page_type = 'landing_page') should only be accessible via the root URL '/'
    // and not through their slug to avoid duplicate content
    Route::get('/{slug}', [\App\Http\Controllers\PublicPageController::class, 'show'])
        ->where('slug', '[a-zA-Z0-9\-]+')
        ->name('pages.show');  // Root level pages like /test, /about, /contact

    Route::get('/{prefix}/{slug}', [\App\Http\Controllers\PublicPageController::class, 'showWithPrefix'])
        ->where(['prefix' => '[a-zA-Z0-9\-]+', 'slug' => '[a-zA-Z0-9\-]+'])
        ->name('pages.show.prefix');  // Prefixed pages like /respiratory/test

    // // Web Receipt Route (for browser viewing)
    // Route::get('/receipt/{receiptNumber}', function($receiptNumber) {
    //     return Inertia::render('Receipt', [
    //         'receiptNumber' => $receiptNumber,
    //     ]);
    // })->name('receipt.view');
    });
}
