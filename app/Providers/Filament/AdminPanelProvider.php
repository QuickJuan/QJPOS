<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\BlockTenantAccessToCentral;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        // // Get the domain from the request
        // $host = request()->getHost();

        // // Check if the domain contains a subdomain (tenant)
        // $isSubdomain = strtolower($host) !== config('app.main_domain');

        // // Initialize tenancy only for subdomains (tenant routes)
        // if ($isSubdomain) {
        //     app(InitializeTenancyByDomain::class)->handle(request(), function () {
        //         // No-op — just forces tenancy initialization for tenant routes
        //     });

        //     // Optionally prevent access from central domains for tenant routes
        //     app(PreventAccessFromCentralDomains::class)->handle(request(), function () {
        //         // No-op — just prevents access from central domain for tenant routes
        //     });
        // }

        
        return $panel
            ->default()
            ->id('central')
            ->path('central')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                BlockTenantAccessToCentral::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
