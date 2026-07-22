<?php

namespace App\Http\Middleware;

use App\Models\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Maintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            // allow admins to bypass restrictions
            if (Auth::user()->is_admin()) {
                return $next($request);
            }

            // check if email verification is enabled, if so force the user to verify
            if (Settings::get('registration_activation', '2') == '2') {
                if (!Auth::user()->hasVerifiedEmail()) {
                    return redirect()->route('verification');
                }
            }

            // Check if the user is authenticated
            if (Auth::user()->status === 'pending') {
                return redirect()->route('restricted.activation');
            }

            // Check if maintenance mode is enabled
            if (Settings::get('maintenance', 'false') == 'true') {
                return redirect()->route('restricted.maintenance');
            }

        }

        return $next($request);
    }
}
