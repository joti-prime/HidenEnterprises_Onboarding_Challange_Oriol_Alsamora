<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceTwoFa
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // check if forced 2fa is enabled
        if (settings('force_staff_2fa', false)) {
            if (auth()->user() and auth()->user()->isAdmin()) {
                // check if the user has 2fa enabled
                if (!auth()->user()->has2FA()) {
                    return redirect()->route('2fa.setup')->withError(__('admin.2fa_setup_required'));
                }
            }
        }

        return $next($request);
    }
}
