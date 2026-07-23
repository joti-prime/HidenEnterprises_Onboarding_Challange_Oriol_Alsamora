<?php

namespace App\Services\UptimeRobot\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'App\Services\UptimeRobot\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Client-facing routes for the manage page's monitor actions.
     * Note: these all include the {order} parameter, which is required
     * for WemX to apply per-service permission checks to them.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('UptimeRobot', '/Routes/web.php'));
    }
}
