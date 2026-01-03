<?php

namespace Modules\Supplier\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Prestashop\Services\SupplierSyncService;
use Modules\Supplier\Entities\SupplierAiContent;
use Modules\Supplier\Services\ContentGenerationService;

class SupplierContentController extends Controller
{
    public function __construct(
        protected ContentGenerationService $contentService,
        protected ?SupplierSyncService $syncService = null
    ) {}

    /**
     * Display pending content review list
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Revisión de Contenido de IA';
        $breadcrumb = 'Configuración / Proveedores / Contenido';

        // Build query
        $query = SupplierAiContent::query()->with(['supplier', 'prompt']);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('generated_name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('model_id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Apply supplier filter
        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        // Get paginated content
        $contents = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get suppliers for filter dropdown
        $suppliers = \App\Models\Supplier\Supplier::orderBy('name')->get();

        // Get stats
        $stats = [
            'total' => SupplierAiContent::count(),
            'pending' => SupplierAiContent::where('status', 'pending')->count(),
            'approved' => SupplierAiContent::where('status', 'approved')->count(),
            'rejected' => SupplierAiContent::where('status', 'rejected')->count(),
        ];

        return view('theme.views.settings.suppliers.content.index', compact('pageTitle', 'breadcrumb', 'contents', 'suppliers', 'stats'));
    }

    /**
     * Get content data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $query = SupplierAiContent::query()
                ->with(['supplier', 'prompt']);

            // Search filter
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('generated_name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('erp_reference', 'like', "%{$search}%")
                        ->orWhere('model_id', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            // Supplier filter
            if ($supplierId = $request->input('supplier_id')) {
                $query->where('supplier_id', $supplierId);
            }

            $totalRecords = SupplierAiContent::count();
            $filteredRecords = $query->count();

            // Map DataTables column index to actual column names
            $columns = ['id', 'generated_name', 'supplier_id', 'status', 'status', 'status', 'created_at'];
            $orderColumnIndex = $request->input('order.0.column', 6);
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $orderDir = $request->input('order.0.dir', 'desc');

            $contents = $query
                ->orderBy($orderColumn, $orderDir)
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get()
                ->map(function (SupplierAiContent $content) {
                    return [
                        'id' => $content->id,
                        'uid' => $content->uid,
                        'product' => $content->generated_name ?? $content->model_id ?? 'Sin nombre',
                        'supplier' => $content->supplier?->name ?? 'N/A',
                        'content_type' => $this->formatContentType($content),
                        'quality_score' => $this->formatQuality(0), // TODO: Implementar cálculo real
                        'status' => $this->formatStatus($content->status),
                        'created_at' => $content->created_at?->format('d/m/Y H:i') ?? 'N/A',
                        'actions' => $this->getActions($content),
                    ];
                });

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
     * Format content type for display
     */
    private function formatContentType(SupplierAiContent $content): string
    {
        $types = [];
        if ($content->generated_name) {
            $types[] = 'Nombre';
        }
        if ($content->short_description) {
            $types[] = 'Descripción';
        }
        if ($content->seo_title) {
            $types[] = 'SEO';
        }

        return ! empty($types) ? implode(', ', $types) : 'Completo';
    }

    /**
     * Format quality score for display
     */
    private function formatQuality(int $score): string
    {
        if ($score >= 80) {
            $badge = 'success';
        } elseif ($score >= 50) {
            $badge = 'warning';
        } else {
            $badge = 'danger';
        }

        return "<span class=\"badge bg-{$badge}\">{$score}%</span>";
    }

    /**
     * Format status for display
     */
    private function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => '<span class="badge bg-warning">Pendiente</span>',
            'approved' => '<span class="badge bg-success">Aprobado</span>',
            'rejected' => '<span class="badge bg-danger">Rechazado</span>',
            'published' => '<span class="badge bg-info">Publicado</span>',
            default => '<span class="badge bg-secondary">'.ucfirst($status).'</span>',
        };
    }

    /**
     * Get action buttons HTML
     */
    private function getActions(SupplierAiContent $content): string
    {
        $actions = '<div class="btn-group btn-group-sm" role="group">';

        $actions .= "
            <button type=\"button\" class=\"btn btn-outline-primary view-content\" data-id=\"{$content->uid}\" title=\"Ver detalle\">
                <i class=\"fas fa-eye\"></i>
            </button>";

        if ($content->status === 'pending') {
            $actions .= "
                <button type=\"button\" class=\"btn btn-outline-success approve-content\" data-id=\"{$content->uid}\" title=\"Aprobar\">
                    <i class=\"fas fa-check\"></i>
                </button>
                <button type=\"button\" class=\"btn btn-outline-danger reject-content\" data-id=\"{$content->uid}\" title=\"Rechazar\">
                    <i class=\"fas fa-times\"></i>
                </button>";
        }

        if ($content->status === 'approved') {
            $actions .= "
                <button type=\"button\" class=\"btn btn-outline-info publish-content\" data-id=\"{$content->uid}\" title=\"Publicar\">
                    <i class=\"fas fa-paper-plane\"></i>
                </button>";
        }

        $actions .= "
            <button type=\"button\" class=\"btn btn-outline-warning regenerate-content\" data-id=\"{$content->uid}\" title=\"Regenerar\">
                <i class=\"fas fa-redo\"></i>
            </button>";

        $actions .= '</div>';

        return $actions;
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

        return view('theme.views.settings.suppliers.content.show', compact('content', 'pageTitle', 'breadcrumb'));
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

    /**
     * Get stats for content review dashboard
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'pending' => SupplierAiContent::where('status', 'pending')->count(),
                'approved' => SupplierAiContent::where('status', 'approved')->count(),
                'rejected' => SupplierAiContent::where('status', 'rejected')->count(),
                'avg_quality' => 0,
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting stats: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
            ], 500);
        }
    }

    /**
     * Bulk action handler (approve, reject, regenerate)
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject,regenerate',
            'ids' => 'required|array',
            'ids.*' => 'string',
        ]);

        try {
            $action = $request->input('action');
            $ids = $request->input('ids');
            $successCount = 0;

            foreach ($ids as $id) {
                $content = SupplierAiContent::where('uid', $id)->first();
                if (! $content) {
                    continue;
                }

                $result = match ($action) {
                    'approve' => $this->contentService->approveContent($content, [
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]),
                    'reject' => $this->contentService->rejectContent($content, [
                        'rejected_by' => auth()->id(),
                        'rejected_at' => now(),
                        'rejection_reason' => 'Bulk rejection',
                    ]),
                    'regenerate' => $this->contentService->regenerateContent($content, [
                        'regeneration_notes' => 'Bulk regeneration',
                    ]),
                    default => ['success' => false],
                };

                if ($result['success']) {
                    $successCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se procesaron {$successCount} de ".count($ids).' contenidos',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk action: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar la acción masiva: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit content inline
     */
    public function edit(Request $request, string $uid): JsonResponse
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();
            $field = $request->input('field');
            $value = $request->input('value');

            if (in_array($field, ['title', 'description', 'meta_title', 'meta_description'])) {
                $content->update([$field => $value]);

                return response()->json([
                    'success' => true,
                    'message' => 'Contenido actualizado correctamente',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Campo no permitido para edición',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error editing content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al editar el contenido: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Filter content by status
     */
    public function filterByStatus(string $status): JsonResponse
    {
        try {
            $validStatuses = ['pending', 'approved', 'rejected', 'published'];

            if (! in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado inválido',
                ], 400);
            }

            $contents = SupplierAiContent::where('status', $status)
                ->with(['supplier', 'prompt', 'validation'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contents,
            ]);
        } catch (\Exception $e) {
            Log::error('Error filtering content: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar el contenido',
            ], 500);
        }
    }

    /**
     * Perform action on single content (approve, reject, regenerate, etc.)
     */
    public function action(Request $request, string $uid): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject,regenerate',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $content = SupplierAiContent::where('uid', $uid)->firstOrFail();
            $action = $request->input('action');

            $result = match ($action) {
                'approve' => $this->contentService->approveContent($content, [
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $request->input('notes'),
                ]),
                'reject' => $this->contentService->rejectContent($content, [
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now(),
                    'rejection_reason' => $request->input('reason', 'Manual rejection'),
                    'notes' => $request->input('notes'),
                ]),
                'regenerate' => $this->contentService->regenerateContent($content, [
                    'regeneration_notes' => $request->input('notes'),
                ]),
                default => ['success' => false, 'message' => 'Invalid action'],
            };

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Action completed',
                'content' => $result['content'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error performing action: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar la acción: '.$e->getMessage(),
            ], 500);
        }
    }
}
