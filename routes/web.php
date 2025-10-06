<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\BlockTenantAccessToCentral;
use Stancl\Tenancy\Middleware\PreventAccessFromTenantDomains;

// Register routes for all central domains
Route::domain('{domain}')
    ->where('domain', implode('|', config('tenancy.central_domains')))
    ->middleware(['web', BlockTenantAccessToCentral::class])
    ->group(function () {
        // Central home route
        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'table' => 'Welcome to the Central Domain',
            ]);
        })->name('central.home');

        // Route::get('/login', function () {
        //         return redirect("/central");
        //     })->name('central.login');

        // Route::get('/dashboard', function () {
        //     return "central dashboard";
        // })->name('central.dashboard');

        // Optional: redirect /admin to login if not using Filament
        // Route::get('/admin', function () {
        //     return redirect("/central");
        // })->name('admin.login');
    });
