<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\EmployeeDashboard;
use App\Filament\Pages\AttendanceCheckIn;
use App\Http\Middleware\CheckAppPanelAccess;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->databaseNotifications()
            ->unsavedChangesAlerts()
            ->spa()
            ->colors([
            50 => '#fef7ed',
            100 => '#fdeed5',
            200 => '#fbdcaa',
            300 => '#f6c376',
            400 => '#f0a247',
            500 => '#e67f1a', // Your main brand color
            600 => '#d06513',
            700 => '#a84c11',
            800 => '#853e13',
            900 => '#6d3514',
            950 => '#3e1b07',
            ])
            ->brandLogo(asset('images/Teamcorelogo.svg'))
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->enableTwoFactorAuthentication(),
                FilamentUnsavedChangesModalPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                EmployeeDashboard::class,
                AttendanceCheckIn::class,
            ])
            ->widgets([
                // Widgets are registered in the Dashboard class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckAppPanelAccess::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
