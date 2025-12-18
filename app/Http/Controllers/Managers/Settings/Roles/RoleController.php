<?php

namespace App\Http\Controllers\Managers\Settings\Roles;

use App\Http\Controllers\Managers\BaseManagerController;
use App\Http\Requests\Systems\RoleRequest;
use App\Models\Role\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleController extends BaseManagerController
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', $this->getPaginationPerPage());
        $searchKey = $request->get('search', '');

        $roles = Role::withCount('users')
            ->when($searchKey, function ($query) use ($searchKey) {
                return $query->where(function ($q) use ($searchKey) {
                    $q->where('name', 'like', "%{$searchKey}%")
                        ->orWhere('description', 'like', "%{$searchKey}%");
                });
            })
            ->latest()
            ->paginate($perPage);

        if ($request->expectsJson()) {
            return $this->success('Roles retrieved successfully', [
                'roles' => $roles,
            ]);
        }

        return view('managers.views.settings.roles.roles.index', compact('roles', 'searchKey'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = $this->getAvailablePermissions();

        return view('managers.views.settings.roles.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage
     */
    public function store(RoleRequest $request)
    {
        $data = $request->validated();

        // Set default role - only one can be default
        if ($request->has('is_default') && $request->boolean('is_default')) {
            Role::where('guard_name', $data['guard_name'])->update(['is_default' => false]);
        }

        // Set slug from name if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Track who created this role
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $role = Role::create($data);

        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->input('permissions'))) {
            $permissions = Permission::whereIn('id', $request->input('permissions'))
                ->where('guard_name', $role->guard_name)
                ->get();

            $role->syncPermissions($permissions);
        }

        if ($request->expectsJson()) {
            return $this->success('Role created successfully', [
                'role' => $role->load('permissions'),
            ]);
        }

        return redirect()->route('manager.roles.edit', $role->id)
            ->with('success', 'Rol creado correctamente.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('users', 'permissions');

        if (request()->expectsJson()) {
            return $this->success('Role retrieved successfully', [
                'role' => $role,
            ]);
        }

        return view('managers.views.settings.roles.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = $this->getAvailablePermissions();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('managers.views.settings.roles.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage
     */
    public function update(RoleRequest $request, Role $role)
    {
        $data = $request->validated();

        // Prevent system roles from being modified
        if (in_array($role->name, ['super-admin', 'customer'])) {
            return $this->error('Cannot modify system roles');
        }

        // Set default role - only one can be default
        if ($request->has('is_default') && $request->boolean('is_default')) {
            Role::where('guard_name', $data['guard_name'])
                ->where('id', '!=', $role->id)
                ->update(['is_default' => false]);
        }

        // Set slug from name if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Track who updated this role
        $data['updated_by'] = auth()->id();

        $role->update($data);

        // Update permissions if provided
        if ($request->has('permissions') && is_array($request->input('permissions'))) {
            $permissions = Permission::whereIn('id', $request->input('permissions'))
                ->where('guard_name', $role->guard_name)
                ->get();

            $role->syncPermissions($permissions);
        }

        if ($request->expectsJson()) {
            return $this->success('Role updated successfully', [
                'role' => $role->load('permissions'),
            ]);
        }

        return redirect()->route('manager.roles.edit', $role->id)
            ->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified role from storage
     */
    public function destroy(Role $role)
    {
        // Prevent system roles from being deleted
        if (in_array($role->name, ['super-admin', 'customer'])) {
            if (request()->expectsJson()) {
                return $this->error('Cannot delete system roles');
            }

            return back()->with('error', 'No se pueden eliminar roles del sistema.');
        }

        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            if (request()->expectsJson()) {
                return $this->error('Cannot delete role with assigned users');
            }

            return back()->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();

        if (request()->expectsJson()) {
            return $this->success('Role deleted successfully');
        }

        return redirect()->route('manager.roles')
            ->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Duplicate a role
     */
    public function duplicate(Role $role)
    {
        $newRole = $role->replicate();
        $newRole->name = $role->name.' (Copy)';
        $newRole->slug = Str::slug($newRole->name).'-'.uniqid();
        $newRole->is_default = false;
        $newRole->created_by = auth()->id();
        $newRole->updated_by = auth()->id();
        $newRole->save();

        // Copy permissions
        $newRole->syncPermissions($role->permissions);

        return $this->success('Role duplicated successfully', [
            'role' => $newRole->load('permissions'),
        ]);
    }

    /**
     * Assign users to a role
     */
    public function assignUsers(Role $role, Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->input('user_ids', []);
        $assignedCount = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->roles()->syncWithoutDetaching([$role->id]);
                $assignedCount++;
            }
        }

        return $this->success("$assignedCount users assigned to role successfully", [
            'assigned_count' => $assignedCount,
        ]);
    }

    /**
     * Remove a user from a role
     */
    public function removeUser(Role $role, User $user)
    {
        $user->roles()->detach($role->id);

        return $this->success('User removed from role successfully', [
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Show permissions assignment form
     */
    public function showPermissions(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('managers.views.settings.roles.roles.permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update permissions for a role
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $request->input('permissions'))
            ->where('guard_name', $role->guard_name)
            ->get();

        $role->syncPermissions($permissions);

        return $this->success('Permissions updated successfully', [
            'assigned' => $permissions->pluck('name'),
        ]);
    }

    /**
     * Show users assigned to a role
     */
    public function showUsers(Role $role, Request $request)
    {
        $search = $request->get('search', '');

        $users = $role->users()
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($this->getPaginationPerPage());

        if ($request->expectsJson()) {
            return $this->success('Users retrieved successfully', [
                'users' => $users,
            ]);
        }

        return view('managers.views.settings.roles.roles.users', compact('role', 'users', 'search'));
    }
}
