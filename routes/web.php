<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\BlockTenantAccessToCentral;
use Stancl\Tenancy\Middleware\PreventAccessFromTenantDomains;

foreach (config('tenancy.central_domains') as $domain) {
    if (empty($domain)) continue; // Skip empty domains

    Route::domain($domain)->middleware(['web', BlockTenantAccessToCentral::class])->group(function () use ($domain) {
        // Central home route
        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'table' => 'Welcome to the Central Domain',
            ]);
        })->name("central.home.{$domain}"); // Make route names unique per domain

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
    });
}
