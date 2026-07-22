<?php

namespace App\Http\Middleware;

use App\Models\UserIp;
use Closure;
use Illuminate\Http\Request;

class LogUserIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and not being impersonated
        if (auth()->check() && !session()->has('is_impersonating')) {
            $user = auth()->user();
            $ipAddress = $request->getClientIp();

            // Check if the IP address has already been logged for the user
            $userIp = UserIp::where('user_id', $user->id)
                ->where('ip_address', $ipAddress)
                ->first();

            // If the IP address is not logged, create a new record
            if (!$userIp) {
                UserIp::create([
                    'user_id' => $user->id,
                    'ip_address' => $ipAddress,
                ]);
            } else {
                $userIp->increment('uses');
            }
        }

        return $next($request);
    }
}
