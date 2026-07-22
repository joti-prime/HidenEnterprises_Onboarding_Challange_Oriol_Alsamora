<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\TwoFA;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct(Google2FA $google2fa) {
        $this->google2fa = $google2fa;
    }

    public function enableTwoFactor()
    {
        $user = auth()->user();
        if ($user->TwoFa()->exists()) {
            return redirect()->route('user.settings')->withError(trans('responses.enable_two_factor'));
        }

        $secretKey = $this->getOrGenerateSecretKey();
        $QRcode = $this->google2fa->getQRCodeInline(
            settings('app_name', 'HCTestDash'),
            $user->email,
            $secretKey
        );

        return Theme::view('auth.2fa.setup', ['QRcode' => $QRcode, 'secretKey' => $secretKey]);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'OPT' => 'required|numeric|digits:6',
        ]);

        $user = auth()->user();
        $validationResult = $user->twoFA->validate($request->input('OPT'));

        if ($validationResult === 'reused') {
            return redirect()->back()->withError(trans('responses.otp_code_reused_two_factor'));
        }

        if (!$validationResult) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        if ($user->TwoFa()->exists()) {
            $user->TwoFa->disable();
        }

        // Clear 2FA validation from session when disabling
        session()->forget('2fa_validated_at');

        $user->twoFaDisabledNotification();

        return redirect()->route('user.settings')->withSuccess(trans('responses.disable_two_factor'));
    }

    public function setupTwoFactor(Request $request)
    {
        $request->validate([
            'OPT' => 'required|numeric|digits:6',
        ]);

        if (!session()->has('2fa_secret')) {
            return redirect()->route('user.settings')->withError(trans('responses.2fa_secret_error_two_factor'));
        }

        $secretKey = $this->getOrGenerateSecretKey();

        // During setup, we don't check for reuse since user doesn't have 2FA enabled yet
        if (!$this->google2fa->verifyKey($secretKey, $request->input('OPT'))) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        return redirect()->route('2fa.recovery');
    }

    public function recovery()
    {
        $user = auth()->user();
        if ($user->TwoFa()->exists()) {
            return redirect()->route('user.settings')->withError(trans('responses.enable_two_factor'));
        }

        if (!session()->has('2fa_secret') and !session()->has('recovery_codes')) {
            return redirect()->route('user.settings')->withError(trans('responses.2fa_secret_error_two_factor'));
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        return Theme::view('auth.2fa.recovery', ['recoveryCodes' => $recoveryCodes]);
    }

    public function activateTwoFactor(Request $request)
    {
        $request->validate([
            'stored_recovery' => 'required|boolean',
        ]);

        $user = auth()->user();
        if ($user->TwoFa()->exists()) {
            return redirect()->route('user.settings')->withError(trans('responses.enable_two_factor'));
        }

        if (!session()->has('2fa_secret') and !session()->has('recovery_codes')) {
            return redirect()->route('user.settings')->withError(trans('responses.2fa_secret_error_two_factor'));
        }

        $twoFa = new TwoFA;
        $twoFa->user_id = $user->id;
        $twoFa->key = $this->getOrGenerateSecretKey();
        $twoFa->recovery_codes = $this->generateRecoveryCodes();
        $twoFa->session_expires_at = Carbon::now();
        $twoFa->save();

        $user->twoFaEnabledNotification();

        return redirect('/dashboard')->withSuccess(trans('responses.enable_success_two_factor'));
    }

    public function validateTwoFactor()
    {
        $user = auth()->user();
        if (!$user->TwoFa()->exists()) {
            return redirect()->route('dashboard')->withError(trans('responses.validate_error_two_factor'));
        }

        return Theme::view('auth.2fa.validate');
    }

    public function validateTwoFactorCheck(Request $request)
    {
        $request->validate([
            'OPT' => 'required|numeric|digits:6',
        ]);

        $user = auth()->user();
        $validationResult = $user->twoFA->validate($request->input('OPT'));

        if ($validationResult === 'reused') {
            return redirect()->back()->withError(trans('responses.otp_code_reused_two_factor'));
        }

        if (!$validationResult) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        // Store 2FA validation in the current session only
        session(['2fa_validated_at' => Carbon::now()->toDateTimeString()]);

        return redirect()->intended(route('dashboard'))->withSuccess(trans('auth.authenticate_welcome', ['name' => $user->username]));
    }

    public function recover()
    {
        return Theme::view('auth.2fa.recover');
    }

    public function recoverDeviceAccess(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'regex:/^[a-zA-Z0-9]{5}-[a-zA-Z0-9]{5}$/'],
        ]);

        $user = auth()->user();
        if (!$user->TwoFa()->exists()) {
            return redirect()->route('dashboard')->withError(trans('responses.validate_error_two_factor'));
        }

        $recovery_codes = (array) $user->TwoFa->recovery_codes;
        if (!in_array($request->input('recovery_code'), $recovery_codes)) {
            return redirect()->back()->withError(trans('responses.two_factor_code_not_exist'));
        }

        $user->TwoFa->disable();

        // Clear 2FA validation from session when using recovery code
        session()->forget('2fa_validated_at');

        return redirect()->route('dashboard')->withSuccess(trans('responses.two_factor_disabled_success'));
    }

    protected function downloadRecoveryCodes()
    {
        $name = settings('app_name', 'laravel');
        $recoveryCodes = $this->generateRecoveryCodes();

        // Join array elements with a newline
        $content = implode(PHP_EOL, $recoveryCodes);

        return Response::streamDownload(function () use ($content) {
            echo $content;
        }, "recovery-codes {$name}.txt");
    }

    protected function getOrGenerateSecretKey()
    {
        if (session()->has('2fa_secret')) {
            return decrypt(session('2fa_secret'));
        }

        session(['2fa_secret' => encrypt($this->google2fa->generateSecretKey())]);

        return decrypt(session('2fa_secret'));
    }

    protected function generateRecoveryCodes(): array
    {
        if (session()->has('recovery_codes')) {
            return decrypt(session('recovery_codes'));
        }

        $recovery_codes = [];
        foreach (range(1, 12) as $i) {
            $recovery_codes[$i] = Str::random(5) . '-'. Str::random(5);
        }

        session(['recovery_codes' => encrypt($recovery_codes)]);

        return decrypt(session('recovery_codes'));
    }
}
