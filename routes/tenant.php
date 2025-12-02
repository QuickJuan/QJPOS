<?php

declare (strict_types = 1);

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TableRoomController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\CashierSessionController;
use App\Http\Controllers\TableManagementController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;

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

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // ROUTE FOR AUTHENTICATION
    Route::controller(AuthController::class)
        ->group(function () {
            // With 'guest' middleware
            Route::middleware('guest')
                ->group(function () {
                    Route::get('/login', 'index')->name('login');
                    Route::post('/login', 'login')->name('login.post');
                    Route::get('/branches/validate/{id}', 'checkBranch')->name('branches.validate');
                });

            // With 'auth' middleware
            Route::middleware('auth')
                ->group(function () {
                    Route::post('/logout', 'logout')->name('logout');
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

    // ROUTE FOR PUBLIC LANDING PAGE
    Route::get('/', function () {
        return Inertia::render('Landing', [
            'tenant' => tenant(),
        ]);
    })->name('landing');

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
                    Route::controller(CashierSessionController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            // Route::get('/tables', 'tables')->name('tables');
                            Route::get('/preview', 'preview')->name('preview');
                            Route::get('/product/{product}/options', 'productOptions')->name('product.options');
                            Route::get('/{categorySlug?}', 'index')->name('category');
                            Route::post('/session/start', 'startSession')->name('session.start');
                            Route::post('/session/close', 'closeSession')->name('session.close');
                            Route::get('/api/session-summary', 'getSessionSummary')->name('api.session-summary');
                            Route::post('/cart/create-order', 'createOrder')->name('cart.create-order');
                            Route::put('/update-bill-no/{branchId}', 'updateBillNo')->name('update-bill-no');
                        });

                    Route::controller(CartController::class)
                        ->group(function () {
                            Route::post('/cart/add', 'addToCart')->name('cart.add');
                            Route::put('/cart/{cartId}', 'updateCart')->name('cart.update');
                            Route::post('/cart/merge', 'mergeCart')->name('cart.merge');
                            Route::post('/cart/place-order', 'placeOrder')->name('cart.place-order');
                            Route::post('/cart/settle-bill/{cartId}', 'settleBill')->name('cart.settle-bill');
                            Route::post('/cart/claim-order/{tableId}', 'claimOrder')->name('cart.claim-order');
                            Route::put('/cart/update-bill-number/{cartId}', 'updateBillNumber')->name('cart.update-bill-number');
                            Route::post('/order/transfer/{tableId}', 'transferOrder')->name('order.transfer');
                            Route::put('/cart/item/discount/', 'applyDiscountToCartItem')->name('cart.apply-discount');
                            Route::put('/cart/item/modifier/', 'applyModifierToCartItem')->name('cart.apply-modifier');
                            Route::put('/cart/item/{cartItemId}', 'updateCartItem')->name('cart.update-item');
                            Route::put('/cart/item/clear-discount/{cartItemId}', 'clearDiscountToCartItem')->name('cart.clear-discount');
                            Route::put('/cart/item/void/{cartItemId}', 'voidCartItem')->name('cart.void-cart');
                            Route::delete('/cart/item/{cartItemId}', 'deleteCartItem')->name('cart.delete');
                        });
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

            Route::as('table-rooms.')
                ->prefix('/table-rooms')
                ->controller(TableRoomController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/list', 'list')->name('list');
                    Route::post('/tables', 'store')->name('store');
                    Route::post('/tables/bulk-update-positions', 'bulkUpdatePositions')->name('bulk-update-positions');
                    Route::post('/tables/reserve', 'reserveTable')->name('reserve');
                    Route::post('/tables/{tableId}/vacant', 'vacantTable')->name('vacant');
                    Route::put('/tables/{tableId}', 'update')->name('update');
                    Route::put('/tables/{tableId}/unmerge', 'unmergeTable')->name('unmerge');
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
                    Route::get('/', 'index')->name('index');
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
        });

        // // Web Receipt Route (for browser viewing)
        // Route::get('/receipt/{receiptNumber}', function($receiptNumber) {
        //     return Inertia::render('Receipt', [
        //         'receiptNumber' => $receiptNumber,
        //     ]);
        // })->name('receipt.view');
});



