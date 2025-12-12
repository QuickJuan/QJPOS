<?php

namespace App\Providers;

use App\Models\User;
use App\Models\CartItem;
use App\Observers\CartItemObserver;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        CartItem::observe(CartItemObserver::class);

        // Dynamically add current host to sanctum.stateful for dynamic tenants
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
