<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaForStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasAnyRole(['admin', 'staff']) && ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.setup')
                ->with('status', 'Two-factor authentication is required for staff accounts.');
        }

        return $next($request);
    }
}