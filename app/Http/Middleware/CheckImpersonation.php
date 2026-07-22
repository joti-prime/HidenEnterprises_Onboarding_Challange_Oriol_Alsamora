<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        if (session('impersonate') && !auth()->check()) {
            // Mark as impersonating to prevent login events
            $request->session()->put('is_impersonating', true);
            auth()->loginUsingId(session('impersonate'), true);
        } elseif (session('impersonate') && auth()->id() !== session('impersonate')) {
            // Mark as impersonating to prevent login events
            $request->session()->put('is_impersonating', true);
            auth()->loginUsingId(session('impersonate'), true);
        }

        return $next($request);
    }
}
