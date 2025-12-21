<?php

namespace App\Http\Controllers\Managers\Settings\Documents;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Validation\ValidatorGroup;
use Illuminate\Http\Request;

class DocumentGroupsController extends Controller
{
    /**
     * Display a listing of validator groups (used for document validation).
     */
    public function index(Request $request)
    {
        $query = ValidatorGroup::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->with('users')->ordered()->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => ValidatorGroup::count(),
            'active' => ValidatorGroup::where('is_active', true)->count(),
            'inactive' => ValidatorGroup::where('is_active', false)->count(),
            'default' => ValidatorGroup::where('is_default', true)->count(),
            'total_members' => \DB::table('validator_group_user')->distinct('user_id')->count('user_id'),
        ];

        return view('managers.views.settings.documents.groups.index', [
            'groups' => $groups,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new group.
     */
    public function create()
    {
        $users = User::where('available', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('managers.views.settings.documents.groups.create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'key' => 'required|string|max:100|unique:validator_groups,key|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string|max:1000',
            'assignment_mode' => 'required|in:manual,round_robin,load_balanced',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'user_priorities' => 'nullable|array',
            'user_priorities.*' => 'in:primary,backup',
        ], [
            'key.unique' => 'Esta clave ya está en uso por otro grupo.',
            'key.regex' => 'La clave solo puede contener letras minúsculas, números, guiones y guiones bajos.',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        $group = ValidatorGroup::create($validated);

        // Attach users with priorities
        if ($request->filled('users')) {
            $usersData = [];
            foreach ($request->users as $index => $userId) {
                $usersData[$userId] = [
                    'priority' => $request->user_priorities[$index] ?? 'primary',
                ];
            }
            $group->users()->attach($usersData);
        }

        return redirect()->route('manager.settings.documents.groups.index')
            ->with('success', 'Grupo de validación creado exitosamente.');
    }

    /**
     * Show the form for editing a group.
     */
    public function edit(ValidatorGroup $group)
    {
        $group->load('users');
        $users = User::where('available', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('managers.views.settings.documents.groups.edit', [
            'group' => $group,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified group.
     */
    public function update(Request $request, ValidatorGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'key' => 'required|string|max:100|regex:/^[a-z0-9_-]+$/|unique:validator_groups,key,'.$group->id,
            'description' => 'nullable|string|max:1000',
            'assignment_mode' => 'required|in:manual,round_robin,load_balanced',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'user_priorities' => 'nullable|array',
            'user_priorities.*' => 'in:primary,backup',
        ], [
            'key.unique' => 'Esta clave ya está en uso por otro grupo.',
            'key.regex' => 'La clave solo puede contener letras minúsculas, números, guiones y guiones bajos.',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');

        $group->update($validated);

        // Sync users with priorities
        if ($request->has('users')) {
            $usersData = [];
            if ($request->filled('users')) {
                foreach ($request->users as $index => $userId) {
                    $usersData[$userId] = [
                        'priority' => $request->user_priorities[$index] ?? 'primary',
                    ];
                }
            }
            $group->users()->sync($usersData);
        }

        return redirect()->route('manager.settings.documents.groups.index')
            ->with('success', 'Grupo de validación actualizado exitosamente.');
    }

    /**
     * Toggle the active status of a group.
     */
    public function toggle(ValidatorGroup $group)
    {
        $group->update(['is_active' => ! $group->is_active]);

        return back()->with('success', 'Estado del grupo actualizado exitosamente.');
    }

    /**
     * Remove the specified group.
     */
    public function destroy(ValidatorGroup $group)
    {
        // Check if group is default
        if ($group->is_default) {
            return back()->with('error', 'No se puede eliminar el grupo predeterminado.');
        }

        // Check if group has documents assigned (if applicable to your system)
        // Uncomment and adjust if you have documents assigned to groups
        // $documentsCount = \App\Models\Document\Document::where('group_id', $group->id)->count();
        // if ($documentsCount > 0) {
        //     return back()->with('error', 'No se puede eliminar un grupo que tiene documentos asignados.');
        // }

        $group->delete();

        return redirect()->route('manager.settings.documents.groups.index')
            ->with('success', 'Grupo de documentos eliminado exitosamente.');
    }

    /**
     * Reorder groups via drag and drop.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:validator_groups,id',
        ]);

        ValidatorGroup::reorder($validated['ids']);

        return response()->json(['success' => true, 'message' => 'Orden actualizado exitosamente.']);
    }
}
