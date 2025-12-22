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

        // Get suppliers for filter
        $suppliers = \App\Models\Supplier\Supplier::orderBy('name')->get();

        // Get stats
        $stats = [
            'total' => SupplierPrompt::count(),
            'active' => SupplierPrompt::where('is_active', true)->count(),
            'global' => SupplierPrompt::whereNull('supplier_id')->count(),
            'supplier' => SupplierPrompt::whereNotNull('supplier_id')->count(),
            'category' => SupplierPrompt::whereNotNull('category_id')->distinct('category_id')->count(),
        ];

        return view('managers.views.settings.suppliers.prompts.index', compact('pageTitle', 'breadcrumb', 'suppliers', 'stats'));
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
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('scope', 'like', "%{$search}%")
                        ->orWhere('tone', 'like', "%{$search}%");
                });
            }

            if ($scope = $request->input('scope')) {
                $query->where('scope', $scope);
            }

            if ($supplierId = $request->input('supplier_id')) {
                $query->where('supplier_id', $supplierId);
            }

            $totalRecords = SupplierPrompt::count();
            $filteredRecords = $query->count();

            // Map DataTables column index to actual column names
            $columns = ['label', 'scope', 'supplier_id', 'tone', 'priority', 'is_active'];
            $orderColumnIndex = $request->input('order.0.column', 4);
            $orderColumn = $columns[$orderColumnIndex] ?? 'priority';
            $orderDir = $request->input('order.0.dir', 'desc');

            $prompts = $query
                ->orderBy($orderColumn, $orderDir)
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get()
                ->map(function (SupplierPrompt $prompt) {
                    return [
                        'uid' => $prompt->uid,
                        'name' => $prompt->label,
                        'scope' => $this->formatScope($prompt->scope),
                        'target' => $prompt->supplier?->name ?? ($prompt->category_id ? "Cat: {$prompt->category_id}" : 'Global'),
                        'type' => ucfirst($prompt->tone),
                        'priority' => $prompt->priority,
                        'is_active' => $this->formatStatus($prompt->is_active),
                        'actions' => $this->getActions($prompt),
                    ];
                });

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
     * Format scope value for display
     */
    private function formatScope(string $scope): string
    {
        return match ($scope) {
            'global' => 'Global',
            'supplier' => 'Proveedor',
            'category' => 'Categoría',
            'source' => 'Fuente',
            default => ucfirst($scope),
        };
    }

    /**
     * Format status for display
     */
    private function formatStatus(bool $isActive): string
    {
        $badge = $isActive ? 'success' : 'danger';
        $text = $isActive ? 'Activo' : 'Inactivo';

        return "<span class=\"badge bg-{$badge}\">{$text}</span>";
    }

    /**
     * Get action buttons HTML
     */
    private function getActions(SupplierPrompt $prompt): string
    {
        return "
            <div class=\"btn-group btn-group-sm\" role=\"group\">
                <button type=\"button\" class=\"btn btn-outline-primary edit-prompt\" data-id=\"{$prompt->uid}\" title=\"Editar\">
                    <i class=\"fas fa-edit\"></i>
                </button>
                <button type=\"button\" class=\"btn btn-outline-info preview-prompt\" data-id=\"{$prompt->uid}\" title=\"Vista previa\">
                    <i class=\"fas fa-eye\"></i>
                </button>
                <button type=\"button\" class=\"btn btn-outline-warning toggle-prompt\" data-id=\"{$prompt->uid}\" title=\"Cambiar estado\">
                    <i class=\"fas fa-power-off\"></i>
                </button>
                <button type=\"button\" class=\"btn btn-outline-secondary duplicate-prompt\" data-id=\"{$prompt->uid}\" title=\"Duplicar\">
                    <i class=\"fas fa-copy\"></i>
                </button>
                <button type=\"button\" class=\"btn btn-outline-danger delete-prompt\" data-id=\"{$prompt->uid}\" data-name=\"{$prompt->label}\" title=\"Eliminar\">
                    <i class=\"fas fa-trash\"></i>
                </button>
            </div>
        ";
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
                'label' => $request->label,
                'scope' => $request->scope ?? 'global',
                'prompt_template' => $request->prompt_template,
                'output_language' => $request->output_language ?? 'es',
                'tone' => $request->tone ?? 'professional',
                'priority' => $request->priority ?? 0,
                'seo_focus' => $request->boolean('seo_focus', false),
                'is_active' => $request->boolean('is_active', true),
                'version' => 1,
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
     * Show prompt details
     */
    public function show(string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)
                ->with(['supplier'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $prompt,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting prompt details: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles del prompt',
            ], 500);
        }
    }

    /**
     * Show edit prompt form
     */
    public function edit(string $uid): View
    {
        $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
        $pageTitle = "Editar Prompt: {$prompt->label}";
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
            $incrementVersion = $prompt->prompt_template !== $request->prompt_template;

            $prompt->update([
                'supplier_id' => $request->supplier_id,
                'label' => $request->label,
                'scope' => $request->scope,
                'prompt_template' => $request->prompt_template,
                'output_language' => $request->output_language,
                'tone' => $request->tone,
                'priority' => $request->priority,
                'seo_focus' => $request->boolean('seo_focus'),
                'is_active' => $request->boolean('is_active'),
                'version' => $incrementVersion ? $prompt->version + 1 : $prompt->version,
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
     * Toggle prompt active status
     */
    public function toggle(string $uid): JsonResponse
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
            $prompt->is_active = ! $prompt->is_active;
            $prompt->save();

            return response()->json([
                'success' => true,
                'is_active' => $prompt->is_active,
                'message' => $prompt->is_active
                    ? 'Prompt activado exitosamente'
                    : 'Prompt desactivado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling prompt: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del prompt: '.$e->getMessage(),
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
            $newPrompt->label = $prompt->label.' (Copia)';
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
