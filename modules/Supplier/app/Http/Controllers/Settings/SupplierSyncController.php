<?php

namespace Modules\Supplier\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Supplier\Models\SupplierSyncBatch;
use Modules\Supplier\Models\SupplierSyncStatus;
use Modules\Supplier\Services\SyncCoordinatorAgent;
use Modules\Supplier\Services\SyncStatusService;

class SupplierSyncController extends Controller
{
    public function __construct(
        protected SyncCoordinatorAgent $syncCoordinator,
        protected SyncStatusService $syncStatusService
    ) {}

    /**
     * Display the main sync dashboard
     */
    public function index(Request $request): View
    {
        $this->authorize('can_sync_suppliers');

        $pageTitle = 'Panel de sincronización';
        $breadcrumb = 'Configuración / Proveedores / Sincronización';

        // Get filter parameters
        $tab = $request->get('tab', 'overview');
        $status = $request->get('status');
        $syncType = $request->get('sync_type');

        // Build query for sync statuses
        $query = SupplierSyncStatus::query()->with('batch');

        if ($status) {
            $query->where('status', $status);
        }

        if ($syncType) {
            $query->where('sync_type', $syncType);
        }

        $syncs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total_syncs' => SupplierSyncStatus::count(),
            'active_syncs' => SupplierSyncStatus::where('status', 'running')->count(),
            'completed_syncs' => SupplierSyncStatus::where('status', 'completed')->count(),
            'failed_syncs' => SupplierSyncStatus::where('status', 'failed')->count(),
            'pending_syncs' => SupplierSyncStatus::where('status', 'pending')->count(),
        ];

        return view('supplier::settings.sync.index', compact(
            'syncs',
            'stats',
            'tab',
            'status',
            'syncType',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Show details of a specific sync operation
     */
    public function show(int $statusId): View
    {
        $this->authorize('can_sync_suppliers');

        $syncStatus = SupplierSyncStatus::with(['batch', 'logs', 'failures'])
            ->findOrFail($statusId);

        $pageTitle = 'Detalles de sincronización';
        $breadcrumb = 'Configuración / Proveedores / Sincronización / Detalles';

        // Get related batches for context
        $relatedBatches = SupplierSyncBatch::where('id', '!=', $syncStatus->batch_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('supplier::settings.sync.show', compact(
            'syncStatus',
            'relatedBatches',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * POST endpoint to start a new sync
     */
    public function startSync(Request $request): JsonResponse
    {
        $this->authorize('can_sync_suppliers');

        $request->validate([
            'sync_type' => 'required|string|in:product,category,price,provider,all',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'triggered_by' => 'required|string|in:manual,scheduled,webhook,api',
            'filter_criteria' => 'nullable|array',
        ]);

        try {
            $result = $this->syncCoordinator->coordinateSync(
                syncType: $request->input('sync_type'),
                supplierId: $request->input('supplier_id'),
                triggeredBy: $request->input('triggered_by'),
                filterCriteria: $request->input('filter_criteria')
            );

            if (! $result['success']) {
                Log::warning('Sync coordination failed', [
                    'sync_type' => $request->input('sync_type'),
                    'supplier_id' => $request->input('supplier_id'),
                    'error' => $result['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo iniciar la sincronización: '.$result['message'],
                ], 422);
            }

            Log::info('Sync coordination started successfully', [
                'batch_id' => $result['batch_id'],
                'sync_type' => $request->input('sync_type'),
                'supplier_id' => $request->input('supplier_id'),
                'agents_started' => $result['agents_started'],
                'total_items' => $result['total_items'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización iniciada correctamente.',
                'batch_id' => $result['batch_id'],
                'sync_types' => $result['sync_types'],
                'agents_started' => $result['agents_started'],
                'total_items' => $result['total_items'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting sync', [
                'sync_type' => $request->input('sync_type'),
                'supplier_id' => $request->input('supplier_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sincronización: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST endpoint to cancel a running sync
     */
    public function cancelSync(int $statusId): JsonResponse
    {
        $this->authorize('can_sync_suppliers');

        try {
            $syncStatus = SupplierSyncStatus::findOrFail($statusId);

            // Check if sync is actually running
            if ($syncStatus->status !== 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar sincronizaciones en progreso.',
                ], 422);
            }

            // Set cancellation flag
            $this->syncStatusService->setCancelFlag($syncStatus->id);

            // Update status to cancelled
            $syncStatus->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            Log::info('Sync cancelled', [
                'status_id' => $statusId,
                'batch_id' => $syncStatus->batch_id,
                'sync_type' => $syncStatus->sync_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización cancelada correctamente.',
                'status' => $syncStatus->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling sync', [
                'status_id' => $statusId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar sincronización: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST endpoint to retry a failed sync
     */
    public function retrySync(int $statusId): JsonResponse
    {
        $this->authorize('can_sync_suppliers');

        try {
            $syncStatus = SupplierSyncStatus::findOrFail($statusId);

            // Check if sync can be retried
            if (! in_array($syncStatus->status, ['failed', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden reintentar sincronizaciones fallidas o canceladas.',
                ], 422);
            }

            // Increment retry count
            $syncStatus->increment('retry_count');
            $syncStatus->update(['status' => 'pending']);

            // Re-coordinate the sync using same parameters
            $result = $this->syncCoordinator->coordinateSync(
                syncType: $syncStatus->sync_type,
                supplierId: $syncStatus->supplier_id,
                triggeredBy: 'retry',
                filterCriteria: $syncStatus->metadata
            );

            if (! $result['success']) {
                Log::warning('Sync retry coordination failed', [
                    'status_id' => $statusId,
                    'sync_type' => $syncStatus->sync_type,
                    'error' => $result['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo reintentar la sincronización: '.$result['message'],
                ], 422);
            }

            Log::info('Sync retry coordination started', [
                'original_status_id' => $statusId,
                'new_batch_id' => $result['batch_id'],
                'sync_type' => $syncStatus->sync_type,
                'retry_count' => $syncStatus->retry_count,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reintento de sincronización iniciado correctamente.',
                'batch_id' => $result['batch_id'],
                'agents_started' => $result['agents_started'],
                'total_items' => $result['total_items'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrying sync', [
                'status_id' => $statusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reintentar sincronización: '.$e->getMessage(),
            ], 500);
        }
    }
}
