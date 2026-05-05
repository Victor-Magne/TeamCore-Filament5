<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Setup2fa extends Page
{
    protected string $view = 'filament.pages.setup2fa';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('Configuração de Autenticação de Dois Factores (2FA)');
    }

    public function mount(): void
    {
        $user = auth()->user();

        if (!$user->two_factor_enabled || $user->hasEnabledTwoFactor()) {
            redirect()->to(filament()->getHomeUrl());
        }
    }
}
