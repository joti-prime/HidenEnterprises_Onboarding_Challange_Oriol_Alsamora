<?php

namespace App\Console\Commands;

use App\Models\Admin\Permission;
use Illuminate\Console\Command;

class SavePermisionsRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saves perms for routes';

    public function handle(): void
    {
        $middlewareName = 'permission';
        $routes = collect(\Route::getRoutes())->filter(function ($route) use ($middlewareName) {
            return in_array($middlewareName, $route->gatherMiddleware());
        });

        $routesData = $routes->map(function ($route) {
            return [
                'name' => $route->getName() ?? 'No name',
                'descriptions' => config('app.url') . '/' . $route->uri(),
            ];
        })->toArray();

        // We store route data in the database
        // Permission::insert($routesData);

        foreach ($routesData as $route) {
            Permission::updateOrInsert(
                ['name' => $route['name']],
                $route
            );
        }

        $this->info('Middleware routes saved to database!');
    }
}
