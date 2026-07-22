<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackAffiliate
{
    /**
     * Handle an incoming request.
     *
     * The Affiliates module was removed from HCTestDash (onboarding challenge build),
     * so affiliate-invite tracking is a no-op.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
