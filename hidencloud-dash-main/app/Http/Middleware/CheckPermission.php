<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $routeName = '')
    {
        if (empty($routeName)) {
            $routeName = Route::currentRouteName();
        }

        if (Auth::check() and Auth::user()->hasPerm($routeName)) {
            return $next($request);
        }

        abort(403, __('responses.no_permission'));
    }
}
