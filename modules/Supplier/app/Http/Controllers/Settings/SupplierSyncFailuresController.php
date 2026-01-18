<?php

namespace Modules\Supplier\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Supplier\Models\SupplierSyncConflict;
use Modules\Supplier\Models\SupplierSyncFailure;
use Modules\Supplier\Services\ErpSyncService;

class SupplierSyncFailuresController extends Controller
{
    public function __construct(
        protected ErpSyncService $erpSyncService
    ) {}

    /**
     * Display dashboard of sync failures and conflicts
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Fallos de Sincronización';
        $breadcrumb = 'Configuración / Proveedores / Sincronización';

        $tab = $request->get('tab', 'failures');
        $syncType = $request->get('sync_type');
        $searchKey = $request->get('search');

        // Failures query
        $failuresQuery = SupplierSyncFailure::query()->latestFailures();

        if ($syncType) {
            $failuresQuery->byType($syncType);
        }

        if ($searchKey) {
            $failuresQuery->where(function ($q) use ($searchKey) {
                $q->where('error_message', 'ILIKE', "%{$searchKey}%")
                    ->orWhere('supplier_id', $searchKey)
                    ->orWhere('erp_id', $searchKey);
            });
        }

        $failures = $failuresQuery->paginate(15, ['*'], 'failures_page');

        // Conflicts query
        $conflictsQuery = SupplierSyncConflict::query()
            ->orderBy('conflict_detected_at', 'desc');

        if ($syncType) {
            $conflictsQuery->byEntityType($syncType);
        }

        $conflicts = $conflictsQuery->paginate(15, ['*'], 'conflicts_page');

        // Statistics
        $stats = [
            'total_failures' => SupplierSyncFailure::count(),
            'retryable_failures' => SupplierSyncFailure::retryable()->count(),
            'total_conflicts' => SupplierSyncConflict::count(),
            'unresolved_conflicts' => SupplierSyncConflict::unresolved()->count(),
            'recent_conflicts' => SupplierSyncConflict::recent(7)->count(),
        ];

        return view('supplier::settings.sync-failures.index', compact(
            'failures',
            'conflicts',
            'stats',
            'tab',
            'syncType',
            'searchKey',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Retry a specific sync failure
     */
    public function retry(Request $request, int $id): JsonResponse
    {
        try {
            $failure = SupplierSyncFailure::findOrFail($id);

            if (! $failure->canRetry()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este fallo no puede ser reintentado. Ha excedido el número máximo de reintentos o fue reintentado recientemente.',
                ], 422);
            }

            // Increment retry count
            $failure->increment('retry_count');
            $failure->update(['last_retry_at' => now()]);

            // Attempt to sync again based on type
            $result = match ($failure->sync_type) {
                'price' => $this->retryPriceSync($failure),
                'product' => $this->retryProductSync($failure),
                'provider' => $this->retryProviderSync($failure),
                default => throw new \Exception('Tipo de sincronización desconocido: '.$failure->sync_type),
            };

            if ($result['success']) {
                // Delete failure record on success
                $failure->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Sincronización reintentada exitosamente.',
                ]);
            } else {
                // Update error message
                $failure->update([
                    'error_message' => $result['error'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'El reintento falló: '.$result['error'],
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error retrying sync failure', [
                'failure_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reintentar: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk retry multiple failures
     */
    public function bulkRetry(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:supplier_sync_failures,id',
        ]);

        $ids = $request->input('ids');
        $successes = 0;
        $failures = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $response = $this->retry($request, $id);
                $data = json_decode($response->getContent(), true);

                if ($data['success']) {
                    $successes++;
                } else {
                    $failures++;
                    $errors[] = "ID {$id}: {$data['message']}";
                }
            } catch (\Exception $e) {
                $failures++;
                $errors[] = "ID {$id}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Reintentos completados: {$successes} exitosos, {$failures} fallidos.",
            'successes' => $successes,
            'failures' => $failures,
            'errors' => $errors,
        ]);
    }

    /**
     * Delete a sync failure
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $failure = SupplierSyncFailure::findOrFail($id);
            $failure->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fallo eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting sync failure', [
                'failure_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete multiple failures
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:supplier_sync_failures,id',
        ]);

        $ids = $request->input('ids');
        $deleted = SupplierSyncFailure::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fallos eliminados correctamente.",
            'deleted' => $deleted,
        ]);
    }

    /**
     * Get conflict details
     */
    public function showConflict(int $id): JsonResponse
    {
        try {
            $conflict = SupplierSyncConflict::with('resolvedBy')->findOrFail($id);

            return response()->json([
                'success' => true,
                'conflict' => $conflict,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conflicto no encontrado.',
            ], 404);
        }
    }

    /**
     * Helper: Retry price sync
     */
    protected function retryPriceSync(SupplierSyncFailure $failure): array
    {
        try {
            $price = \Modules\Supplier\Models\SupplierProductPrice::find($failure->supplier_id);

            if (! $price) {
                return ['success' => false, 'error' => 'Precio no encontrado'];
            }

            $changedFields = array_keys($failure->changed_data);
            $this->erpSyncService->syncPriceToOracle($price, $changedFields);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Helper: Retry product sync
     */
    protected function retryProductSync(SupplierSyncFailure $failure): array
    {
        try {
            $product = \Modules\Supplier\Models\SupplierProduct::find($failure->supplier_id);

            if (! $product) {
                return ['success' => false, 'error' => 'Producto no encontrado'];
            }

            $changedFields = array_keys($failure->changed_data);
            $this->erpSyncService->syncProductToOracle($product, $changedFields);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Helper: Retry provider sync
     */
    protected function retryProviderSync(SupplierSyncFailure $failure): array
    {
        try {
            $provider = \Modules\Supplier\Models\SupplierErpProvider::find($failure->supplier_id);

            if (! $provider) {
                return ['success' => false, 'error' => 'Proveedor no encontrado'];
            }

            $changedFields = array_keys($failure->changed_data);
            $this->erpSyncService->syncProviderToOracle($provider, $changedFields);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
