<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // License enforcement removed for HCTestDash (onboarding challenge build).

        $reauthenticatedAt = session('reauthenticated_at');
        $expired = $reauthenticatedAt === null || (time() - $reauthenticatedAt > 3600);

        if (!session('reauthenticated') || $expired) {
            session()->forget(['reauthenticated', 'reauthenticated_at']);

            return redirect()->route('reauthenticate', ['redirect' => $request->getPathInfo()])->with('title', 'Please reauthenticate');
        }

        return $next($request);
    }
}
