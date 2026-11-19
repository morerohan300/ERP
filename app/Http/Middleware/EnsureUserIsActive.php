<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out users whose account has been deactivated by an administrator.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            if ($request->is('api/*')) {
                $user->currentAccessToken()?->delete();
                abort(403, 'Your account has been deactivated.');
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => __('Your account has been deactivated.')]);
        }

        return $next($request);
    }
}
