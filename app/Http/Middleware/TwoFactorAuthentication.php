<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // check if user has 2fa enabled
            if ($user->TwoFa()->exists()) {
                // Check if 2FA has been validated for this specific session
                $twoFactorValidatedAt = session('2fa_validated_at');

                // If not validated in this session or validation expired (24 hours)
                if (!$twoFactorValidatedAt || Carbon::parse($twoFactorValidatedAt)->addHours(24)->lessThan(Carbon::now())) {
                    session()->put('url.intended', $request->fullUrl()); return redirect()->route('2fa.validate');
                }
            }

        }

        return $next($request);
    }
}
