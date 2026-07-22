<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Admin\Group;
use App\Models\Admin\Permission;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::query()->get();

        return Theme::view('groups.index', compact('groups'));
    }

    public function create()
    {
        return Theme::view('groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:groups',
        ]);

        $group = new Group();
        $group->name = $request->input('name');
        $group->save();

        return redirect()->route('groups.index')->with('success',
            trans('responses.group_create_success', ['default' => 'Group :name created successfully.', 'name' => $group->name])
        );
    }

    public function edit(Group $group)
    {
        $permissions = Permission::all();

        return Theme::view('groups.edit', compact('group', 'permissions'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|unique:groups,name,' . $group->id,
        ]);
        $group->name = $request->input('name');
        $group->save();
        $group->permissions()->sync($request->input('perms'), true);

        return redirect()->route('groups.index')->with('success',
            trans('responses.group_update_success', ['default' => 'Group :name updated successfully.', 'name' => $group->name])
        );
    }

    public function destroy(Group $group)
    {
        $group->users()->detach();
        $group->delete();

        return redirect()->route('groups.index')->with('success',
            trans('responses.group_delete_success', ['default' => 'Group :name deleted successfully.', 'name' => $group->name])
        );
    }

    public function showUsers(Group $group)
    {
        $users = $group->users()->paginate(20);

        if($users->isEmpty()) {
            return redirect()->route('groups.index')->with('error', 'No users found in this group.');
        }

        return Theme::view('groups.users', compact('group', 'users'));
    }
}
