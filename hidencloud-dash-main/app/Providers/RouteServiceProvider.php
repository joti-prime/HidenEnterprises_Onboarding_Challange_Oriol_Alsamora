<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Map API routes
            $this->mapApiRoutes();

            Route::middleware('web', 'admin', 'permission')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function mapApiRoutes()
    {
        // v1 api routes
        Route::prefix('api/v1')
            ->middleware('api', 'application-api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api/v1.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Authentication rate limiting. For login and checkpoint endpoints we'll apply
        // a limit of 8 requests per minute, for the forgot password endpoint apply a
        // limit of two per minute for the requester so that there is less ability to
        // trigger email spam.
        RateLimiter::for('authentication', function (Request $request) {
            if ($request->route()->named('forgot-password.send-email')) {
                return Limit::perMinute(2)->by($request->ip());
            }

            if ($request->route()->named('2fa.recover.access')) {
                return Limit::perMinute(3);
            }

            return Limit::perMinute(8);
        });

        // rate limit amount of requests for api
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('utils.api-ratelimit', 60))->by($request->user()?->id ?: $request->ip());
        });
    }
}
