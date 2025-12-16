<?php

namespace App\Http\Controllers;

use App\Models\ProfileRoute;
use App\Models\Role\RoleMapping;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    /**
     * Show users list with their roles
     */
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        return view('admin.roles.index', compact('users', 'roles'));
    }

    /**
     * Show form to edit user roles
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $roleMappings = RoleMapping::getActive();
        $profileRoutes = ProfileRoute::all()->pluck('dashboard_route', 'profile');

        return view('admin.roles.edit', compact('user', 'roles', 'userRoles', 'roleMappings', 'profileRoutes'));
    }

    /**
     * Update user roles
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        // Get role names from IDs
        $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();

        // Sync roles
        $user->syncRoles($roleNames);

        return redirect()->route('admin.roles.index')
            ->with('success', "Roles actualizados para {$user->email}");
    }

    /**
     * Show role mappings configuration
     */
    public function mappings()
    {
        $roleMappings = RoleMapping::all();
        $profileRoutes = ProfileRoute::all();
        $roles = Role::all();

        return view('admin.roles.mappings', compact('roleMappings', 'profileRoutes', 'roles'));
    }

    /**
     * Update role mapping for a profile
     */
    public function updateMapping(Request $request, RoleMapping $mapping)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $mapping->update([
            'roles' => $request->roles,
        ]);

        return redirect()->route('admin.roles.mappings')
            ->with('success', "Configuración de {$mapping->profile} actualizada");
    }

    /**
     * Update profile route
     */
    public function updateRoute(Request $request, ProfileRoute $route)
    {
        $request->validate([
            'dashboard_route' => 'required|string',
        ]);

        $route->update([
            'dashboard_route' => $request->dashboard_route,
        ]);

        return redirect()->route('admin.roles.mappings')
            ->with('success', "Ruta para {$route->profile} actualizada");
    }
}
