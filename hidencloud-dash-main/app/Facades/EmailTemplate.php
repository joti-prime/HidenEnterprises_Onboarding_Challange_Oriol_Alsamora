<?php

namespace App\Facades;

use App\Models\Settings;
use Illuminate\Support\Arr;

/**
 * @method static string getViewPath(string $theme, string $path = null)
 * @method static string serviceView(string $service, string $path)
 * @method static string moduleView(string $module, string $path)
 * @method static string wrapper()
 * @method static void registerComponents()
 * @method static string pagination()
 */
class EmailTemplate
{
    // You may use this function to return active theme view from Controller
    public static function view(array $data = []): string
    {
        return 'emails.' . EmailTemplate::active()->folder . '.layout';
    }

    // You may use this function to return details about current active theme
    public static function active()
    {
        if (!Settings::has('email::template')) {
            Settings::put('email::template', 'Default');
        }

        return EmailTemplate::get(Settings::get('email::template'));
    }

    // You may use this function to activate a given theme, if theme cannot be found
    // system will opt for the Default theme
    public static function activate($template): bool
    {
        if (EmailTemplate::get($template)->name !== $template) {
            return false;
        }

        if (!Settings::has('email::template')) {
            Settings::put('email::template', 'Default');
        }

        Settings::put('email::template', $template);

        return true;
    }

    // You may use this function to retrieve information about a specific theme,
    // if theme does not exists it will display the default theme
    public static function get($template)
    {
        $templates = EmailTemplate::list();

        if (Arr::has($templates, $template)) {
            return $templates[$template];
        }

        return $templates['Default'];
    }

    public static function path($path): string
    {
        return 'emails.' . self::active()->folder . '.' . $path;
    }

    public static function assets($path): string
    {
        return self::active()->assets . '/assets/' . $path;
    }

    // You may use this function to retrieve all available email templates
    public static function list(): array
    {
        $dir = base_path('resources/themes/emails');
        $templates = [];
        $contents = scandir($dir);

        foreach ($contents as $item) {
            if (is_dir($dir . '/' . $item) && $item != '.' && $item != '..') {
                if (file_exists($dir . '/' . $item . '/email.php')) {
                    $template = include $dir . '/' . $item . '/email.php';
                    if (isset($template['name']) and $template['name'] !== null) {
                        $templates[$template['name']] = (object) $template;
                    }
                }
            }
        }

        return $templates;
    }
}
