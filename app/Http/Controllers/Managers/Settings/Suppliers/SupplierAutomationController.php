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
                ->with(['createdBy']);

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

            // Map DataTables column index to actual column names
            $columns = ['name', 'name', 'workflow_type', 'last_executed_at', 'is_active', 'id'];
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $orderDir = $request->input('order.0.dir', 'desc');

            $workflows = $query
                ->orderBy($orderColumn, $orderDir)
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
                ->with(['workflow', 'supplier', 'source']);

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

            // Map DataTables column index to actual column names
            $columns = ['id', 'workflow_id', 'started_at', 'duration_ms', 'status', 'status', 'id'];
            $orderColumnIndex = $request->input('order.0.column', 2);
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $orderDir = $request->input('order.0.dir', 'desc');

            $executions = $query
                ->orderBy($orderColumn, $orderDir)
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
                        ->orWhere('trigger_type', 'like', "%{$search}%");
                });
            }

            if ($type = $request->input('type')) {
                $query->where('trigger_type', $type);
            }

            $totalRecords = SupplierAutomationTrigger::count();
            $filteredRecords = $query->count();

            // Map DataTables column index to actual column names
            $columns = ['workflow_id', 'trigger_type', 'trigger_config', 'last_triggered_at', 'is_enabled', 'id'];
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $orderDir = $request->input('order.0.dir', 'asc');

            $triggers = $query
                ->orderBy($orderColumn, $orderDir)
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
            $activeWorkflows = SupplierAutomationWorkflow::where('is_active', true)->count();
            $pendingExecutions = SupplierAutomationExecution::where('status', 'pending')->count();
            $failedExecutions = SupplierAutomationExecution::whereDate('created_at', today())
                ->where('status', 'failed')->count();
            $totalExecutions = SupplierAutomationExecution::whereDate('created_at', today())->count();

            // Calculate system health based on failure rate
            $failureRate = $totalExecutions > 0 ? ($failedExecutions / $totalExecutions) * 100 : 0;
            $systemHealth = 'healthy';
            if ($failureRate > 50) {
                $systemHealth = 'critical';
            } elseif ($failureRate > 20) {
                $systemHealth = 'warning';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'active_workflows' => $activeWorkflows,
                    'pending_executions' => $pendingExecutions,
                    'failed_executions' => $failedExecutions,
                    'system_health' => $systemHealth,
                ],
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
                'data' => $execution,
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

    /**
     * Create workflow page
     */
    public function createWorkflow(): View
    {
        $pageTitle = 'Crear Workflow de Automatización';
        $breadcrumb = 'Configuración / Proveedores / Automatización / Crear';

        return view('managers.views.settings.suppliers.automation.create', compact('pageTitle', 'breadcrumb'));
    }

    /**
     * Store new workflow
     */
    public function storeWorkflow(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'workflow_type' => 'required|in:n8n,make,zapier,webhook,internal',
                'external_workflow_id' => 'nullable|string|max:255',
                'webhook_url' => 'nullable|url|max:500',
                'callback_url' => 'nullable|url|max:500',
                'timeout_seconds' => 'nullable|integer|min:0',
                'max_retries' => 'nullable|integer|min:0|max:10',
                'priority' => 'nullable|integer|min:1|max:10',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['uid'] = \Illuminate\Support\Str::uuid();
            $validated['created_by'] = auth()->id();
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['priority'] = $validated['priority'] ?? 5;
            $validated['timeout_seconds'] = $validated['timeout_seconds'] ?? 300;
            $validated['max_retries'] = $validated['max_retries'] ?? 3;

            $workflow = SupplierAutomationWorkflow::create($validated);

            return redirect()
                ->route('manager.settings.suppliers.automation.index')
                ->with('success', 'Workflow creado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error creating workflow: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error al crear el workflow: '.$e->getMessage());
        }
    }

    /**
     * Edit workflow page
     */
    public function editWorkflow(string $uid): View
    {
        $workflow = SupplierAutomationWorkflow::where('uid', $uid)->firstOrFail();

        $pageTitle = 'Editar Workflow de Automatización';
        $breadcrumb = 'Configuración / Proveedores / Automatización / Editar';

        return view('managers.views.settings.suppliers.automation.edit', compact('pageTitle', 'breadcrumb', 'workflow'));
    }

    /**
     * Update workflow
     */
    public function updateWorkflow(Request $request, string $uid)
    {
        try {
            $workflow = SupplierAutomationWorkflow::where('uid', $uid)->firstOrFail();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'workflow_type' => 'required|in:n8n,make,zapier,webhook,internal',
                'external_workflow_id' => 'nullable|string|max:255',
                'webhook_url' => 'nullable|url|max:500',
                'callback_url' => 'nullable|url|max:500',
                'timeout_seconds' => 'nullable|integer|min:0',
                'max_retries' => 'nullable|integer|min:0|max:10',
                'priority' => 'nullable|integer|min:1|max:10',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $request->boolean('is_active', $workflow->is_active);

            $workflow->update($validated);

            return redirect()
                ->route('manager.settings.suppliers.automation.index')
                ->with('success', 'Workflow actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating workflow: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el workflow: '.$e->getMessage());
        }
    }

    /**
     * Delete workflow
     */
    public function destroyWorkflow(string $uid)
    {
        try {
            $workflow = SupplierAutomationWorkflow::where('uid', $uid)->firstOrFail();
            $workflow->delete();

            return redirect()
                ->route('manager.settings.suppliers.automation.index')
                ->with('success', 'Workflow eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error deleting workflow: '.$e->getMessage());

            return redirect()
                ->route('manager.settings.suppliers.automation.index')
                ->with('error', 'Error al eliminar el workflow: '.$e->getMessage());
        }
    }

    /**
     * Run all active workflows
     */
    public function runAllWorkflows(): JsonResponse
    {
        try {
            $activeWorkflows = SupplierAutomationWorkflow::where('is_active', true)->get();

            if ($activeWorkflows->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay workflows activos para ejecutar',
                ], 400);
            }

            $executed = 0;
            foreach ($activeWorkflows as $workflow) {
                $result = $this->orchestrationService->executeWorkflow($workflow);
                if ($result['success']) {
                    $executed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se ejecutaron {$executed} de {$activeWorkflows->count()} workflows",
            ]);

        } catch (\Exception $e) {
            Log::error('Error running all workflows: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar todos los workflows: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run specific workflow
     */
    public function runWorkflow(string $uid): JsonResponse
    {
        try {
            $workflow = SupplierAutomationWorkflow::where('uid', $uid)->firstOrFail();

            $result = $this->orchestrationService->executeWorkflow($workflow);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'execution' => $result['execution'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error running workflow: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar el workflow: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear failed executions
     */
    public function clearFailedExecutions(): JsonResponse
    {
        try {
            $count = SupplierAutomationExecution::where('status', 'failed')->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} ejecuciones fallidas",
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing failed executions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar ejecuciones fallidas: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get alerts list
     */
    public function alerts(Request $request): JsonResponse
    {
        try {
            // For now, return empty data structure
            // This can be implemented later with a proper alerts table
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting alerts: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas',
            ], 500);
        }
    }

    /**
     * Show logs page
     */
    public function logsPage()
    {
        return view('managers.views.settings.suppliers.automation.logs');
    }

    /**
     * Get system logs data (AJAX)
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'error');
            $limit = $request->input('limit', 100);

            $logFile = storage_path('logs/laravel.log');

            if (! file_exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'logs' => [],
                    'message' => 'No hay logs disponibles',
                ]);
            }

            // Read the log file
            $logContent = file_get_contents($logFile);
            $lines = explode("\n", $logContent);
            $logs = [];

            // Parse log entries
            $currentEntry = null;
            foreach ($lines as $line) {
                // Match Laravel log format: [timestamp] environment.LEVEL: message
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)/', $line, $matches)) {
                    // Save previous entry if exists
                    if ($currentEntry) {
                        $logs[] = $currentEntry;
                    }

                    // Start new entry
                    $currentEntry = [
                        'timestamp' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'message' => $matches[3],
                    ];
                } elseif ($currentEntry && trim($line)) {
                    // Append to current message (multi-line logs)
                    $currentEntry['message'] .= "\n".$line;
                }
            }

            // Add last entry
            if ($currentEntry) {
                $logs[] = $currentEntry;
            }

            // Calculate counts for all types
            $allLogs = $logs;
            $counts = [
                'error' => count(array_filter($allLogs, fn ($log) => $log['level'] === 'error')),
                'warning' => count(array_filter($allLogs, fn ($log) => $log['level'] === 'warning')),
                'info' => count(array_filter($allLogs, fn ($log) => $log['level'] === 'info')),
                'total' => count($allLogs),
            ];

            // Filter by type if not 'all'
            if ($type !== 'all') {
                $logs = array_filter($logs, function ($log) use ($type) {
                    return $log['level'] === $type;
                });
            }

            // Get latest entries
            $logs = array_slice(array_reverse($logs), 0, $limit);

            return response()->json([
                'success' => true,
                'logs' => array_values($logs),
                'total' => count($logs),
                'counts' => $counts,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting logs: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los logs: '.$e->getMessage(),
                'logs' => [],
            ], 500);
        }
    }

    /**
     * Download logs file
     */
    public function downloadLogs(Request $request)
    {
        try {
            $type = $request->input('type', 'all');
            $logFile = storage_path('logs/laravel.log');

            if (! file_exists($logFile)) {
                return back()->with('error', 'No hay logs disponibles para descargar');
            }

            $filename = 'automation-logs-'.date('Y-m-d-His').'.log';

            // If type is 'all', download entire file
            if ($type === 'all') {
                return response()->download($logFile, $filename);
            }

            // Otherwise, filter by type
            $logContent = file_get_contents($logFile);
            $lines = explode("\n", $logContent);
            $filteredLines = [];

            foreach ($lines as $line) {
                if (stripos($line, '.'.$type.':') !== false || empty(trim($line))) {
                    $filteredLines[] = $line;
                }
            }

            $filteredContent = implode("\n", $filteredLines);

            return response($filteredContent, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');

        } catch (\Exception $e) {
            Log::error('Error downloading logs: '.$e->getMessage());

            return back()->with('error', 'Error al descargar los logs: '.$e->getMessage());
        }
    }
}
