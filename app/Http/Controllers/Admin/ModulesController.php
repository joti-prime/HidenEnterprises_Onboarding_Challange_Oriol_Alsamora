<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResourceApiClient;
use App\Events\ModuleServiceDeleted;
use App\Events\ModuleServiceDisabled;
use App\Events\ModuleServiceEnabled;
use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;

class ModulesController extends Controller
{
    // return login page view
    public function index()
    {
        $api = new ResourceApiClient;
        $marketplace = $api->getAllResources('Modules');
        if (array_key_exists('error', $marketplace)) {
            $marketplace = [];
        }

        return Theme::view('modules.index', compact('marketplace'));
    }

    public function toggleStatus($module)
    {
        $module = Module::find($module);

        if (!$module) {
            return redirect()->back()->with('error',
                trans('responses.module_toggle_error', ['default' => 'Specified module does not exist or has been deleted.'])
            );
        }

        if ($module->isEnabled()) {
            event(new ModuleServiceDisabled($module, auth()->user()));
            $module->disable();

            return redirect()->back()->with('warning',
                trans('responses.module_toggle_warning', ['module' => $module->getName(), 'default' => 'Module :module has been disabled'])
            );
        }

        try {
            $module->enable();
            event(new ModuleServiceEnabled($module, auth()->user()));
            Artisan::call('module:migrate', ['module' => $module->getName(), '--force' => true]);
            Artisan::call('module:update', ['module' => $module->getName()]);
            Artisan::call('module:publish', ['module' => $module->getName()]);
            if (!$this->checkModuleMigrations($module)) {
                Artisan::call('module:disable', ['module' => $module->getName()]);

                return redirect()->back()->with('error', 'Module migrations are not up to date. Please run <code>php artisan module:migrate ' . $module->getName() . ' --force </code>');
            }
        } catch (Exception) {
            event(new ModuleServiceEnabled($module, auth()->user()));
            Artisan::queue('module:enable', ['module' => $module->getName()]);
            Artisan::queue('module:migrate', ['module' => $module->getName(), '--force' => true]);
            Artisan::queue('module:update', ['module' => $module->getName()]); // update module composer
            Artisan::queue('module:publish', ['module' => $module->getName()]); // publish module assets
            if (!$this->checkModuleMigrations($module)) {
                Artisan::queue('module:disable', ['module' => $module->getName()]);

                return redirect()->back()->with('error', 'Module migrations are not up to date. Please run <code>php artisan module:migrate ' . $module->getName() . ' --force </code>');
            }
        }

        return redirect()->back()->with('success',
            trans('responses.module_toggle_success', ['module' => $module->getName(), 'default' => 'Module :module has been enabled'])
        );
    }

    public function delete($module)
    {
        $module = Module::find($module);
        if (!$module) {
            return redirect()->back()->with('error',
                trans('responses.module_toggle_error', ['default' => 'Specified module does not exist or has been deleted.'])
            );
        }
        if ($module->isEnabled()) {
            $module->disable();
        }
        try {
            event(new ModuleServiceDeleted($module, auth()->user()));
            $module->delete();
        } catch (Exception) {
            event(new ModuleServiceDeleted($module, auth()->user()));
            Artisan::queue('module:delete', ['module' => $module->getName()]);
        }

        return redirect()->back()->with('success', 'Module has been deleted');
    }

    public function checkModuleMigrations($module)
    {
        $migration_path = module_path($module->getName(), 'Database/Migrations');
        if (!is_dir($migration_path)) {
            return true;
        }
        $migrationFiles = \File::files($migration_path);
        $migrated = \DB::table('migrations')->pluck('migration')->toArray();
        foreach ($migrationFiles as $file) {
            $fileNameWithoutExtension = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (!in_array($fileNameWithoutExtension, $migrated)) {
                return false;
            }
        }

        return true;
    }
}
