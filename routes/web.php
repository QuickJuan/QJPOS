<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\BlockTenantAccessToCentral;
use Stancl\Tenancy\Middleware\PreventAccessFromTenantDomains;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\TenantController;

foreach (config('tenancy.central_domains') as $domain) {
    if (empty($domain)) continue; // Skip empty domains

    Route::domain($domain)->middleware(['web', BlockTenantAccessToCentral::class])->group(function () use ($domain) {
        // Central home route - redirect authenticated users to central, others to landing
        Route::get('/', function () {
            if (auth()->check()) {
                return redirect('/central');
            }
            return Inertia::render('Landing', [
                'table' => 'Welcome to the Central Domain',
            ]);
        })->name('home');

        // Tenant Registration Routes
        Route::get('/tenant/register', [TenantRegistrationController::class, 'show'])
            ->name('tenant-registration');
        Route::post('/tenant/register', [TenantRegistrationController::class, 'store'])
            ->name('tenant-registration-store');
        Route::get('/tenant/registration-success', [TenantRegistrationController::class, 'success'])
            ->name('tenant-registration-success');

        // Route::get('/login', function () {
        //         return redirect("/central");
        //     })->name("central.login.{$domain}");

        // Route::get('/dashboard', function () {
        //     return "central dashboard";
        // })->name("central.dashboard.{$domain}");

                // Optional: redirect /admin to login if not using Filament
        // Route::get('/admin', function () {
        //     return redirect("/central");
        // })->name("admin.login.{$domain}");

        // Tenant management routes (simple non-Filament UI)
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    });
}
