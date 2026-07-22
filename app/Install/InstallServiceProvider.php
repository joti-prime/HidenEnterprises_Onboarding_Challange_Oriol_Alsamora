<?php

namespace App\Install;

use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'install');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    public function register()
    {
        //
    }
}
