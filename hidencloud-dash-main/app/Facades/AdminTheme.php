<?php

namespace App\Facades;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

class AdminTheme
{
    // returns view of currently active theme
    public static function view($path, array $data = []): Factory|View|Application
    {
        return view('admin.' . self::active()->folder . '.' . $path, $data);
    }

    public static function active()
    {
        if (!Cache::has('admin_theme')) {
            Cache::put('admin_theme', 'Default');
        }

        return self::get(Cache::get('admin_theme'));
    }

    // You may use this function to activate a given theme, if theme cannot be found
    // system will opt for the Default theme
    public static function activate($theme): bool
    {
        if (self::get($theme)->name !== $theme) {
            return false;
        }

        if (!Cache::has('admin_theme')) {
            Cache::put('admin_theme', 'Default');
        }

        Cache::put('admin_theme', $theme);

        return true;
    }

    public static function get($theme)
    {
        $themes = self::list();

        if (Arr::has($themes, $theme)) {
            return $themes[$theme];
        }

        return $themes['Default'];
    }

    public static function getViewPath($theme, $path = null): string
    {
        return 'admin.' . self::get($theme)->folder . '.' . $path;
    }

    public static function path($path): string
    {
        return 'admin.' . self::active()->folder . '.' . $path;
    }

    public static function base_path($path = null): string
    {
        $path = $path ? "/$path" : $path;

        return 'resources/themes/admin/' . self::active()->folder . $path;
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

        // If nothing is found, return the template from the Default theme
        return strtolower($service) . '::admin.default.' . $path;
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

        // If nothing is found, return the template from the Default theme
        return strtolower($module) . '::admin.default.' . $path;
    }

    public static function assets($path): string
    {
        $theme_assets = '/assets/themes/' . self::active()->folder . '/assets/' . $path;
        if (file_exists(base_path('/public/' .  $theme_assets))) {
            return $theme_assets;
        }

        return self::active()->assets . '/assets/' . $path;
    }

    public static function wrapper(): string
    {
        return 'admin.' . self::active()->folder . '.' . self::active()->wrapper;
    }

    public static function list(): array
    {
        $dir = base_path('resources/themes/admin');
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
        $componentsPath = base_path("resources/themes/admin/{$activeThemeFolder}/components");
        Blade::anonymousComponentPath($componentsPath);
    }

    public static function pagination(): string
    {
        return self::path('layouts.pagination');
    }
}
