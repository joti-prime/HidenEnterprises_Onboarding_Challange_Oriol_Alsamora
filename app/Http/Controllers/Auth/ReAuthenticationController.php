<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReAuthenticationController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->is_admin()) {
            return redirect()->route('dashboard')->with('error',
                trans('auth.reauth_perms_error',
                    ['default' => 'You don\'t have permissions to access this resource'])
            );
        }

        $reauthenticatedAt = session('reauthenticated_at');
        $expired = $reauthenticatedAt === null || (time() - $reauthenticatedAt > 3600);

        if (session('reauthenticated') && !$expired) {
            return redirect($request->input('redirect'))->with('warning',
                trans('auth.reauth_double_warning',
                    ['default' => 'You have already reauthenticated'])
            );
        }

        return Theme::view('reauthenticate', ['is_admin' => true, 'redirect' => $request->input('redirect')]);
    }

    public function reauthenticate(Request $request)
    {
        $credentials = $request->validate([
            'password' => ['required', 'string'],
            'OPT' => auth()->user()->TwoFa()->exists() ? 'required|numeric|digits:6' : 'nullable',
        ]);

        if (auth()->user()->TwoFa()->exists() and !auth()->user()->twoFA->validate($request->input('OPT'))) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        if (Auth::attempt(['email' => Auth::user()->email, 'password' => $credentials['password']])) {
            session([
                'reauthenticated' => true,
                'reauthenticated_at' => time(),
            ]);

            return redirect()->intended($request->input('redirect'));
        }

        return redirect()->route('reauthenticate', ['redirect' => $request->input('redirect')])->with('error',
            trans('auth.reauth_password_error',
                ['default' => 'Invalid password'])
        );

    }

    public function client(Device $device)
    {
        if ($device->user_id !== Auth::user()->id && !Auth::user()->is_admin()) {
            return redirect()->back()->with('error',
                trans('auth.reauth_perms_error',
                    ['default' => 'You don\'t have permissions to access this resource'])
            );

        }

        if (!$device->is_revoked) {
            return redirect('/')->with('error',
                trans('auth.reauth_unknown_error',
                    ['default' => 'Something went wrong'])
            );
        }

        return Theme::view('reauthenticate', ['is_admin' => false, 'device' => $device]);
    }

    public function clientPost(Request $request, Device $device)
    {
        if ($device->user_id !== Auth::user()->id && !Auth::user()->is_admin()) {
            return redirect()->back()->with('error',
                trans('auth.reauth_perms_error',
                    ['default' => 'You don\'t have permissions to access this resource'])
            );
        }

        $credentials = $request->validate([
            'password' => ['required', 'string'],
            'OPT' => auth()->user()->TwoFa()->exists() ? 'required|numeric|digits:6' : 'nullable',
        ]);

        if (auth()->user()->TwoFa()->exists() and !auth()->user()->twoFA->validate($request->input('OPT'))) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        if (Auth::attempt(['email' => Auth::user()->email, 'password' => $credentials['password']])) {
            $device->revoke();

            return redirect('/')->with('success',
                trans('auth.reauth_unlocked_success',
                    ['default' => 'Account has been unlocked'])
            );
        }

        return redirect()->back()->with('error',
            trans('auth.reauth_data_error',
                ['default' => 'Provided credentials do not exists within our record'])
        );

    }
}
