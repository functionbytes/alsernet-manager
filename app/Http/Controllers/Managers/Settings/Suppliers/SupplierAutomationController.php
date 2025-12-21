<?php

namespace App\Http\Controllers\Managers\Settings\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Supplier\SupplierAutomationExecution;
use App\Models\Supplier\SupplierAutomationTrigger;
use App\Models\Supplier\SupplierAutomationWorkflow;
use App\Services\Supplier\AutomationOrchestrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierAutomationController extends Controller
{
    public function __construct(protected AutomationOrchestrationService $orchestrationService) {}

    /**
     * Display automation dashboard
     */
    public function index(): View
    {
        $pageTitle = 'Automatización de Proveedores';
        $breadcrumb = 'Configuración / Proveedores / Automatización';

        // Dashboard statistics
        $stats = [
            'active_workflows' => SupplierAutomationWorkflow::where('is_active', true)->count(),
            'total_executions_today' => SupplierAutomationExecution::whereDate('created_at', today())->count(),
            'failed_executions_today' => SupplierAutomationExecution::whereDate('created_at', today())->where('status', 'failed')->count(),
            'pending_executions' => SupplierAutomationExecution::where('status', 'pending')->count(),
        ];

        return view('managers.views.settings.suppliers.automation.index', compact('pageTitle', 'breadcrumb', 'stats'));
    }

    /**
     * Get workflows list
     */
    public function workflows(Request $request): JsonResponse
    {
        try {
            $query = SupplierAutomationWorkflow::query()
                ->with(['supplier', 'source', 'trigger']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($status = $request->input('status')) {
                $query->where('is_active', $status === 'active');
            }

            $totalRecords = SupplierAutomationWorkflow::count();
            $filteredRecords = $query->count();

            $workflows = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $workflows,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting workflows: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los flujos de trabajo',
            ], 500);
        }
    }

    /**
     * Get executions list
     */
    public function executions(Request $request): JsonResponse
    {
        try {
            $query = SupplierAutomationExecution::query()
                ->with(['workflow', 'supplier', 'trigger']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('workflow', function ($wq) use ($search) {
                        $wq->where('name', 'like', "%{$search}%");
                    });
                });
            }

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($workflowId = $request->input('workflow_id')) {
                $query->where('workflow_id', $workflowId);
            }

            $totalRecords = SupplierAutomationExecution::count();
            $filteredRecords = $query->count();

            $executions = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $executions,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting executions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las ejecuciones',
            ], 500);
        }
    }

    /**
     * Get triggers list
     */
    public function triggers(Request $request): JsonResponse
    {
        try {
            $query = SupplierAutomationTrigger::query()
                ->with(['workflow']);

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            }

            if ($type = $request->input('type')) {
                $query->where('type', $type);
            }

            $totalRecords = SupplierAutomationTrigger::count();
            $filteredRecords = $query->count();

            $triggers = $query
                ->orderBy($request->input('order.0.column', 'created_at'), $request->input('order.0.dir', 'desc'))
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $triggers,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting triggers: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los triggers',
            ], 500);
        }
    }

    /**
     * Retry failed execution
     */
    public function retryExecution(string $uid): JsonResponse
    {
        try {
            $execution = SupplierAutomationExecution::where('uid', $uid)->firstOrFail();

            if ($execution->status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden reintentar ejecuciones fallidas',
                ], 400);
            }

            $result = $this->orchestrationService->retryExecution($execution);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'execution' => $result['execution'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrying execution: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al reintentar la ejecución: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel running execution
     */
    public function cancelExecution(string $uid): JsonResponse
    {
        try {
            $execution = SupplierAutomationExecution::where('uid', $uid)->firstOrFail();

            if (! in_array($execution->status, ['pending', 'running'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar ejecuciones pendientes o en ejecución',
                ], 400);
            }

            $result = $this->orchestrationService->cancelExecution($execution);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error canceling execution: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la ejecución: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get automation health metrics
     */
    public function getHealthMetrics(): JsonResponse
    {
        try {
            $metrics = [
                'overall_health' => $this->orchestrationService->calculateOverallHealth(),
                'workflow_stats' => [
                    'total' => SupplierAutomationWorkflow::count(),
                    'active' => SupplierAutomationWorkflow::where('is_active', true)->count(),
                    'paused' => SupplierAutomationWorkflow::where('is_active', false)->count(),
                ],
                'execution_stats' => [
                    'today' => SupplierAutomationExecution::whereDate('created_at', today())->count(),
                    'successful' => SupplierAutomationExecution::whereDate('created_at', today())
                        ->where('status', 'completed')->count(),
                    'failed' => SupplierAutomationExecution::whereDate('created_at', today())
                        ->where('status', 'failed')->count(),
                    'pending' => SupplierAutomationExecution::where('status', 'pending')->count(),
                ],
                'average_execution_time' => SupplierAutomationExecution::whereDate('created_at', today())
                    ->whereNotNull('execution_time')
                    ->avg('execution_time'),
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting health metrics: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas de salud',
            ], 500);
        }
    }

    /**
     * Get execution details
     */
    public function getExecutionDetails(string $uid): JsonResponse
    {
        try {
            $execution = SupplierAutomationExecution::where('uid', $uid)
                ->with(['workflow', 'supplier', 'trigger', 'chainExecutions.chain'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'execution' => $execution,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting execution details: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la ejecución',
            ], 500);
        }
    }

    /**
     * Toggle workflow status
     */
    public function toggleWorkflow(string $uid): JsonResponse
    {
        try {
            $workflow = SupplierAutomationWorkflow::where('uid', $uid)->firstOrFail();
            $workflow->is_active = ! $workflow->is_active;
            $workflow->save();

            return response()->json([
                'success' => true,
                'is_active' => $workflow->is_active,
                'message' => $workflow->is_active
                    ? 'Flujo de trabajo activado exitosamente'
                    : 'Flujo de trabajo desactivado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling workflow: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del flujo de trabajo: '.$e->getMessage(),
            ], 500);
        }
    }
}
