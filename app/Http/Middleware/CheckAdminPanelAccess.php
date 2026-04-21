<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPanelAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->cannot('Access:AdminPanel')) {
            abort(403, 'Unauthorized access to Admin Panel');
        }

        return $next($request);
    }
}
