<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckAdminPanelAccess;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
// Plugins
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
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
                // Primary/Brand colors: warm earth tones (WCAG AA compliant on light backgrounds)
                'primary' => '#582f0e',      // Rich brown - main CTA, active states
                'secondary' => '#7f4f24',    // Golden brown - secondary CTAs, accents

                // Semantic colors: optimized for accessibility
                'success' => '#2d5016',      // Deep green - improved contrast
                'warning' => '#b45309',      // Amber/orange - pending states, important alerts
                'danger' => '#7f1d1d',       // Deep red - errors, rejections
                'info' => '#936639',         // Warm tan - informational content

                // Neutral grayscale: improved contrast and visual hierarchy
                'gray' => '#4b5563',         // Neutral dark gray
                'muted' => '#6b7280',        // Medium gray - distinct from gray
                'accent' => '#414833',       // Very dark olive - dark accents
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
                    ->enableTwoFactorAuthentication(),
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
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
