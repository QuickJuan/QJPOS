<?php

declare (strict_types = 1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
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
            Route::get('/home', [HomeController::class, 'index'])->name('home');

            Route::get('/dashboard', function () {
                return Inertia::render('Dashboard', [
                    'tenant' => tenant(),
                ]);
            })->name('dashboard');

            // Attendance routes
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::post('/attendance/check-status', [AttendanceController::class, 'checkStatus'])->name('attendance.check-status');
            Route::post('/attendance/toggle', [AttendanceController::class, 'toggle'])->name('attendance.toggle');
            Route::get('/attendance/today', [AttendanceController::class, 'today'])->name('attendance.today');
            Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

        });

    Route::controller(AuthController::class)
        ->group(function () {
            Route::get('/login', 'index')->middleware('guest')->name('tenant.login');
            Route::post('/login', 'login')->middleware('guest')->name('tenant.login.post');
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
