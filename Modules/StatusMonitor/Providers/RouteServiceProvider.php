<?php

namespace Modules\StatusMonitor\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\StatusMonitor\Http\Controllers';

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Routes visible to any logged in user (the public/user status page).
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('StatusMonitor', '/Routes/web.php'));
    }

    /**
     * Routes for admin CRUD management of monitors.
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'admin'])
            ->prefix('admin/statusmonitor')
            ->namespace($this->moduleNamespace)
            ->group(module_path('StatusMonitor', '/Routes/admin.php'));
    }
}
