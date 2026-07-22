<?php

namespace App\Services\CloudFlare\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'App\Services\CloudFlare\Http\Controllers';

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('cloudflare', '/Routes/web.php'));
    }

    protected function mapAdminRoutes(): void
    {
        Route::middleware('web', 'admin', 'permission')
            ->namespace($this->moduleNamespace)
            ->prefix('admin/cloudflare')
            ->group(module_path('cloudflare', '/Routes/admin.php'));
    }
}