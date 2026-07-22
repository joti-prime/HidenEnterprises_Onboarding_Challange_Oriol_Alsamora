<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRevokedDevices
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && $request->hasHeader('user-agent')) {

            // Skip device tracking during impersonation
            if (!session()->has('is_impersonating')) {
                // add the device
                Device::addDevice(request(), Auth::user());

                // check if device is revoked
                $device = Auth::user()->devices->where('user_agent', $request->header('user-agent'))->where('is_revoked', true)->first();
                if ($device !== null) {
                    return redirect()->route('client.reauthenticate', ['device' => $device->id]);
                }
            }
        }

        return $next($request);
    }
}
