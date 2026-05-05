<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class CheckTwoFactorEnforced
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $panel = Filament::getCurrentPanel();

        if (!$user || !$panel) {
            return $next($request);
        }

        // Se o utilizador está a mudar a password obrigatória, essa tem prioridade
        if ($user->must_change_password) {
            return $next($request);
        }

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $setup2faRoute = "filament.{$panel->getId()}.pages.setup2fa";
        $logoutRoute = "filament.{$panel->getId()}.auth.logout";

        if ($currentRoute === $setup2faRoute || $currentRoute === $logoutRoute) {
            return $next($request);
        }

        // Se o 2FA é obrigatório para este utilizador mas ele ainda não o configurou
        if ($user->two_factor_enabled && !$user->hasEnabledTwoFactor()) {
            return redirect()->route($setup2faRoute);
        }

        return $next($request);
    }
}
