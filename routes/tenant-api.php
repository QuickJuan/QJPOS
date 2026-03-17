<?php

declare (strict_types = 1);

use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group and tenant middleware.
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])
    ->prefix('api')
    ->as('api.')
    ->group(function () {

        // Cart API Routes
        Route::as('carts.')
            ->prefix('/carts')
            ->controller(CartController::class)
            ->group(function () {
                Route::get('/{cartId}/print-bill', 'printBill')->name('print-bill');
            });

        // Users API Routes
        Route::prefix('/users')
            ->controller(CartController::class)
            ->group(function () {
                Route::get('/servers', 'getServers');
            });

        // Receipt API Routes
        Route::as('receipts.')
            ->prefix('/receipts')
            ->controller(ReceiptController::class)
            ->group(function () {
                Route::get('/{receiptNumber}/items', 'getReceiptItems')->name('items');
                Route::get('/{receiptNumber}/download', 'downloadReceipt')->name('download');
                Route::get('/{receiptNumber}/{cashierSessionId}', 'getReceipt')->name('get');
            });

        Route::as('tables.')
            ->prefix('/tables')
            ->controller(TableController::class)
            ->group(function () {
                Route::get('/', 'list')->name('list');
            });

        // Branches API Routes
        Route::get('/branches', function () {
            return response()->json([
                'data' => \App\Models\Branch::select('id', 'name', 'branch_code')->get(),
            ]);
        });

        // Get users for a specific branch
        Route::get('/branches/{branch}/users', function (\App\Models\Branch $branch) {
            $users = $branch->users()
                ->select('users.id', 'users.name', 'users.email')
                ->whereHas('roles', function ($query) {
                    $query->whereRaw('LOWER(name) IN (?, ?)', ['waiter', 'server']);
                })
                ->orderBy('users.name')
                ->get();

            return response()->json([
                'data' => $users,
            ]);
        });

        // You can add more tenant-specific API routes here
        // Route::apiResource('products', ProductController::class);
        // Route::apiResource('categories', CategoryController::class);
    });
