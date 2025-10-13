<?php

declare (strict_types = 1);

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierSessionController;
use App\Http\Controllers\HomeController;
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

            Route::get('/dashboard', function () {
                return Inertia::render('Dashboard', [
                    'tenant' => tenant(),
                ]);
            })->name('dashboard');

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

            // ROUTE FOR RETAIL CASHIER
            Route::as('retail-cashier.')
                ->prefix('/retail-cashier')
                ->controller(CashierSessionController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                });
        });
});
