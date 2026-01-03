<?php

namespace Modules\Supplier\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Supplier\Entities\SupplierPrompt;
use Modules\Supplier\Services\ContentGenerationService;

class SupplierPromptsController extends Controller
{
    public function __construct(protected ContentGenerationService $contentService) {}

    /**
     * Display AI prompts list
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Gestión de Prompts de IA';
        $breadcrumb = 'Configuración / Proveedores / Prompts';

        // Build query
        $query = SupplierPrompt::query()->with(['supplier']);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('scope', 'like', "%{$search}%")
                    ->orWhere('tone', 'like', "%{$search}%")
                    ->orWhere('content_type', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        // Apply scope filter
        if ($scope = $request->input('scope')) {
            $query->where('scope', $scope);
        }

        // Get paginated prompts
        $prompts = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('supplier::settings.prompts.index', compact('pageTitle', 'breadcrumb', 'prompts'));
    }

    /**
     * Show create prompt form
     */
    public function create(): View
    {
        $pageTitle = 'Crear Prompt de IA';
        $breadcrumb = 'Configuración / Proveedores / Prompts / Crear';

        $suppliers = \App\Models\Supplier\Supplier::orderBy('name')->get();

        return view('supplier::settings.prompts.create', compact('pageTitle', 'breadcrumb', 'suppliers'));
    }

    /**
     * Store new prompt
     */
    public function store(Request $request)
    {
        try {
            $prompt = SupplierPrompt::create([
                'supplier_id' => $request->supplier_id,
                'category_id' => $request->category_id,
                'label' => $request->label,
                'scope' => $request->scope ?? 'global',
                'content_type' => $request->content_type ?? 'description',
                'prompt_template' => $request->prompt_template,
                'output_language' => $request->output_language ?? 'es',
                'tone' => $request->tone ?? 'professional',
                'priority' => $request->priority ?? 0,
                'seo_focus' => $request->boolean('seo_focus', false),
                'is_active' => $request->boolean('is_active', true),
                'version' => 1,
            ]);

            return redirect()->route('manager.backups.suppliers.prompts.index')
                ->with('success', 'Prompt creado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error creating prompt: '.$e->getMessage());

            return back()->with('error', 'Error al crear el prompt: '.$e->getMessage());
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
        $prompt = SupplierPrompt::where('uid', $uid)->with(['supplier'])->firstOrFail();
        $pageTitle = "Editar Prompt: {$prompt->label}";
        $breadcrumb = 'Configuración / Proveedores / Prompts / Editar';

        $suppliers = \App\Models\Supplier\Supplier::orderBy('name')->get();

        return view('supplier::settings.prompts.edit', compact('prompt', 'pageTitle', 'breadcrumb', 'suppliers'));
    }

    /**
     * Update prompt
     */
    public function update(Request $request, string $uid)
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            // Increment version if content changed
            $incrementVersion = $prompt->prompt_template !== $request->prompt_template;

            $prompt->update([
                'supplier_id' => $request->supplier_id,
                'category_id' => $request->category_id,
                'label' => $request->label,
                'scope' => $request->scope,
                'content_type' => $request->content_type,
                'prompt_template' => $request->prompt_template,
                'output_language' => $request->output_language,
                'tone' => $request->tone,
                'priority' => $request->priority,
                'seo_focus' => $request->boolean('seo_focus'),
                'is_active' => $request->boolean('is_active'),
                'version' => $incrementVersion ? $prompt->version + 1 : $prompt->version,
            ]);

            return redirect()->route('manager.backups.suppliers.prompts.index')
                ->with('success', 'Prompt actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating prompt: '.$e->getMessage());

            return back()->with('error', 'Error al actualizar el prompt: '.$e->getMessage());
        }
    }

    /**
     * Toggle prompt active status
     */
    public function toggle(string $uid)
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
            $prompt->is_active = ! $prompt->is_active;
            $prompt->save();

            return back()->with('success', $prompt->is_active
                ? 'Prompt activado exitosamente'
                : 'Prompt desactivado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error toggling prompt: '.$e->getMessage());

            return back()->with('error', 'Error al cambiar el estado del prompt');
        }
    }

    /**
     * Delete prompt
     */
    public function destroy(string $uid)
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();
            $prompt->delete();

            return back()->with('success', 'Prompt eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error deleting prompt: '.$e->getMessage());

            return back()->with('error', 'Error al eliminar el prompt');
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
    public function duplicate(string $uid)
    {
        try {
            $prompt = SupplierPrompt::where('uid', $uid)->firstOrFail();

            $newPrompt = $prompt->replicate();
            $newPrompt->label = $prompt->label.' (Copia)';
            $newPrompt->version = 1;
            $newPrompt->is_active = false;
            $newPrompt->save();

            return back()->with('success', 'Prompt duplicado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error duplicating prompt: '.$e->getMessage());

            return back()->with('error', 'Error al duplicar el prompt');
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
