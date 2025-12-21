<?php

namespace App\Http\Controllers\Managers\Settings\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Suppliers\StoreSupplierPromptRequest;
use App\Http\Requests\Managers\Settings\Suppliers\UpdateSupplierPromptRequest;
use App\Models\Supplier\SupplierPrompt;
use App\Services\Supplier\ContentGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierPromptsController extends Controller
{
    public function __construct(protected ContentGenerationService $contentService) {}

    /**
     * Display AI prompts list
     */
    public function index(): View
    {
        $pageTitle = 'Gestión de Prompts de IA';
        $breadcrumb = 'Configuración / Proveedores / Prompts';

        return view('managers.views.settings.suppliers.prompts.index', compact('pageTitle', 'breadcrumb'));
    }

    /**
     * Get prompts data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $query = SupplierPrompt::query()->with(['supplier']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            }

            if ($type = $request->input('type')) {
                $query->where('type', $type);
            }

            if ($category = $request->input('category')) {
                $query->where('category', $category);
            }

            $totalRecords = SupplierPrompt::count();
            $filteredRecords = $query->count();

            $prompts = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $prompts,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting prompts data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de prompts',
            ], 500);
        }
    }

    /**
     * Show create prompt form
     */
    public function create(): View
    {
        $pageTitle = 'Crear Prompt de IA';
        $breadcrumb = 'Configuración / Proveedores / Prompts / Crear';

        return view('managers.views.settings.suppliers.prompts.create', compact('pageTitle', 'breadcrumb'));
    }

    /**
     * Store new prompt
     */
    public function store(StoreSupplierPromptRequest $request): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::create([
                'supplier_id' => $request->supplier_id,
                'name' => $request->name,
                'type' => $request->type,
                'category' => $request->category,
                'system_prompt' => $request->system_prompt,
                'user_prompt' => $request->user_prompt,
                'variables' => $request->variables ?? [],
                'model' => $request->model ?? 'gpt-4',
                'temperature' => $request->temperature ?? 0.7,
                'max_tokens' => $request->max_tokens ?? 2000,
                'is_active' => $request->boolean('is_active', true),
                'version' => 1,
                'metadata' => $request->metadata ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prompt creado exitosamente',
                'prompt' => $prompt,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el prompt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit prompt form
     */
    public function edit(string $uid): View
    {
        $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
        $pageTitle = "Editar Prompt: {$prompt->name}";
        $breadcrumb = 'Configuración / Proveedores / Prompts / Editar';

        return view('managers.views.settings.suppliers.prompts.edit', compact('prompt', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Update prompt
     */
    public function update(UpdateSupplierPromptRequest $request, string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            // Increment version if content changed
            $incrementVersion = $prompt->system_prompt !== $request->system_prompt
                || $prompt->user_prompt !== $request->user_prompt;

            $prompt->update([
                'supplier_id' => $request->supplier_id,
                'name' => $request->name,
                'type' => $request->type,
                'category' => $request->category,
                'system_prompt' => $request->system_prompt,
                'user_prompt' => $request->user_prompt,
                'variables' => $request->variables ?? $prompt->variables,
                'model' => $request->model,
                'temperature' => $request->temperature,
                'max_tokens' => $request->max_tokens,
                'is_active' => $request->boolean('is_active'),
                'version' => $incrementVersion ? $prompt->version + 1 : $prompt->version,
                'metadata' => $request->metadata ?? $prompt->metadata,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prompt actualizado exitosamente',
                'prompt' => $prompt->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el prompt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete prompt
     */
    public function destroy(string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            // Check if prompt is being used in active workflows
            if ($prompt->automationWorkflows()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el prompt porque está siendo usado en flujos de trabajo activos',
                ], 409);
            }

            $prompt->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prompt eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el prompt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview prompt with sample data
     */
    public function preview(Request $request, string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
            $sampleData = $request->input('sample_data', []);

            $preview = $this->contentService->previewPrompt($prompt, $sampleData);

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al previsualizar el prompt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate prompt
     */
    public function duplicate(string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            $newPrompt = $prompt->replicate();
            $newPrompt->name = $prompt->name.' (Copia)';
            $newPrompt->version = 1;
            $newPrompt->is_active = false;
            $newPrompt->save();

            return response()->json([
                'success' => true,
                'message' => 'Prompt duplicado exitosamente',
                'prompt' => $newPrompt,
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar el prompt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get prompt performance metrics
     */
    public function getMetrics(string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            $metrics = [
                'total_uses' => $prompt->aiContents()->count(),
                'approved_count' => $prompt->aiContents()->where('status', 'approved')->count(),
                'rejected_count' => $prompt->aiContents()->where('status', 'rejected')->count(),
                'average_tokens' => $prompt->aiContents()->avg('tokens_used') ?? 0,
                'total_cost' => $prompt->aiCosts()->sum('total_cost') ?? 0,
                'average_quality_score' => $prompt->aiContents()->avg('quality_score') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting prompt metrics: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas del prompt',
            ], 500);
        }
    }
}
