<?php

namespace App\Http\Controllers\Managers\Settings\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Suppliers\StoreSupplierSourceRequest;
use App\Http\Requests\Managers\Settings\Suppliers\UpdateSupplierSourceRequest;
use App\Models\Supplier\Supplier;
use App\Services\Supplier\SourceConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierSourcesController extends Controller
{
    public function __construct(protected SourceConfigurationService $sourceConfigService) {}

    /**
     * Display sources list for supplier
     */
    public function index(string $supplierUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
        $pageTitle = "Fuentes de {$supplier->name}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes";

        return view('managers.views.settings.suppliers.sources.index', compact('supplier', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Get sources data for DataTables
     */
    public function getData(Request $request, string $supplierUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $query = $supplier->sources()->with(['configuration', 'monitor']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%");
                });
            }

            $totalRecords = $supplier->sources()->count();
            $filteredRecords = $query->count();

            $sources = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

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
     * Show create source form
     */
    public function create(string $supplierUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
        $pageTitle = "Crear Fuente para {$supplier->name}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes / Crear";

        return view('managers.views.settings.suppliers.sources.create', compact('supplier', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Store new source
     */
    public function store(StoreSupplierSourceRequest $request, string $supplierUid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

            $source = $supplier->sources()->create([
                'name' => $request->name,
                'type' => $request->type,
                'url' => $request->url,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'refresh_interval' => $request->refresh_interval ?? 3600,
                'timeout' => $request->timeout ?? 30,
                'retry_attempts' => $request->retry_attempts ?? 3,
                'retry_delay' => $request->retry_delay ?? 5,
                'metadata' => $request->metadata ?? [],
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
        $pageTitle = "Editar Fuente: {$source->name}";
        $breadcrumb = "Configuración / Proveedores / {$supplier->name} / Fuentes / Editar";

        return view('managers.views.settings.suppliers.sources.edit', compact('supplier', 'source', 'pageTitle', 'breadcrumb'));
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
                'name' => $request->name,
                'type' => $request->type,
                'url' => $request->url,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'refresh_interval' => $request->refresh_interval,
                'timeout' => $request->timeout,
                'retry_attempts' => $request->retry_attempts,
                'retry_delay' => $request->retry_delay,
                'metadata' => $request->metadata ?? $source->metadata,
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
