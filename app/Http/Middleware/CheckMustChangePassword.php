<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class CheckMustChangePassword
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $panel = Filament::getCurrentPanel();

        // Se não houver utilizador ou painel, segue normalmente
        if (!$user || !$panel) {
            return $next($request);
        }

        // Se o utilizador já está na página de trocar password ou a fazer logout, permite
        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $forceChangePasswordRoute = "filament.{$panel->getId()}.pages.force-change-password";
        $logoutRoute = "filament.{$panel->getId()}.auth.logout";

        if ($currentRoute === $forceChangePasswordRoute || $currentRoute === $logoutRoute) {
            return $next($request);
        }

        // Se o utilizador tem de trocar a password, redireciona
        if ($user->must_change_password) {
            return redirect()->route($forceChangePasswordRoute);
        }

        return $next($request);
    }
}
