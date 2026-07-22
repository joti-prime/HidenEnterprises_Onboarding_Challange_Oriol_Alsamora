<?php

namespace App\Http\Middleware;

use App\Models\Punishment;
use Closure;
use Illuminate\Http\Request;

class Punishments
{
    public function handle(Request $request, Closure $next)
    {
        // Don't redirect if already on the suspended page
        if ($request->routeIs('suspended')) {
            return $next($request);
        }

        // Allow exit from impersonation
        if ($request->routeIs('admin.user.impersonate.exit')) {
            return $next($request);
        }

        // Allow access to the appeal form
        if ($request->routeIs('forms.view') && $request->route('form') && $request->route('form')->slug === 'appeal-suspended-by-system') {
            return $next($request);
        }

        // Allow access to form submissions
        if ($request->routeIs('forms.view-submission')) {
            return $next($request);
        }

        // Also allow if the path matches the appeal form URL or submissions
        if ($request->is('forms/appeal-suspended-by-system') ||
            $request->is('forms/appeal-suspended-by-system/*') ||
            $request->is('forms/submissions/*')) {
            return $next($request);
        }

        if (Punishment::hasActiveBans()) {
            return redirect()->route('suspended');
        }

        // Check if the authenticated user has status 'suspended'
        $user = auth()->user();
        if ($user && $user->status === 'suspended') {
            return redirect()->route('suspended');
        }

        return $next($request);
    }
}
