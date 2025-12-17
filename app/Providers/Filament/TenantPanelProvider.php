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
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('admin')

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\\Filament\\Tenant\\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\\Filament\\Tenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\\Filament\\Tenant\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingQueue('backups') // Optional: use queue for backups
                    ->usingPolingInterval('30s'), // Optional: polling interval
            ])
            ->middleware($this->registerMiddlewares())
            ->login()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'Store',
                'Products',
                'Table / Rooms',
                'System'
                // Add more group names as needed
            ]);
    }

    // Register middlewares depending when the domain is central or tenant
    private function registerMiddlewares(): array
    {
        $baseMiddlewares = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];

        // Only apply tenant middleware on tenant domains
        if (!isCentralDomain()) {
            return array_merge([
                PreventAccessFromCentralDomains::class,
                InitializeTenancyBySubdomain::class,
            ], $baseMiddlewares);
        }

        // On central domain, don't apply tenant middleware
        return $baseMiddlewares;
    }

}
