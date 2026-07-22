<?php

namespace App\Facades;

use App\Models\Settings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

/**
 * @method static string getViewPath(string $theme, string $path = null)
 * @method static string serviceView(string $service, string $path)
 * @method static string moduleView(string $module, string $path)
 * @method static string wrapper()
 * @method static void registerComponents()
 * @method static string pagination()
 */
class Portal
{
    // You may use this function to return active theme view from Controller
    public static function view($path, array $data = []): Factory|View|Application
    {
        return view('portal.' . Portal::active()->folder . '.' . $path, $data);
    }

    // You may use this function to return details about current active theme
    public static function active()
    {
        if (!Settings::has('portal')) {
            Settings::put('portal', 'Default');
        }

        return Portal::get(Settings::get('portal'));
    }

    // You may use this function to activate a given theme, if theme cannot be found
    // system will opt for the Default theme
    public static function activate($portal): bool
    {
        if (Portal::get($portal)->name !== $portal) {
            return false;
        }

        if (!Settings::has('portal')) {
            Settings::put('portal', 'Default');
        }

        Settings::put('portal', $portal);

        return true;
    }

    // You may use this function to retrieve information about a specific theme,
    // if theme does not exists it will display the default theme
    public static function get($portal)
    {
        $portals = Portal::list();

        if (Arr::has($portals, $portal)) {
            return $portals[$portal];
        }

        return $portals['Default'];
    }

    public static function path($path): string
    {
        return 'portal.' . self::active()->folder . '.' . $path;
    }

    public static function assets($path): string
    {
        return self::active()->assets . '/assets/' . $path;
    }

    // You may use this function to retrieve all available themes
    public static function list(): array
    {
        $dir = base_path('resources/themes/portal');
        $portals = [];
        $contents = scandir($dir);

        foreach ($contents as $item) {
            if (is_dir($dir . '/' . $item) && $item != '.' && $item != '..') {
                if (file_exists($dir . '/' . $item . '/portal.php')) {
                    $portal = include $dir . '/' . $item . '/portal.php';
                    if (isset($portal['name']) and $portal['name'] !== null) {
                        $portals[$portal['name']] = (object) $portal;
                    }
                }
            }
        }

        return $portals;
    }
}
