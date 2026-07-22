<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResourceApiClient;
use App\Facades\AdminTheme as Theme;
use App\Facades\Theme as ClientTheme;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    public function themes()
    {
        $api = new ResourceApiClient;
        $marketplace = $api->getAllResources('Templates', 'client');
        if (array_key_exists('error', $marketplace)) {
            $marketplace = [];
        }

        return Theme::view('themes.client_themes', compact('marketplace'));
    }

    public function activate($theme)
    {
        if (!ClientTheme::activate($theme)) {
            return redirect()->back()->with('error',
                trans('responses.themes_activate_error',
                    ['default' => 'Unable to activate theme, reverted to default. Please check if theme has theme.php file and all files have been uploaded!'])
            );
        }

        return redirect()->back()->with('success',
            trans('responses.themes_activate_success',
                ['default' => 'Theme has been activated!'])
        );
    }

    public function admin_activate($theme)
    {
        if (!Theme::activate($theme)) {
            return redirect()->back()->with('error',
                trans('responses.themes_activate_error',
                    ['default' => 'Unable to activate theme, reverted to default. Please check if theme has theme.php file and all files have been uploaded!'])
            );

        }

        return redirect()->back()->with('success',
            trans('responses.themes_activate_success',
                ['default' => 'Theme has been activated!'])
        );

    }

    public function admin_themes()
    {
        $api = new ResourceApiClient;
        $marketplace = $api->getAllResources('Templates', 'admin');
        if (array_key_exists('error', $marketplace)) {
            $marketplace = [];
        }

        return Theme::view('themes.admin_themes', compact('marketplace'));
    }

    public function files($folder)
    {
        $directory = resource_path('themes/' . $folder);
        $files = File::files($directory);
        $directories = File::directories($directory);

        return Theme::view('themes.files.index', compact('files', 'directories', 'folder', 'directory'));
    }

    public function edit_file()
    {
        $filename = $_GET['file'];
        if (!$_GET['file'] or $_GET['file'] == null) {
            return redirect()->back()->with('error',
                trans('responses.themes_edit_file_error',
                    ['default' => 'Specified file could not be found, please try again'])
            );
        }

        if (!str_contains($filename, '.blade.php') or str_contains($filename, './')) {
            return redirect()->back()->with('error',
                trans('responses.themes_edit_file_blade_error',
                    ['default' => 'You are only allowed to edit files with the .blade.php extension'])
            );
        }

        $filePath = resource_path('themes/' . $filename);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error',
                trans('responses.themes_edit_file_exist_error',
                    ['default' => 'File does not exist or has been deleted.'])
            );
        }

        $contents = File::get($filePath);

        return Theme::view('themes.files.edit', compact('filename', 'contents'));
    }

    public function save_file(Request $request)
    {
        $filename = $_GET['file'];
        if (!$_GET['file'] or $_GET['file'] == null) {
            return redirect()->back()->with('error',
                trans('responses.themes_save_error',
                    ['default' => 'Specified file could not be found, please try again'])
            );
        }

        $file = resource_path('themes/' . $filename);

        if (!File::exists($file)) {
            return redirect()->back()->with('error',
                trans('responses.themes_save_exist_error',
                    ['default' => 'File does not exist or has been deleted.'])
            );
        }

        if (!is_writable($file)) {
            return redirect()->back()->with('error',
                trans('responses.themes_save_permission_error',
                    ['default' => 'File is not writable, permission denied'])
            );
        }

        $contents = $request->input('contents');
        File::put($file, $contents);

        return redirect()->back()->with('success',
            trans('responses.themes_save_success',
                ['default' => 'File saved successfully.'])
        );
    }
}
