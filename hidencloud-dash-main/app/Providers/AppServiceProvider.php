<?php

namespace App\Providers;

use App\Facades\AdminTheme;
use App\Facades\Theme;
use App\Services\Geo\CountryLookup;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CountryLookup::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // force https if FORCE_HTTPS is set to true
        if (config('app.force_https', false)) {
            URL::forceScheme('https');
        }

        // define @settings('key') directive
        Blade::directive('settings', function ($key, $default = null) {
            return "<?php echo App\\Models\\Settings::get({$key}, {$default}); ?>";
        });

        // create @admin directive
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->is_admin();
        });

        // define theme components
        $this->app->booted(function () {
            Theme::registerComponents();
            AdminTheme::registerComponents();
        });
    }
}
