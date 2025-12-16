<?php

namespace App\Http\Controllers\Managers\Settings\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentRequirement;
use App\Models\Document\DocumentType;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DocumentTypeController
 *
 * Manages document types. Translations are now handled via language files
 * in resources/lang/{locale}/documents.php instead of database tables.
 */
class DocumentTypeController extends Controller
{
    /**
     * Display list of document types
     */
    public function index()
    {
        $documentTypes = DocumentType::with('requirements')
            ->orderBy('sort_order')
            ->orderBy('slug')
            ->get();

        $langs = Lang::all();

        return view('managers.views.settings.documents.types.index', [
            'documentTypes' => $documentTypes,
            'langs' => $langs,
        ]);
    }

    /**
     * Show form to create new document type
     */
    public function create()
    {
        return view('managers.views.settings.documents.types.create');
    }

    /**
     * Store new document type
     *
     * Note: Labels and descriptions should be added to language files:
     * resources/lang/{locale}/documents.php
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:document_types,slug|max:50|regex:/^[a-z0-9_-]+$/',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'sla_multiplier' => 'nullable|numeric|min:0|max:100',

            // Requirements
            'requirements' => 'nullable|array',
            'requirements.*.key' => 'required|string|max:50',
            'requirements.*.is_required' => 'nullable|boolean',
            'requirements.*.accepts_multiple' => 'nullable|boolean',
            'requirements.*.max_file_size' => 'nullable|integer|min:1',
            'requirements.*.allowed_extensions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Create document type
            $documentType = DocumentType::create([
                'slug' => $validated['slug'],
                'icon' => $validated['icon'] ?? 'fas fa-file',
                'color' => $validated['color'] ?? '#6c757d',
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
                'sla_multiplier' => $validated['sla_multiplier'] ?? 1.0,
            ]);

            // Create requirements if any
            if (isset($validated['requirements']) && is_array($validated['requirements'])) {
                foreach ($validated['requirements'] as $index => $requirementData) {
                    DocumentRequirement::create([
                        'document_type_id' => $documentType->id,
                        'key' => $requirementData['key'],
                        'is_required' => $requirementData['is_required'] ?? true,
                        'accepts_multiple' => $requirementData['accepts_multiple'] ?? false,
                        'max_file_size' => $requirementData['max_file_size'] ?? 10240,
                        'allowed_extensions' => $requirementData['allowed_extensions'] ?? ['pdf', 'jpg', 'jpeg', 'png'],
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('manager.settings.documents.types')
                ->with('success', "Tipo de documento '{$validated['slug']}' creado exitosamente. Recuerde agregar las traducciones en resources/lang/{locale}/documents.php");
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error creating document type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el tipo de documento: '.$e->getMessage());
        }
    }

    /**
     * Show form to edit document type
     */
    public function edit(string $documentType)
    {
        // Support both slug and uid
        $type = DocumentType::where('slug', $documentType)
            ->orWhere('uid', $documentType)
            ->with('requirements')
            ->firstOrFail();

        $langs = Lang::all();

        return view('managers.views.settings.documents.types.edit', [
            'documentType' => $type,
            'langs' => $langs,
        ]);
    }

    /**
     * Update document type
     *
     * Note: Labels and descriptions should be updated in language files:
     * resources/lang/{locale}/documents.php
     */
    public function update(Request $request, string $documentType)
    {
        // Support both slug and uid
        $type = DocumentType::where('slug', $documentType)
            ->orWhere('uid', $documentType)
            ->firstOrFail();

        $validated = $request->validate([
            'slug' => 'required|string|max:50|regex:/^[a-z0-9_-]+$/|unique:document_types,slug,'.$type->id,
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'sla_multiplier' => 'nullable|numeric|min:0|max:100',

            // Requirements
            'requirements' => 'nullable|array',
            'requirements.*.id' => 'nullable|exists:document_requirements,id',
            'requirements.*.key' => 'required|string|max:50',
            'requirements.*.is_required' => 'nullable|boolean',
            'requirements.*.accepts_multiple' => 'nullable|boolean',
            'requirements.*.max_file_size' => 'nullable|integer|min:1',
            'requirements.*.allowed_extensions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Update document type
            $type->update([
                'slug' => $validated['slug'],
                'icon' => $validated['icon'] ?? 'fas fa-file',
                'color' => $validated['color'] ?? '#6c757d',
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
                'sla_multiplier' => $validated['sla_multiplier'] ?? 1.0,
            ]);

            // Get existing requirement IDs
            $existingRequirementIds = $type->requirements->pluck('id')->toArray();
            $updatedRequirementIds = [];

            // Update or create requirements
            if (isset($validated['requirements']) && is_array($validated['requirements'])) {
                foreach ($validated['requirements'] as $index => $requirementData) {
                    if (isset($requirementData['id']) && in_array($requirementData['id'], $existingRequirementIds)) {
                        // Update existing requirement
                        $requirement = DocumentRequirement::find($requirementData['id']);
                        $requirement->update([
                            'key' => $requirementData['key'],
                            'is_required' => $requirementData['is_required'] ?? true,
                            'accepts_multiple' => $requirementData['accepts_multiple'] ?? false,
                            'max_file_size' => $requirementData['max_file_size'] ?? 10240,
                            'allowed_extensions' => $requirementData['allowed_extensions'] ?? ['pdf', 'jpg', 'jpeg', 'png'],
                            'sort_order' => $index,
                        ]);
                        $updatedRequirementIds[] = $requirement->id;
                    } else {
                        // Create new requirement
                        $requirement = DocumentRequirement::create([
                            'document_type_id' => $type->id,
                            'key' => $requirementData['key'],
                            'is_required' => $requirementData['is_required'] ?? true,
                            'accepts_multiple' => $requirementData['accepts_multiple'] ?? false,
                            'max_file_size' => $requirementData['max_file_size'] ?? 10240,
                            'allowed_extensions' => $requirementData['allowed_extensions'] ?? ['pdf', 'jpg', 'jpeg', 'png'],
                            'sort_order' => $index,
                        ]);
                        $updatedRequirementIds[] = $requirement->id;
                    }
                }
            }

            // Delete removed requirements
            $requirementsToDelete = array_diff($existingRequirementIds, $updatedRequirementIds);
            if (! empty($requirementsToDelete)) {
                DocumentRequirement::whereIn('id', $requirementsToDelete)->delete();
            }

            DB::commit();

            return redirect()
                ->route('manager.settings.documents.types')
                ->with('success', 'Tipo de documento actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating document type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el tipo de documento: '.$e->getMessage());
        }
    }

    /**
     * Delete document type
     */
    public function destroy(string $documentType)
    {
        // Support both slug and uid
        $type = DocumentType::where('slug', $documentType)
            ->orWhere('uid', $documentType)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Delete requirements (cascade will handle this if configured)
            $type->requirements()->delete();

            // Delete the type
            $type->delete();

            DB::commit();

            return redirect()
                ->route('manager.settings.documents.types')
                ->with('success', 'Tipo de documento eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error deleting document type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el tipo de documento: '.$e->getMessage());
        }
    }

    /**
     * Export all document types configuration
     */
    public function export()
    {
        $documentTypes = DocumentType::with('requirements')
            ->get()
            ->map(function ($type) {
                return [
                    'slug' => $type->slug,
                    'icon' => $type->icon,
                    'color' => $type->color,
                    'is_active' => $type->is_active,
                    'sort_order' => $type->sort_order,
                    'sla_multiplier' => $type->sla_multiplier,
                    'label' => $type->getLabel(),
                    'description' => $type->getDescription(),
                    'instructions' => $type->getInstructions(),
                    'requirements' => $type->requirements->map(function ($req) {
                        return [
                            'key' => $req->key,
                            'is_required' => $req->is_required,
                            'accepts_multiple' => $req->accepts_multiple,
                            'max_file_size' => $req->max_file_size,
                            'allowed_extensions' => $req->allowed_extensions,
                            'name' => $req->getName(),
                            'help_text' => $req->getHelpText(),
                        ];
                    }),
                ];
            });

        return response()->json($documentTypes, 200, [
            'Content-Disposition' => 'attachment; filename="document-types-'.now()->format('Y-m-d-His').'.json"',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(string $documentType)
    {
        $type = DocumentType::where('slug', $documentType)
            ->orWhere('uid', $documentType)
            ->firstOrFail();

        try {
            $type->update(['is_active' => ! $type->is_active]);

            return redirect()
                ->back()
                ->with('success', 'Estado actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar el estado: '.$e->getMessage());
        }
    }

    /**
     * Show translation management page
     *
     * Redirects to barryvdh/laravel-translation-manager for editing translations
     */
    public function translations()
    {
        return redirect()
            ->route('translations.index')
            ->with('info', 'Use el gestor de traducciones para editar las etiquetas y descripciones de los tipos de documentos.');
    }
}
