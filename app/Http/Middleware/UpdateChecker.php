<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateChecker
{
    public function handle(Request $request, Closure $next)
    {
        // License / update enforcement removed for HCTestDash (onboarding challenge build).
        return $next($request);
    }
}
