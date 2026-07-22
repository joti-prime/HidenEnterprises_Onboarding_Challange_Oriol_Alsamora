<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Captcha;
use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\PasswordReset;
use App\Models\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        Captcha::setConfig();
    }

    /**
     * @return Factory|View|Application
     */
    public function login()
    {
        return Theme::view('auth.login');
    }

    /**
     * @return Redirector|RedirectResponse|Application
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|min:2|max:191',
            'password' => 'required|min:4|max:255',
            'cf-turnstile-response' => Captcha::CloudFlareRules('page_login'),
        ]);

        $username_password = ['username' => $request->input('username'), 'password' => $request->input('password')];
        $email_password = ['email' => $request->input('username'), 'password' => $request->input('password')];

        if (Auth::attempt($username_password, true) or Auth::attempt($email_password, true)) {

            // Only trigger login events if this is not an impersonation
            if (!session()->has('is_impersonating')) {
                Auth::user()->loggedIn();
            }

            // Clear any previous 2FA validation from this session on new login
            session()->forget('2fa_validated_at');

            return redirect()->intended('/dashboard')->with('success',
                trans('auth.authenticate_welcome', ['name' => Auth::user()->username, 'default' => 'Welcome back, :name'])
            );
        }

        return redirect(route('login'))->with('error',
            trans('auth.authenticate_error', ['default' => 'Login details are not valid'])
        );
    }

    // invalidate session & log the user out

    /**
     * @return Redirector|Application|RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::user()->loggedOut();

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * @return Factory|View|Application
     */
    public function address()
    {
        // if the user has an address, redirect them to the dashboard
        if (Auth::user()->address->hasCompletedAddress()) {
            return redirect()->route('dashboard');
        }

        return Theme::view('auth.registration.address');
    }

    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function register()
    {
        if (Settings::get('registrations', 'true') == 'false') {
            return redirect()->back()->with('error',
                trans('auth.registration_disable', ['default' => 'Registrations have been disabled'])
            );
        }

        return Theme::view('auth.register');
    }

    /**
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if (Settings::get('registrations', 'true') == 'false') {
            return redirect()->back()->with('error',
                trans('auth.registration_disable', ['default' => 'Registrations have been disabled'])
            );
        }

        $request->validate([
            'first_name' => 'required|min:2|max:255',
            'last_name' => 'required|min:2|max:255',
            'username' => 'required|string|unique:users,username|min:2|max:191|regex:/^[A-Za-z0-9_]+$/',
            'email' => [
                'required', 'email', 'max:255',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    // Block aliases of an already-registered email (gmail dots, +tag, etc).
                    $normalized = User::normalizeEmail($value);
                    if (User::where('email_normalized', $normalized)->exists()) {
                        $fail(__('auth.email_alias_already_taken', [
                            'default' => 'This email address is already in use (or an alias of an existing account).',
                        ]));
                    }
                },
            ],
            'company_name' => 'max:255',
            'address' => 'max:255',
            'address_2' => 'max:255',
            'country' => 'max:255',
            'city' => 'max:255',
            'region' => 'max:255',
            'zip_code' => 'max:255',
            'password' => 'required|confirmed|min:8|max:255',
            'cf-turnstile-response' => Captcha::CloudFlareRules('page_register'),
        ]);

        // create the user and save them
        $user = new User();
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->email = $request->input('email');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->status = (Settings::get('registration_activation', '1') == '3') ? 'pending' : 'active';
        $user->last_login_at = Carbon::now();
        $user->save();

        $address = new Address();
        $address->user_id = $user->id;
        $address->company_name = $request->input('company_name');
        $address->address = $request->input('address');
        $address->address_2 = $request->input('address_2');
        $address->country = $request->header('cf-ipcountry', $request->input('country'));
        $address->city = $request->input('city');
        $address->region = $request->input('region');
        $address->zip_code = $request->input('zip_code');
        $address->save();

        Auth::login($user, true);

        // Clear any previous 2FA validation from this session on registration
        session()->forget('2fa_validated_at');

        return redirect()->route('dashboard')->with('success',
            trans('auth.registration_success', ['default' => 'Logged in successfully'])
        );
    }

    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function verification()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->back()->with('success',
                trans('auth.has_verified_email', ['default' => 'You have already verified your email.'])
            );
        }
        self::emailVerificationCode();

        return Theme::view('auth.registration.verify_email');
    }

    /**
     * @return RedirectResponse
     */
    public function validateVerification(Request $request)
    {
        $request->validate([
            'first_digit' => 'required|integer|max:9',
            'second_digit' => 'required|integer|max:9',
            'third_digit' => 'required|integer|max:9',
            'fourth_digit' => 'required|integer|max:9',
            'fifth_digit' => 'required|integer|max:9',
            'sixth_digit' => 'required|integer|max:9',
        ]);

        $code = $this->combine(['first_digit', 'second_digit', 'third_digit', 'fourth_digit', 'fifth_digit', 'sixth_digit']);

        $user = Auth::user();
        $expired = $user->verification_code_expires_at === null
            || $user->verification_code_expires_at->isPast();

        if (!$expired && $user->verification_code == $code) {
            $user->markEmailAsVerified();
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();

            return redirect()->route('dashboard')->with('success',
                trans('auth.validate_verification_success', ['default' => 'Your email has been verified.'])
            );
        }

        if ($expired) {
            return redirect()->back()->with('error',
                trans('auth.validate_verification_expired', ['default' => 'The code has expired. Request a new one.'])
            );
        }

        return redirect()->back()->with('error',
            trans('auth.validate_verification_error', ['default' => 'The code provided does not match our records.'])
        );
    }

    public function emailVerificationCode(): void
    {
        $code = Auth::user()->generateVerificationCode();

        if (Auth::user()->emails()->where('subject', "Verification Code [$code]")->exists()) {
            return;
        }

        app()->setLocale(Auth::user()->language ?? settings('language', 'en'));
        Auth::user()->email([
            'subject' => trans('auth.verification_code_subject', ['code' => $code, 'default' => 'Verification Code [:code]']),
            'content' => emailMessage('verification', app()->getLocale()) . '<br><br>' . __('admin.email_code') . "<br><br><code style='font-size: 50px;letter-spacing: 3.5rem;font-weight: 800;'>" . $code . '</code>',
            'button' => [
                'name' => __('admin.verify'),
                'url' => route('verification', ['code' => $code]),
            ],
        ]);

    }

    /**
     * @return Factory|View|Application
     */
    public function forgotPassword()
    {
        return Theme::view('auth.forgot-password');
    }

    /**
     * @return RedirectResponse
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        if (User::whereEmail($validated['email'])->exists()) {
            User::whereEmail($validated['email'])->first()->sendPasswordResetEmail();
        }

        return redirect()->back()->with('success',
            trans('auth.reset_password_send_success', ['default' => 'We have emailed you a password reset link.'])
        );
    }

    /**
     * @return Factory|View|Application
     */
    public function resetPassword($token)
    {
        $passwordReset = PasswordReset::whereToken($token)->first();
        
        if (!$passwordReset) {
            return redirect()->route('forgot-password')->with('error',
                trans('auth.invalid_reset_token', ['default' => 'This password reset link is invalid or has expired.'])
            );
        }
        
        // Check if token has expired (config value drives the TTL)
        if ($passwordReset->created_at->addMinutes(config('auth.passwords.users.expire', 30))->isPast()) {
            $passwordReset->delete();
            return redirect()->route('forgot-password')->with('error',
                trans('auth.reset_token_expired', ['default' => 'This password reset link has expired.'])
            );
        }
        
        // Check if user still exists with this email
        if (!User::whereEmail($passwordReset->email)->exists()) {
            $passwordReset->delete();
            return redirect()->route('forgot-password')->with('error',
                trans('auth.reset_token_invalid', ['default' => 'This password reset link is no longer valid.'])
            );
        }

        return Theme::view('auth.reset-password', ['password_reset' => $passwordReset]);
    }

    /**
     * @return RedirectResponse
     */
    public function resetPasswordUpdate(Request $request, $token)
    {
        $passwordReset = PasswordReset::whereToken($token)->first();
        
        if (!$passwordReset) {
            return redirect()->route('forgot-password')->with('error',
                trans('auth.invalid_reset_token', ['default' => 'This password reset link is invalid or has expired.'])
            );
        }

        $request->validate([
            'password' => 'required|confirmed|min:4|max:255',
        ]);
        
        // Check if token has expired
        if ($passwordReset->created_at->addMinutes(config('auth.passwords.users.expire', 30))->isPast()) {
            $passwordReset->delete();
            return redirect()->route('forgot-password')->with('error',
                trans('auth.reset_token_expired', ['default' => 'This password reset link has expired.'])
            );
        }

        $user = User::whereEmail($passwordReset->email)->first();
        
        // Check if user still exists
        if (!$user) {
            $passwordReset->delete();
            return redirect()->route('forgot-password')->with('error',
                trans('auth.reset_token_invalid', ['default' => 'This password reset link is no longer valid.'])
            );
        }
        
        $user->update(['password' => Hash::make($request->input('password'))]);

        $passwordReset->delete();

        return redirect()->route('login')->with('success',
            trans('auth.reset_password_update_success', ['default' => 'Your password was reset successfully.'])
        );
    }

    /**
     * Combine the values of the given keys into a single string.
     */
    public function combine(array $keys, string $delimiter = ''): string
    {
        $values = [];
        foreach ($keys as $key) {
            $values[] = request()->input($key);
        }

        return implode($delimiter, $values);
    }
}
