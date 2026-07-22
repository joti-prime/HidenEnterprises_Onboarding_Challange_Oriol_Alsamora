<?php

namespace App\Facades;

use App\Models\Settings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;

class Theme
{
    // You may use this function to return active theme view from Controller
    public static function view($path, array $data = []): Factory|View|Application
    {
        return view('client.' . self::active()->folder . '.' . $path, $data);
    }

    // You may use this function to return details about current active theme
    public static function active()
    {
        if (!Settings::has('theme')) {
            Settings::put('theme', 'Tailwind');
        }

        return self::get(Settings::get('theme'));
    }

    // You may use this function to activate a given theme, if theme cannot be found
    // system will opt for the Default theme
    public static function activate($theme): bool
    {
        if (self::get($theme)->name !== $theme) {
            return false;
        }

        if (!Settings::has('theme')) {
            Settings::put('theme', 'Tailwind');
        }

        Settings::put('theme', $theme);

        return true;
    }

    // You may use this function to retrieve information about a specific theme,
    // if theme does not exist it will display the default theme
    public static function get($theme)
    {
        $themes = self::list();

        if (Arr::has($themes, $theme)) {
            return $themes[$theme];
        }

        return $themes['Tailwind'];
    }

    // You may use this function to retrieve view path to a specific theme,
    // additionally you may pass optional variable $path to direct to a specific folder
    public static function getViewPath($theme, $path = null): string
    {
        return 'client.' . self::get($theme)->folder . '.' . $path;
    }

    public static function path($path): string
    {
        return 'client.' . self::active()->folder . '.' . $path;
    }

    public static function base_path($path = null): string
    {
        $path = $path ? "/$path" : $path;

        return 'resources/themes/client/' . self::active()->folder . $path;
    }

    public static function serviceView(string $service, string $path): string
    {
        // We check the blade file in the theme
        $activeThemePath = 'client.' . self::active()->folder . '.' . ucfirst($service) . '.' . $path;
        if (\View::exists($activeThemePath)) {
            return $activeThemePath;
        }
        // Checking in the service directory
        $themePath = strtolower($service) . '::' . self::path($path);
        if (\View::exists($themePath)) {
            return $themePath;
        }

        // If nothing is found, return the template from the Tailwind theme
        return strtolower($service) . '::client.tailwind.' . $path;
    }

    public static function moduleView(string $module, string $path): string
    {
        // We check the blade file in the theme
        $activeThemePath = 'client.' . self::active()->folder . '.' . ucfirst($module) . '.' . $path;
        if (\View::exists($activeThemePath)) {
            return $activeThemePath;
        }
        // Checking in the module directory
        $themePath = strtolower($module) . '::' . self::path($path);
        if (\View::exists($themePath)) {
            return $themePath;
        }

        // If nothing is found, return the template from the Tailwind theme
        return strtolower($module) . '::client.tailwind.' . $path;
    }

    public static function assets($path): string
    {
        $theme_assets = '/assets/themes/' . self::active()->folder . '/assets/' . $path;
        if (file_exists(base_path('/public/' .  $theme_assets))) {
            return $theme_assets;
        }

        return self::active()->assets . '/assets/' . $path;
    }

    // Module developers may use this function to return the wrapper of the active theme
    // to use it, add @extends(Theme::wrapper())
    public static function wrapper(): string
    {
        return 'client.' . self::active()->folder . '.' . self::active()->wrapper;
    }

    // You may use this function to retrieve all available themes
    public static function list(): array
    {
        $dir = base_path('resources/themes/client');
        $themes = [];
        $contents = scandir($dir);

        foreach ($contents as $item) {
            if (is_dir($dir . '/' . $item) && $item != '.' && $item != '..') {
                if (file_exists($dir . '/' . $item . '/theme.php')) {
                    $theme = include $dir . '/' . $item . '/theme.php';
                    if (isset($theme['name']) and $theme['name'] !== null) {
                        $themes[$theme['name']] = (object) $theme;
                    }
                }
            }
        }

        return $themes;
    }

    public static function registerComponents(): void
    {
        $activeThemeFolder = self::active()->folder;
        $componentsPath = base_path("resources/themes/client/{$activeThemeFolder}/components");
        Blade::anonymousComponentPath($componentsPath);
    }

    public static function pagination(): string
    {
        return self::path('layouts.pagination');
    }
}
