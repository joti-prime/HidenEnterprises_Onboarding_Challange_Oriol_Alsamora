<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Admin\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()->get();

        return Theme::view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return Theme::view('permissions.create');
    }

    public function show(Permission $permission)
    {
        return Theme::view('permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return Theme::view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $permission->name = $request->input('name');
        $permission->descriptions = empty($request->input('descriptions')) ? '' : $request->input('descriptions');
        $permission->save();

        return redirect()->route('permissions.index')->with('success',
            trans('responses.permission_update_success',
                ['default' => 'Permission updated successfully.'])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);
        $permission = new Permission;
        $permission->name = $request->input('name');
        $permission->descriptions = empty($request->input('descriptions')) ? '' : $request->input('descriptions');
        $permission->order = 1;
        $permission->save();

        return redirect()->route('permissions.index')->with('success',
            trans('responses.permission_create_success',
                ['default' => 'Permission created successfully.'])
        );
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success',
            trans('responses.permission_delete_success',
                ['default' => 'Permission deleted successfully.'])
        );
    }

    public function import()
    {
        Artisan::call('permissions:save');

        return redirect()->route('permissions.index')->with('success', Artisan::output());
    }
}
