<?php

namespace Modules\StatusMonitor\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\StatusMonitor\Console\CheckMonitors;

class StatusMonitorServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'StatusMonitor';

    protected string $moduleNameLower = 'statusmonitor';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckMonitors::class,
            ]);
        }

        // Register the recurring check with the app's scheduler without touching
        // app/Console/Kernel.php: the single "schedule:run" cron entry already
        // configured on the server will pick this up automatically.
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('statusmonitor:check')->everyMinute()->withoutOverlapping();
        });
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
