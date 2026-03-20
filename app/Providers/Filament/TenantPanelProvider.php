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
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Http\Middleware\ConditionalInitializeTenancyByDomain;
use App\Http\Middleware\ConditionalPreventAccessFromCentralDomains;

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
                \App\Filament\Tenant\Widgets\LowStockWidget::class,
                \App\Filament\Tenant\Widgets\EventCalendarWidget::class,
            ])
            ->plugins([
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingQueue('backups')
                    ->usingPolingInterval('30s')
                    ->usingPage(\App\Filament\Tenant\Pages\TenantBackups::class),
                FilamentSpatieRolesPermissionsPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable(),
            ])
            ->databaseNotifications()
            ->middleware($this->registerMiddlewares())
            ->login()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'Customer Management',
                'Store',
                'Order',
                'Sales Report',
                'Events & Reservations',
                'Products',
                'Inventory',
                'Table / Rooms',
                'Finance',
                'Payroll',
                'Page Builder',
                'Roles and Permissions',
                'Settings',
                'Management',
                'System',
            ]);
    }

    // Register middlewares - use conditional middleware that checks at request time
    private function registerMiddlewares(): array
    {
        return [
            // CRITICAL: Initialize tenancy FIRST, before authentication
            // This ensures User model queries use tenant database
            ConditionalInitializeTenancyByDomain::class,
            ConditionalPreventAccessFromCentralDomains::class,

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
    }

}
