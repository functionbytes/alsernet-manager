<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class UsersController extends Controller
{
    /**
     * Roles that require warehouse/shop assignment
     */
    protected array $rolesRequiringAssignment = [
        'inventory-manager',
        'inventory-staff',
        'shop-manager',
        'shop-staff',
    ];

    public function index(Request $request)
    {
        // Fix: Use correct null coalescing syntax
        $searchKey = $request->search ?? null;
        $roleFilter = $request->role ?? null;

        $users = User::latest();

        // Search by name, email, or identification
        if ($searchKey) {
            $users->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.lastname', 'like', '%' . $searchKey . '%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%' . $searchKey . '%')
                    ->orWhere('users.email', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.identification', 'like', '%' . $searchKey . '%');
            });
        }

        // Filter by role name (not by ID)
        if ($roleFilter) {
            $users->whereHas('roles', function ($query) use ($roleFilter) {
                $query->where('roles.name', $roleFilter);
            });
        }

        $users = $users->paginate(paginationNumber());

        return view('managers.views.users.users.index')->with([
            'users' => $users,
            'roleFilter' => $roleFilter,
            'searchKey' => $searchKey,
            'availableRoles' => SpatieRole::pluck('name', 'name')->toArray(),
        ]);
    }

    public function create()
    {
        $shops = Shop::get()->pluck('title', 'id');
        // Get all roles from Spatie
        $roles = SpatieRole::orderBy('name')->pluck('name', 'id');

        return view('managers.views.users.users.create')->with([
            'shops' => $shops,
            'roles' => $roles,
            'rolesRequiringAssignment' => $this->rolesRequiringAssignment,
        ]);
    }

    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'firstname' => 'required|string|min:3|max:100',
            'lastname' => 'required|string|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'available' => 'required|boolean',
            'role' => 'required|numeric|exists:roles,id',
            'shop' => 'nullable|numeric|exists:shops,id',
        ], [
            'email.unique' => 'El correo electrónico ya está registrado en el sistema.',
            'email.required' => 'El correo electrónico es requerido.',
            'role.exists' => 'El rol seleccionado no existe.',
        ]);

        try {
            $user = new User();
            $user->uid = $this->generate_uid('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->available = (bool)$request->available;
            $user->shop_id = $this->shouldAssignShop($request->role) ? $request->shop : null;
            $user->save();

            // Assign role using Spatie relationship
            $role = SpatieRole::findOrFail($request->role);
            $user->assignRole($role->name);

            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'assigned_role' => $role->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function view($uid)
    {
        $user = User::uid($uid);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        $shops = Shop::get()->pluck('title', 'id');
        $roles = SpatieRole::orderBy('name')->pluck('name', 'id');

        return view('managers.views.users.users.view')->with([
            'user' => $user,
            'shops' => $shops,
            'roles' => $roles,
            'userRoles' => $user->getRoleNames()->toArray(),
            'rolesRequiringAssignment' => $this->rolesRequiringAssignment,
        ]);
    }

    public function edit($uid)
    {
        $user = User::uid($uid);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        $shops = Shop::get()->pluck('title', 'id');
        $roles = SpatieRole::orderBy('name')->pluck('name', 'id');

        return view('managers.views.users.users.edit')->with([
            'user' => $user,
            'shops' => $shops,
            'roles' => $roles,
            'userRoles' => $user->getRoleNames()->toArray(),
            'rolesRequiringAssignment' => $this->rolesRequiringAssignment,
        ]);
    }

    public function update(Request $request)
    {
        $user = User::where('uid', $request->uid)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Validate request data
        $request->validate([
            'firstname' => 'required|string|min:3|max:100',
            'lastname' => 'required|string|min:3|max:100',
            'email' => "required|email|unique:users,email,{$user->id}",
            'available' => 'required|boolean',
            'role' => 'required|numeric|exists:roles,id',
            'shop' => 'nullable|numeric|exists:shops,id',
            'password' => 'nullable|string|min:6',
        ], [
            'email.unique' => 'El correo electrónico ya está registrado en otro usuario.',
            'role.exists' => 'El rol seleccionado no existe.',
        ]);

        try {
            // Update basic user data
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }

            $user->available = (bool)$request->available;
            $user->shop_id = $this->shouldAssignShop($request->role) ? $request->shop : null;
            $user->save();

            // Update role
            $role = SpatieRole::findOrFail($request->role);
            $user->syncRoles([$role->name]);

            Log::info('Usuario actualizado exitosamente', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'assigned_role' => $role->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($uid)
    {
        $user = User::uid($uid);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        try {
            Log::info('Usuario eliminado', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'assigned_roles' => $user->getRoleNames()->toArray(),
            ]);

            $user->delete();

            return redirect()->route('manager.users')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('manager.users')
                ->with('error', 'Error al eliminar el usuario.');
        }
    }

    /**
     * Determine if a shop should be assigned to a user based on their role
     */
    private function shouldAssignShop($roleId): bool
    {
        $role = SpatieRole::find($roleId);

        if (!$role) {
            return false;
        }

        return in_array($role->name, $this->rolesRequiringAssignment);
    }
}
