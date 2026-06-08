<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckActiveUser;
use App\Http\Middleware\CheckAdminPanelAccess;
use App\Http\Middleware\CheckMustChangePassword;
use App\Http\Middleware\CheckTwoFactorEnforced;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
// Plugins
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => Blade::render('<x-pwa.meta />'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render('<x-pwa.login-prompt />'),
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin') // URL base: localhost:8000/admin
            ->login()       // Ativa o formulário de login padrão
            ->databaseNotifications() // Ativa notificações persistentes no banco de dados
            ->unsavedChangesAlerts() // Alertas de mudanças não salvas
            ->spa() // Ativa o modo SPA para navegação sem recarregamento
            ->colors([
                'primary' => [
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
                ],
                // Repeat the array structure for 'secondary', 'success', etc.
            ])
            ->brandLogo(asset('images/Teamcorelogo.svg')) // Logo personalizada
            // Registo de Plugins
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->withoutMyProfileComponents(['update_password', 'two_factor_authentication']),
                FilamentShieldPlugin::make(),
                FilamentUnsavedChangesModalPlugin::make(),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
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
                CheckAdminPanelAccess::class,
                CheckMustChangePassword::class,
                CheckTwoFactorEnforced::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckActiveUser::class,
            ]);
    }
}
