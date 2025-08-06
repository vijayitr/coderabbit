<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    // Show all roles
    public function index()
    {
        $permissions = $this->user->getAllPermissions()->pluck('name');
        // echo 'inside code<pre>'; print_r($permissions); die;
        if (is_null($this->user) || !$this->user->can('role.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any role !');
        }
        $position = auth()->user()->roles->min('position');
        $roles = Role::with('permissions')->where('position', '>', $position)->orderBy('position', 'asc')->get();
        return view('roles.index', compact('roles'));
    }

    // Show form to create a new role
    public function create()
    {
        $role_counts = Role::count() + 1;
        $position = auth()->user()->roles->min('position');
        return view('roles.create', compact('role_counts', 'position'));
    }

    // Store a new role
    public function store(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('role.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any role !');
        }
        $position = auth()->user()->roles->min('position');
        if ($position >= $request->position) {
            return back()->with('error', 'You are not Unauthorized to assing position '.$request->position);
        }
        $role = Role::create(['name' => $request->name, 'position' => $request->position]);
        return redirect()->route('roles.index');
    }

    // Show form to assign permissions to a role
    public function assignPermissions(Role $role)
    {
        if ($role->id == 1) {
            abort(403, 'Sorry !! You are Unauthorized !');
        }
        if (is_null($this->user) || !$this->user->can('role.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to edit any role !');
        }
        $roleIds = auth()->user()->roles->pluck('id')->toArray();
        $permissionIds = auth()->user()->getAllPermissions()->pluck('id')->toArray();
        $permissionGroups = Permission::when(!in_array(1, $roleIds), function ($query) use ($permissionIds) {
            return $query->whereIn('id', $permissionIds);
        })->get()->groupBy('group_name');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role_counts = Role::count();
        $position = auth()->user()->roles->min('position');
        return view('roles.assignPermissions', compact('role', 'permissionGroups', 'role_counts', 'position'));
    }

    // Update permissions for a role
    public function updatePermissions(Request $request, $roleId)
    {
        if (is_null($this->user) || !$this->user->can('role.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to edit any role !');
        }

        if ($roleId == 1) {
            abort(403, 'Sorry !! You are Unauthorized !');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|integer|min:1',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Find the role
        $role = Role::findOrFail($roleId);
        $position = auth()->user()->roles->min('position');
        if ($position >= $request->position) {
            session()->flash('warning', 'You are not Unauthorized to assing position '.$request->position);
            $validated['position'] = $role->position;
        }
        // Update role details
        $role->update([
            'name' => $validated['name'],
            'position' => $validated['position'],
        ]);

        // Sync the permissions
        $role->permissions()->sync($validated['permissions']);

        // Flash success message
        session()->flash('success', 'Permissions have been successfully updated.');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        // Redirect to the roles page
        return redirect()->route('roles.index');
    }

    public function deleteRole($id)
    {
        if (is_null($this->user) || !$this->user->can('role.delete')) {
            abort(403, 'Sorry !! You are Unauthorized to delete any role !');
        }

        if ($id == 1) {
            abort(403, 'Sorry !! You are Unauthorized !');
        }
        // Find the role by ID
        $role = Role::findOrFail($id);

        // Detach all permissions associated with this role
        $role->permissions()->detach();

        // Delete the role
        $role->delete();

        // Provide feedback to the user
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        session()->flash('success', 'Role deleted successfully along with unassigned permissions.');
        return redirect()->route('roles.index');
    }

}
