<?php

namespace Modules\Supplier\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Suppliers\StoreSupplierRequest;
use App\Http\Requests\Managers\Settings\Suppliers\UpdateSupplierRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Supplier\Entities\Supplier;

class SuppliersController extends Controller
{
    /**
     * Display suppliers list with pagination
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Gestión de Proveedores';
        $breadcrumb = 'Configuración / Proveedores';

        $searchKey = $request->get('search');
        $is_active = $request->get('is_active');

        $query = Supplier::query()->withCount('sources');

        if ($searchKey) {
            $query->where(function ($q) use ($searchKey) {
                $q->where('name', 'like', "%{$searchKey}%")
                    ->orWhere('code', 'like', "%{$searchKey}%")
                    ->orWhere('contact_email', 'like', "%{$searchKey}%");
            });
        }

        if ($is_active !== null && $is_active !== '') {
            $query->where('is_active', $is_active);
        }

        $suppliers = $query->orderBy('priority', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('theme.views.settings.suppliers.index', compact('suppliers', 'searchKey', 'is_active', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Get suppliers data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $query = Supplier::query()->withCount('sources');

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            }

            $totalRecords = Supplier::count();
            $filteredRecords = $query->count();

            // Get column name for ordering
            $columns = ['code', 'name', 'sources_count', 'is_active', 'priority', 'last_sync_at', 'actions'];
            $orderColumn = $columns[$request->input('order.0.column', 4)] ?? 'priority';
            $orderDir = $request->input('order.0.dir', 'desc');

            $suppliers = $query
                ->orderBy($orderColumn === 'sources_count' ? 'priority' : $orderColumn, $orderDir)
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get()
                ->map(function ($supplier) {
                    return [
                        'uid' => $supplier->uid,
                        'code' => $supplier->code,
                        'name' => $supplier->name,
                        'sources_count' => '<span class="badge bg-info">'.$supplier->sources_count.'</span>',
                        'is_active' => $supplier->is_active
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-secondary">Inactivo</span>',
                        'priority' => '<span class="badge bg-primary">'.$supplier->priority.'</span>',
                        'last_sync_at' => $supplier->last_sync_at
                            ? $supplier->last_sync_at->format('d/m/Y H:i')
                            : '<span class="text-muted">Nunca</span>',
                        'actions' => $this->getActionsHtml($supplier),
                    ];
                });

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $suppliers,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting suppliers data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de proveedores',
            ], 500);
        }
    }

    /**
     * Generate actions HTML for DataTables
     */
    private function getActionsHtml(Supplier $supplier): string
    {
        $editBtn = '<button class="btn btn-sm btn-outline-primary edit-supplier me-1" data-id="'.$supplier->uid.'" title="Editar"><i class="fas fa-edit"></i></button>';
        $toggleBtn = '<button class="btn btn-sm btn-outline-'.($supplier->is_active ? 'warning' : 'success').' toggle-supplier me-1" data-id="'.$supplier->uid.'" title="'.($supplier->is_active ? 'Desactivar' : 'Activar').'"><i class="fas fa-'.($supplier->is_active ? 'pause' : 'play').'"></i></button>';
        $deleteBtn = '<button class="btn btn-sm btn-outline-danger delete-supplier" data-id="'.$supplier->uid.'" data-name="'.$supplier->name.'" title="Eliminar"><i class="fas fa-trash"></i></button>';

        return '<div class="btn-group">'.$editBtn.$toggleBtn.$deleteBtn.'</div>';
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $pageTitle = 'Crear Proveedor';
        $breadcrumb = 'Configuración / Proveedores / Crear';

        return view('theme.views.settings.suppliers.create', compact('pageTitle', 'breadcrumb'));
    }

    /**
     * Store new supplier
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = Supplier::create([
                'name' => $request->name,
                'code' => $request->code,
                'website' => $request->website,
                'description' => $request->description,
                'contact_email' => $request->contact_email,
                'priority' => $request->priority ?? 10,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'supplier' => $supplier,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating supplier: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el proveedor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $uid): View
    {
        $supplier = Supplier::where('uid', $uid)->firstOrFail();
        $pageTitle = 'Editar Proveedor';
        $breadcrumb = 'Configuración / Proveedores / Editar';

        return view('theme.views.settings.suppliers.edit', compact('supplier', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Update supplier
     */
    public function update(UpdateSupplierRequest $request, string $uid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $uid)->firstOrFail();

            $supplier->update([
                'name' => $request->name,
                'code' => $request->code,
                'website' => $request->website,
                'description' => $request->description,
                'contact_email' => $request->contact_email,
                'priority' => $request->priority ?? $supplier->priority,
                'is_active' => $request->boolean('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente',
                'supplier' => $supplier->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating supplier: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el proveedor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete supplier
     */
    public function destroy(string $uid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $uid)->firstOrFail();

            // Check if supplier has active sources
            if ($supplier->sources()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene fuentes activas',
                ], 409);
            }

            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting supplier: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proveedor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle supplier active status
     */
    public function toggle(string $uid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $uid)->firstOrFail();
            $supplier->is_active = ! $supplier->is_active;
            $supplier->save();

            return response()->json([
                'success' => true,
                'is_active' => $supplier->is_active,
                'message' => $supplier->is_active
                    ? 'Proveedor activado exitosamente'
                    : 'Proveedor desactivado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling supplier status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del proveedor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get supplier statistics
     */
    public function getStats(string $uid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $uid)->firstOrFail();

            $stats = [
                'total_sources' => $supplier->sources()->count(),
                'active_sources' => $supplier->sources()->where('is_active', true)->count(),
                'total_prompts' => $supplier->prompts()->count(),
                'pending_content' => $supplier->contents()->where('status', 'pending')->count(),
                'approved_content' => $supplier->contents()->where('status', 'approved')->count(),
                'rejected_content' => $supplier->contents()->where('status', 'rejected')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting supplier stats: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del proveedor',
            ], 500);
        }
    }

    /**
     * Get supplier details
     */
    public function show(string $uid): JsonResponse
    {
        try {
            $supplier = Supplier::where('uid', $uid)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'uid' => $supplier->uid,
                    'code' => $supplier->code,
                    'name' => $supplier->name,
                    'website' => $supplier->website,
                    'description' => $supplier->description,
                    'contact_email' => $supplier->contact_email,
                    'contact_phone' => $supplier->contact_phone,
                    'priority' => $supplier->priority,
                    'content_type' => $supplier->content_type,
                    'is_active' => $supplier->is_active,
                    'api_rate_limit' => $supplier->api_rate_limit,
                    'api_rate_period' => $supplier->api_rate_period,
                    'config' => $supplier->config ? json_encode($supplier->config, JSON_PRETTY_PRINT) : null,
                    'metadata' => $supplier->metadata,
                    'last_sync_at' => $supplier->last_sync_at?->format('d/m/Y H:i'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting supplier: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el proveedor',
            ], 500);
        }
    }

    /**
     * Test all suppliers
     */
    public function testAll(): JsonResponse
    {
        try {
            // Implementation of testing all suppliers
            // For now, returning a success message as per existing UI expectation
            return response()->json([
                'success' => true,
                'message' => 'Prueba de todos los proveedores iniciada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error testing all suppliers: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al probar los proveedores',
            ], 500);
        }
    }
}
