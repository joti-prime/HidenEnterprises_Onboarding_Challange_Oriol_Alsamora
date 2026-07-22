<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserOauth;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OauthController extends Controller
{
    public string|null|object $service;

    private bool $authorization = false;

    public function __construct()
    {
        $this->service = request()->route('service');

        // No route bound (CLI contexts like `php artisan route:list` instantiate
        // controllers without a request). Skip validation and config in that case.
        if ($this->service === null) {
            return;
        }

        $services = ['github', 'discord', 'google'];
        if (!in_array($this->service, $services)) {
            abort(404, 'Invalid OAuth service.');
        }
        $this->setConfig();
    }

    /**
     * Build the Socialite driver for the current service.
     *
     * The community Discord Socialite provider injects `prompt=none` by default, which makes
     * Discord reject the authorization request with `consent_required` whenever the user has
     * not previously authorized the HidenCloud app, breaking first-time linking.
     */
    private function driver()
    {
        $driver = Socialite::driver($this->service);

        if ($this->service === 'discord' && method_exists($driver, 'withConsent')) {
            $driver->withConsent();
        }

        return $driver;
    }

    public function login()
    {
        return $this->driver()->redirect();
    }

    public function connect()
    {
        try {
            // Mark this OAuth flow as a "connect" attempt so the callback can detect session loss
            // and refuse to silently log the user into a different account.
            session([
                'oauth_intent' => 'connect',
                'oauth_intent_user_id' => Auth::id(),
                'oauth_intent_service' => $this->service,
            ]);

            return $this->driver()->redirect();
        } catch (Exception $error) {
            ErrorLog::catch('oauth::connect::' . $this->service, $error->getMessage());

            return redirect()->route('user.settings')->with('error',
                trans('auth.oauth_failed',
                    ['default' => 'Authentication Failed: Please contact an Administrator, errors have been logged.'])
            );
        }
    }

    public function remove()
    {
        // Discord cannot be unlinked while the user holds a Free service in any state.
        // This prevents the abuse vector where a user creates a free server, unlinks Discord,
        // links it to a fresh account and creates a second free server.
        if ($this->service === 'discord'
            && Auth::user()->oauthService('discord')->exists()) {
            $hasFreeService = Auth::user()->orders()
                ->where('service', 'freepterodactyl')
                ->whereIn('status', ['active', 'suspended', 'terminated'])
                ->exists();

            if ($hasFreeService) {
                return redirect()->route('user.settings')->with('error',
                    'You cannot unlink your Discord account while you hold a free service. Please delete the free service first, then try again.'
                );
            }
        }

        if (Auth::user()->oauthService($this->service)->exists()) {
            Auth::user()->oauthService($this->service)->first()->delete();
        }

        return redirect()->route('user.settings')->with('success',
            trans('auth.oauth_disconnect',
                ['service' => $this->service, 'default' => ':service was disconnected from your account.'])
        );
    }

    public function callback()
    {
        try {
            $oauthUser = Socialite::driver($this->service)->user();

            // Connect-flow safety net: if the user started a "connect" but lost their session
            // during the OAuth round-trip (cookie expired, blocked, regenerated), do NOT fall
            // back to the login/register path — that would silently link or create a different
            // account and the original account would never get the connection. Bail with an error.
            $intent = session('oauth_intent');
            $intentUserId = session('oauth_intent_user_id');
            $intentService = session('oauth_intent_service');
            session()->forget(['oauth_intent', 'oauth_intent_user_id', 'oauth_intent_service']);

            if ($intent === 'connect' && $intentService === $this->service) {
                if (!Auth::check()) {
                    return redirect()->route('login')->with('error',
                        'Tu sesión expiró durante la conexión. Inicia sesión y vuelve a conectar tu cuenta de ' . ucfirst($this->service) . '.'
                    );
                }
                if ($intentUserId && (int) Auth::id() !== (int) $intentUserId) {
                    return redirect()->route('user.settings')->with('error',
                        'La sesión cambió durante la conexión. Cierra sesión, vuelve a iniciar y reintenta conectar ' . ucfirst($this->service) . '.'
                    );
                }
            }

            // store or update the user for the service
            $result = $this->{$this->service}($oauthUser);

            // If the service method returned a redirect (validation error), use it
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return $result;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle rate limiting specifically
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 429) {
                ErrorLog::catch('oauth::callback::' . $this->service . '::rate_limited', 'Discord API rate limit exceeded');

                return redirect()->route('user.settings')->with('error',
                    'Discord is temporarily busy. Please wait a few seconds and try again.'
                );
            }

            ErrorLog::catch('oauth::callback::' . $this->service, $e->getMessage());

            return redirect()->route('user.settings')->with('error',
                trans('auth.oauth_connect_error',
                    ['default' => 'Something went wrong, please try again.'])
            );
        } catch (Exception $error) {
            ErrorLog::catch('oauth::callback::' . $this->service, $error->getMessage());

            return redirect()->route('user.settings')->with('error',
                trans('auth.oauth_connect_error',
                    ['default' => 'Something went wrong, please try again.'])
            );
        }

        return $this->redirect();
    }

    // store the user for Google
    protected function google($user)
    {
        // ensure user is verified on Google
        if (!$user->user['verified_email']) {
            return redirect()->route('user.settings')->with('error',
                trans('auth.oauth_google_verified_error',
                    ['default' => 'Your Google account is not verified.'])
            );
        }
        $loginRedirect = $this->loginOrRegisterOauthUser($user);
        if ($loginRedirect instanceof \Illuminate\Http\RedirectResponse) {
            return $loginRedirect;
        }
        UserOauth::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'driver' => $this->service,
            ],
            [
                'driver' => $this->service,
                'email' => $user->getEmail(),
                'data' => $user->user,
            ]
        );
    }

    // store the user for GitHub
    protected function github($user)
    {
        $githubUser = Http::withToken($user->token)->get('https://api.github.com/user/emails');
        $verified = collect($githubUser->json())->first(fn($email) => $email['verified'] === true && $email['primary'] === true);
        if (!$verified) {
            return redirect()->route('user.settings')->with('error',
                trans('auth.auth.oauth_failed')
            );
        }
        $loginRedirect = $this->loginOrRegisterOauthUser($user);
        if ($loginRedirect instanceof \Illuminate\Http\RedirectResponse) {
            return $loginRedirect;
        }
        UserOauth::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'driver' => $this->service,
            ],
            [
                'driver' => $this->service,
                'email' => $user->getEmail(),
                'external_profile' => $user->user['html_url'],
                'data' => $user->user,
            ]
        );
    }

    // store the user for Discord
    protected function discord($user)
    {
        // ensure user is verified on discord
        if (!$user->user['verified']) {
            return redirect()->route('user.settings')->with('error',
                trans('auth.oauth_discord_verified_error',
                    ['default' => 'Your Discord account is not verified.'])
            );
        }

        // Check if email is available for Discord accounts
        if (empty($user->getEmail())) {
            return redirect()->route('user.settings')->with('error',
                'Your Discord account must have a verified email address associated with it. Please add an email to your Discord account and try again.'
            );
        }

        // Discord age check (snowflake → creation timestamp). Reject accounts younger than 14 days.
        $discordId = $user->user['id'] ?? null;
        if (!$discordId || !ctype_digit((string) $discordId)) {
            return redirect()->route('user.settings')->with('error',
                'Could not retrieve your Discord account ID. Please try again.'
            );
        }
        $createdMs = ((int) $discordId >> 22) + 1420070400000; // Discord epoch
        $ageDays = (now()->getTimestamp() * 1000 - $createdMs) / 86400000;
        if ($ageDays < 14) {
            return redirect()->route('user.settings')->with('error',
                'Your Discord account is too new to be linked here. Discord accounts must be at least 14 days old.'
            );
        }

        // Uniqueness: a Discord account may only be linked to ONE dash account at a time.
        if (Auth::check()) {
            // Connect-flow: block linking if Discord is already attached to a different account.
            $alreadyLinked = UserOauth::where('driver', 'discord')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?", [(string) $discordId])
                ->where('user_id', '!=', Auth::id())
                ->exists();
            if ($alreadyLinked) {
                return redirect()->route('user.settings')->with('error',
                    "This Discord account is already linked to another HidenCloud account. If that account is yours, log out and use 'Login with Discord' on the login page. You'll be signed in automatically. Each Discord account can only be linked to one HidenCloud account at a time."
                );
            }
        } else {
            // Login-flow: if this Discord is already linked, log into THAT account directly.
            // loginOrRegisterOauthUser() looks users up by EMAIL, so when a user changes their
            // Discord email it would otherwise fail to find them and create a fresh account,
            // re-introducing a duplicate Discord link.
            $existingOauth = UserOauth::where('driver', 'discord')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?", [(string) $discordId])
                ->first();
            if ($existingOauth) {
                $existingUser = User::find($existingOauth->user_id);
                if ($existingUser) {
                    if ($existingUser->isAdmin() && !settings('staff_sso_login', false)) {
                        $this->reportSSOLogin($existingUser);
                        return redirect()->route('login')->with('error',
                            'Staff cannot login using SSO, please contact an administrator.'
                        );
                    }
                    $this->authorization = true;
                    Auth::login($existingUser, true);
                }
            }
        }

        $loginRedirect = $this->loginOrRegisterOauthUser($user);
        if ($loginRedirect instanceof \Illuminate\Http\RedirectResponse) {
            return $loginRedirect;
        }
        UserOauth::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'driver' => $this->service,
            ],
            [
                'driver' => $this->service,
                'email' => $user->getEmail(),
                'data' => $user->user,
            ]
        );
    }

    /**
     * This function dynamically sets the values for config/services.php
     * so that oauth services can retrieve settings set by the user in the admin area
     */
    protected function setConfig(): void
    {
        config([
            'services.' . $this->service . '.client_id' => Settings::getJson('encrypted::oauth::' . $this->service, 'client_id'),
        ]);

        config([
            'services.' . $this->service . '.client_secret' => Settings::getJson('encrypted::oauth::' . $this->service, 'client_secret'),
        ]);

        config([
            'services.' . $this->service . '.redirect' => config('app.url') . '/oauth/' . $this->service . '/redirect',
        ]);
    }

    private function loginOrRegisterOauthUser($oauthUser): ?\Illuminate\Http\RedirectResponse
    {
        if (!Auth::check()) {
            $this->authorization = true;
            // Trying to find a user by email in UserOauth
            $userOauth = UserOauth::where('email', $oauthUser->getEmail())->first();
            if ($userOauth) {
                // check if user is staff and whether staff can login using sso
                if ($userOauth->user->isAdmin() && !settings('staff_sso_login', false)) {
                    $this->reportSSOLogin($userOauth->user);
                    return redirect()->route('login')->with('error',
                        'Staff cannot login using SSO, please contact an administrator.'
                    );
                }

                // If found, authorize the user
                $user = User::find($userOauth->user_id);
                Auth::login($user, true);

                return null;
            }

            // Trying to find a user by email in User
            $user = User::where('email', $oauthUser->getEmail())->first();
            $nickname = $oauthUser->getNickname() ?: Str::random(10); // If the nickname is empty, generate a random one

            // We check the uniqueness of the nickname
            while (User::where('username', $nickname)->exists()) {
                $nickname = $nickname . Str::random(5); // We add a unique suffix
            }

            if (!$user) {
                if (Settings::get('registrations', 'true') == 'false') {
                    return redirect()->route('login')->with('error', trans('auth.registration_disable'));
                }
                // If the user is not found, we create a new one
                $password = Str::random(16);

                // Split the full name into first name and last name
                $nameParts = explode(' ', $oauthUser->getName(), 2);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : $firstName;

                $user = User::create([
                    'username' => $nickname,
                    'email' => $oauthUser->getEmail(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => 'active',
                    'password' => Hash::make($password),
                ]);

                $user->email([
                    'subject' => "HidenCloud Account Created",
                    'content' => "Your account has been created with the following details: <br><br> Username: " . $user->username . "<br> Password: " . $password . "<br><br> Please login and change your password. Remember that you can change your username in your account settings.",
                    'button' => [
                        'name' => settings('app_name'),
                        'url' => route('login'),
                    ]
                ]);
            }

            // check if user is staff and whether staff can login using sso
            if ($user->isAdmin() && !settings('staff_sso_login', false)) {
                $this->reportSSOLogin($user);
                return redirect()->route('login')->with('error',
                    'Staff cannot login using SSO, please contact an administrator.'
                );
            }

            Auth::login($user, true);
        }

        return null;
    }

    protected function reportSSOLogin($user)
    {
        // Report the login to the user
        $user->email([
            'subject' => 'Failed Login Attempt using ' . $this->service,
            'content' => 'You have attempted to login using ' . $this->service . '. SSO logins for staff members have been disabled in settings. <br><br> If this was not you, please contact an administrator.',
        ]);
    }

    private function redirect()
    {
        if ($this->authorization) {
            return redirect()->route('dashboard')->with('success', trans('auth.authenticate_welcome', ['name' => Auth::user()->username]));
        } else {
            return redirect()->route('user.settings')->with('success',
                trans('auth.oauth_connect_success', ['service' => $this->service])
            );
        }
    }
}
