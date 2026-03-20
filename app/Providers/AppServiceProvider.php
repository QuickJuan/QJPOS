<?php

namespace App\Providers;

use App\Models\User;
use App\Models\CartItem;
use App\Models\LeaveRequest;
use App\Observers\CartItemObserver;
use App\Observers\LeaveRequestObserver;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Disable Spatie Permission completely in central domain
        if (!app()->runningInConsole()) {
            $this->app->booted(function () {
                if (!tenancy()->initialized) {
                    // Clear permission cache and disable
                    app(PermissionRegistrar::class)->forgetCachedPermissions();
                    Config::set('permission.register_permission_check_method', false);
                    Config::set('permission.teams', false);
                }
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        CartItem::observe(CartItemObserver::class);
        LeaveRequest::observe(LeaveRequestObserver::class);

        // Sanctum stateful domains configuration
        if (app()->environment('local', 'development', 'production')) {
            $host = request()->getHost();
            $stateful = Config::get('sanctum.stateful', []);
            if (is_string($stateful)) {
                $stateful = explode(',', $stateful);
            }
            $stateful[] = $host;
            $stateful[] = ltrim($host, '.');
            Config::set('sanctum.stateful', array_unique($stateful));
        }
    }
}
