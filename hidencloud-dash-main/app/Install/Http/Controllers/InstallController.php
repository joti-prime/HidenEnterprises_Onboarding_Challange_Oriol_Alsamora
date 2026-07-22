<?php

namespace App\Install\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function requirements()
    {
        if (Str::length(config('app.key')) === 0) {
            Artisan::call('key:generate --force');
            Artisan::call('storage:link');
        }

        $info = [
            'app_key' => [
                'status' => Str::length(config('app.key')) > 0,
                'key' => config('app.key'),
            ],
            'php' => [
                'status' => version_compare(phpversion(), '8.1', '>='),
                'version' => phpversion(),
            ],
            'mysql' => [
                'status' => extension_loaded('mysqli'),
            ],
        ];

        $info['files'] = [
            'env' => is_readable(base_path('.env')) and is_writable(base_path('.env')),
            'storage' => is_readable(storage_path()) and is_writable(storage_path()),
            'cache' => is_readable(base_path('bootstrap/cache')) and is_writable(base_path('bootstrap/cache')),
        ];

        return view('install::requirements', compact('info'));
    }

    public function configuration(Request $request)
    {
        return view('install::configuration');
    }

    public function database(Request $request)
    {
        if ($this->dbConnect()) {
            return Redirect::to(route('install.user'));
        }

        // License verification removed for HCTestDash (onboarding challenge build).

        return view('install::database');
    }

    public function user(Request $request)
    {
        if (!$this->dbConnect()) {
            return Redirect::to(route('install.database'));
        }
        try {
            if (User::get()->count()){
                return Redirect::to(route('install.mail'));
            }
        } catch (\Exception){
            Artisan::call('module:enable');
            Artisan::call('migrate --force');
        }

        if ($request->isMethod('post')) {
            // Create user code
            $validatedData = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'username' => 'required|string',
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            Artisan::call('user:create', [
                '--first_name' => $validatedData['first_name'],
                '--last_name' => $validatedData['last_name'],
                '--username' => $validatedData['username'],
                '--email' => $validatedData['email'],
                '--password' => $validatedData['password'],
            ]);

            return Redirect::to(route('install.user'));
        }

        return view('install::user');
    }

    public function mail(Request $request)
    {
        if (!$this->dbConnect()) {
            return Redirect::to(route('install.database'));
        }
        if ($request->isMethod('post')) {
            // Setup mail code
            $validatedData = $request->validate([
                'host' => 'required|string|max:255',
                'port' => 'required|integer|min:1|max:65535',
                'type' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log,array',
                'username' => 'required|string|email|max:255',
                'password' => 'required|string|max:255',
            ]);

            Artisan::call('setup:mail', [
                '--driver' => $validatedData['type'],
                '--host' => $validatedData['host'],
                '--port' => $validatedData['port'],
                '--username' => $validatedData['username'],
                '--email' => $validatedData['username'],
                '--password' => $validatedData['password'],
                '--from' => $validatedData['username'],
                '--encryption' => 'TLS',
            ]);

            return Redirect::to(route('dashboard'));
        }

        return view('install::mail');
    }

    private function dbConnect(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
