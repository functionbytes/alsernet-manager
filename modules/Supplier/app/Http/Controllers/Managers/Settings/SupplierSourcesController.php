<?php

namespace Modules\Supplier\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Suppliers\StoreSupplierSourceRequest;
use App\Http\Requests\Managers\Settings\Suppliers\UpdateSupplierSourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Services\SourceConfigurationService;

class SupplierSourcesController extends Controller
{
    public function __construct(protected SourceConfigurationService $sourceConfigService) {}

    /**
     * Display sources list for supplier
     */
    public function index(Request $request, string $supplierUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

        $query = $supplier->sources();

        // Search by label or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by source type
        if ($sourceType = $request->input('source_type')) {
            $query->where('source_type', $sourceType);
        }

        // Filter by active status
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by priority desc
        $sources = $query->orderBy('priority', 'desc')
            ->orderBy('label', 'asc')
            ->paginate(15)
            ->withQueryString();

        $pageTitle = "Fuentes de {$supplier->name}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes";

        return view('theme.views.settings.suppliers.sources.index', compact('supplier', 'sources', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Get sources data for DataTables
     */
    public function getData(Request $request, string $supplierUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $query = $supplier->sources();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('source_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $totalRecords = $supplier->sources()->count();
            $filteredRecords = $query->count();

            // Get column name for ordering (map to actual DB column names)
            $columns = ['source_type', 'label', 'description', 'is_active', 'priority', 'last_accessed_at', 'actions'];
            $orderColumn = $columns[$request->input('order.0.column', 4)] ?? 'priority';
            $orderDir = $request->input('order.0.dir', 'desc');

            $sources = $query
                ->orderBy($orderColumn, $orderDir)
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get()
                ->map(function ($source) {
                    return [
                        'source_type' => $this->getSourceTypeBadge($source->source_type),
                        'name' => $source->label,
                        'url' => $source->description ?? '<span class="text-muted">-</span>',
                        'is_active' => $source->is_active
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-secondary">Inactivo</span>',
                        'priority' => '<span class="badge bg-primary">'.$source->priority.'</span>',
                        'last_accessed_at' => $source->last_accessed_at
                            ? $source->last_accessed_at->format('d/m/Y H:i')
                            : '<span class="text-muted">Nunca</span>',
                        'actions' => $this->getActionsHtml($source),
                    ];
                });

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $sources,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting supplier sources data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de fuentes',
            ], 500);
        }
    }

    /**
     * Get source type badge HTML
     */
    private function getSourceTypeBadge(string $type): string
    {
        $badges = [
            'website' => '<span class="badge bg-info"><i class="fas fa-globe me-1"></i>Web</span>',
            'ftp' => '<span class="badge bg-warning"><i class="fas fa-server me-1"></i>FTP</span>',
            'sftp' => '<span class="badge bg-warning"><i class="fas fa-server me-1"></i>SFTP</span>',
            'api' => '<span class="badge bg-primary"><i class="fas fa-code me-1"></i>API</span>',
            'upload' => '<span class="badge bg-secondary"><i class="fas fa-upload me-1"></i>Upload</span>',
            'email' => '<span class="badge bg-success"><i class="fas fa-envelope me-1"></i>Email</span>',
        ];

        return $badges[$type] ?? '<span class="badge bg-light text-dark">'.$type.'</span>';
    }

    /**
     * Get actions dropdown HTML
     */
    private function getActionsHtml($source): string
    {
        $editUrl = route('manager.settings.suppliers.sources.edit', [$source->supplier->uid, $source->uid]);

        return '
            <div class="dropdown dropstart">
                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                    <i class="fa-duotone fa-solid fa-ellipsis"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button class="dropdown-item test-source" data-uid="'.$source->uid.'">
                            <i class="fas fa-flask me-2"></i>Probar conexión
                        </button>
                    </li>
                    <li>
                        <a class="dropdown-item" href="'.$editUrl.'">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item text-danger delete-source"
                                data-uid="'.$source->uid.'"
                                data-name="'.$source->label.'">
                            <i class="fas fa-trash me-2"></i>Eliminar
                        </button>
                    </li>
                </ul>
            </div>
        ';
    }

    /**
     * Show create source form
     */
    public function create(string $supplierUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
        $pageTitle = "Crear Fuente para {$supplier->name}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes / Crear";

        return view('theme.views.settings.suppliers.sources.create', compact('supplier', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Store new source
     */
    public function store(StoreSupplierSourceRequest $request, string $supplierUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

            $source = $supplier->sources()->create([
                'label' => $request->label ?? $request->name,
                'source_type' => $request->source_type ?? $request->type,
                'description' => $request->description,
                'trust_level' => $request->trust_level ?? 'medium',
                'usage_notes' => $request->usage_notes,
                'priority' => $request->priority ?? 10,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Create configuration if provided
            if ($request->has('configuration')) {
                $this->sourceConfigService->createConfiguration($source, $request->configuration);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fuente creada exitosamente',
                'source' => $source->load('configuration'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating supplier source: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la fuente: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit source form
     */
    public function edit(string $supplierUid, string $sourceUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
        $source = $supplier->sources()->where('uid', $sourceUid)->firstOrFail();
        $pageTitle = "Editar Fuente: {$source->label}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes / Editar";

        return view('theme.views.settings.suppliers.sources.edit', compact('supplier', 'source', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Update source
     */
    public function update(UpdateSupplierSourceRequest $request, string $supplierUid, string $sourceUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $source = $supplier->sources()->where('uid', $sourceUid)->firstOrFail();

            $source->update([
                'label' => $request->label ?? $request->name,
                'source_type' => $request->source_type ?? $request->type,
                'description' => $request->description,
                'trust_level' => $request->trust_level ?? $source->trust_level,
                'usage_notes' => $request->usage_notes,
                'priority' => $request->priority ?? $source->priority,
                'is_active' => $request->boolean('is_active'),
            ]);

            // Update configuration if provided
            if ($request->has('configuration')) {
                $this->sourceConfigService->updateConfiguration($source, $request->configuration);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fuente actualizada exitosamente',
                'source' => $source->fresh()->load('configuration'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating supplier source: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la fuente: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete source
     */
    public function destroy(string $supplierUid, string $sourceUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $source = $supplier->sources()->where('uid', $sourceUid)->firstOrFail();

            // Check if source has active automations
            if ($source->automationWorkflows()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la fuente porque tiene automatizaciones activas',
                ], 409);
            }

            $source->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fuente eliminada exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting supplier source: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la fuente: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test source connection
     */
    public function testConnection(string $supplierUid, string $sourceUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $source = $supplier->sources()->where('uid', $sourceUid)->firstOrFail();

            $result = $this->sourceConfigService->testConnection($source);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'response_time' => $result['response_time'] ?? null,
                'status_code' => $result['status_code'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error testing source connection: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al probar la conexión: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get source health status
     */
    public function getHealth(string $supplierUid, string $sourceUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $source = $supplier->sources()->where('uid', $sourceUid)->firstOrFail();

            $health = [
                'status' => $source->monitor->health_status ?? 'unknown',
                'uptime_percentage' => $source->monitor->uptime_percentage ?? 0,
                'last_check' => $source->monitor->last_checked_at?->diffForHumans(),
                'last_success' => $source->monitor->last_successful_at?->diffForHumans(),
                'consecutive_failures' => $source->monitor->consecutive_failures ?? 0,
                'average_response_time' => $source->monitor->average_response_time ?? 0,
            ];

            return response()->json([
                'success' => true,
                'health' => $health,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting source health: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de salud de la fuente',
            ], 500);
        }
    }
}
