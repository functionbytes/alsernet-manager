<?php

namespace App\Http\Controllers\Managers\Settings\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Supplier\SupplierAiContent;
use App\Services\Supplier\ContentGenerationService;
use App\Services\Supplier\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierContentController extends Controller
{
    public function __construct(
        protected ContentGenerationService $contentService,
        protected SyncService $syncService
    ) {}

    /**
     * Display pending content review list
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Revisión de Contenido de IA';
        $breadcrumb = 'Configuración / Proveedores / Contenido';

        $statusFilter = $request->input('status', 'pending');

        return view('managers.views.settings.suppliers.content.index', compact('pageTitle', 'breadcrumb', 'statusFilter'));
    }

    /**
     * Get content data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $query = SupplierAiContent::query()
                ->with(['supplier', 'prompt', 'extractionResult', 'validation']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('meta_title', 'like', "%{$search}%");
                });
            }

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($supplierId = $request->input('supplier_id')) {
                $query->where('supplier_id', $supplierId);
            }

            $totalRecords = SupplierAiContent::count();
            $filteredRecords = $query->count();

            $contents = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $contents,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting content data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de contenido',
            ], 500);
        }
    }

    /**
     * Show content detail view
     */
    public function show(string $uid): View
    {
        $content = SupplierAiContent::where('uid', $uid)
            ->with(['supplier', 'prompt', 'extractionResult', 'validation', 'cost'])
            ->firstOrFail();

        $pageTitle = "Revisión de Contenido: {$content->title}";
        $breadcrumb = 'Configuración / Proveedores / Contenido / Detalle';

        return view('managers.views.settings.suppliers.content.show', compact('content', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Approve content
     */
    public function approve(Request $request, string $uid): JsonResponse
    {
        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();

            if ($content->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este contenido ya ha sido aprobado',
                ], 400);
            }

            $result = $this->contentService->approveContent($content, [
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'content' => $result['content'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el contenido: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject content with reason
     */
    public function reject(Request $request, string $uid): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();

            if ($content->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este contenido ya ha sido rechazado',
                ], 400);
            }

            $result = $this->contentService->rejectContent($content, [
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->input('reason'),
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el contenido: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate content with AI
     */
    public function regenerate(Request $request, string $uid): JsonResponse
    {
        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();

            $result = $this->contentService->regenerateContent($content, [
                'prompt_id' => $request->input('prompt_id'),
                'keep_approved_sections' => $request->boolean('keep_approved_sections', false),
                'regeneration_notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'content' => $result['content'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error regenerating content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar el contenido: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish content to PrestaShop
     */
    public function publish(Request $request, string $uid): JsonResponse
    {
        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();

            if ($content->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede publicar contenido aprobado',
                ], 400);
            }

            $result = $this->syncService->publishContentToPrestaShop($content, [
                'update_existing' => $request->boolean('update_existing', false),
                'published_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'prestashop_id' => $result['prestashop_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error publishing content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al publicar el contenido: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk approve content
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'content_uids' => 'required|array',
            'content_uids.*' => 'required|string',
        ]);

        try {
            $results = [];

            foreach ($request->input('content_uids') as $uid) {
                $content = SupplierAiContent::where('uid', $uid)->first();

                if ($content && $content->status !== 'approved') {
                    $result = $this->contentService->approveContent($content, [
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'bulk_action' => true,
                    ]);

                    $results[] = [
                        'uid' => $uid,
                        'success' => $result['success'],
                    ];
                }
            }

            $successCount = collect($results)->where('success', true)->count();

            return response()->json([
                'success' => true,
                'message' => "Se aprobaron {$successCount} de {count($results)} contenidos",
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk approving content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el contenido en lote: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get content validation results
     */
    public function getValidation(string $uid): JsonResponse
    {
        try {
            $content = SupplierAiContent::where('uid', $uid)
                ->with('validation')
                ->firstOrFail();

            $validation = $content->validation;

            return response()->json([
                'success' => true,
                'validation' => [
                    'is_valid' => $validation->is_valid ?? false,
                    'quality_score' => $validation->quality_score ?? 0,
                    'readability_score' => $validation->readability_score ?? 0,
                    'seo_score' => $validation->seo_score ?? 0,
                    'issues' => $validation->issues ?? [],
                    'warnings' => $validation->warnings ?? [],
                    'suggestions' => $validation->suggestions ?? [],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting validation: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la validación del contenido',
            ], 500);
        }
    }

    /**
     * Get content cost information
     */
    public function getCost(string $uid): JsonResponse
    {
        try {
            $content = SupplierAiContent::where('uid', $uid)
                ->with('cost')
                ->firstOrFail();

            $cost = $content->cost;

            return response()->json([
                'success' => true,
                'cost' => [
                    'input_tokens' => $cost->input_tokens ?? 0,
                    'output_tokens' => $cost->output_tokens ?? 0,
                    'total_tokens' => $cost->total_tokens ?? 0,
                    'cost' => $cost->total_cost ?? 0,
                    'model' => $cost->model ?? 'N/A',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cost: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el costo del contenido',
            ], 500);
        }
    }
}
