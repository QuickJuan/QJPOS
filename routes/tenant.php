<?php

declare (strict_types = 1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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

    Route::middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('/dashboard', function () {
                return Inertia::render('Dashboard', [
                    'tenant' => tenant(),
                ]);
            })->name('dashboard');

            // Route::get('/user/profile', function () {
            //     return Inertia::render('Profile/Show', [
            //         'user' => auth()->user(),
            //         'sessions' => auth()->user()->sessions,
            //         'tenants' => auth()->user()->tenants,
            //     ]);
            // })->name('profile.show');

        });

    Route::controller(AuthController::class)
        ->group(function () {
            Route::get('/login', 'index')->middleware('guest')->name('login');
            Route::post('/login', 'login')->middleware('guest')->name('login.post');
            Route::post('/logout', 'logout')->middleware('auth')->name('logout');
            Route::get('/branches/validate/{id}', 'checkBranch')->middleware('guest')->name('branches.validate');
        });

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');

    //public landing page
    Route::get('/', function () {
        return Inertia::render('Landing', [
            'tenant' => tenant(),
        ]);
    })->name('landing');

    Route::get('/retail-cashier', function () {
        return Inertia::render('RetailCashier/Index');
    })->name('test');
});
