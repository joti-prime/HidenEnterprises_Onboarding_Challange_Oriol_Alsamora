<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {

            config(['services.github.client_id' => Settings::get('oauth::github::client_id')]);
            config(['services.github.client_secret' => Settings::get('oauth::github::client_secret')]);
            // Add more keys here as necessary
        } catch (\Exception $e) {
            // Log the exception or handle it as you wish
        }
    }
}
