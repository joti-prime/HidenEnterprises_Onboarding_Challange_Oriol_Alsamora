<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAddress
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (settings('require_address', false)) {
            if (auth()->user() and !auth()->user()->address->hasCompletedAddress()) {
                return redirect()->route('auth.address');
            }
        }

        return $next($request);
    }
}
