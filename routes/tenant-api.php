<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReceiptController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
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
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])
->prefix('api')
->as('api.')
->group(function () {

    // Receipt API Routes
    Route::as('receipts.')
        ->prefix('/receipts')
        ->controller(ReceiptController::class)
        ->group(function () {
            Route::get('/{receiptNumber}', 'getReceipt')->name('get');
            Route::get('/{receiptNumber}/items', 'getReceiptItems')->name('items');
            Route::get('/{receiptNumber}/download', 'downloadReceipt')->name('download');
        });

    // You can add more tenant-specific API routes here
    // Route::apiResource('products', ProductController::class);
    // Route::apiResource('categories', CategoryController::class);
});
