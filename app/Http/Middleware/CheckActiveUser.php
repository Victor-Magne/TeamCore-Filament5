<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginUrl = Filament::getCurrentPanel()?->getLoginUrl() ?? route('filament.admin.auth.login');

            return redirect($loginUrl)
                ->withErrors(['email' => 'A sua conta está desativada. Contacte o administrador.']);
        }

        return $next($request);
    }
}
