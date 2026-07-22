<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilterHetrixBots
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->userAgent() ?? '';
        
        // Check if the user agent is from HetrixTools
        if (str_contains(strtolower($userAgent), 'hetrixtools')) {
            // Set session driver to array for this request (in-memory only)
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }
}