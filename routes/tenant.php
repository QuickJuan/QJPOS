<?php

declare(strict_types=1);

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

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

        Route::get('/login', [AuthController::class, 'index'])
            ->middleware('guest')
            ->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('guest')
            ->name('login.post');
        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth')
            ->name('logout');

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
});
