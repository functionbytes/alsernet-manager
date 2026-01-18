@extends('layouts.theme')

@section('title', 'Automatización de Proveedores')

@section('content')

    @include('core::components.card', ['title' => 'Automatización de Proveedores'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <!-- Main Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Panel de automatización</h5>
                        <p class="small mb-0 text-muted">Gestiona workflows, ejecuciones y disparadores automáticos</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" id="refreshStatsBtn">
                            Actualizar
                        </button>
                        <button type="button" class="btn btn-primary" id="createWorkflowBtn">
                            Nuevo workflow
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Workflows activos</h6>
                                <h4 class="mb-1 fw-bold" id="activeWorkflows">{{ $stats['active_workflows'] ?? 0 }}</h4>
                                <small class="text-muted">En funcionamiento</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Pendientes</h6>
                                <h4 class="mb-1 fw-bold" id="pendingExecutions">{{ $stats['pending_executions'] ?? 0 }}</h4>
                                <small class="text-muted">Ejecuciones en cola</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Fallidas</h6>
                                <h4 class="mb-1 fw-bold" id="failedExecutions">{{ $stats['failed_executions_today'] ?? 0 }}</h4>
                                <small class="text-muted">Hoy</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Ejecuciones totales</h6>
                                <h4 class="mb-1 fw-bold">{{ $stats['total_executions_today'] ?? 0 }}</h4>
                                <small class="text-muted">Hoy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card-body border-bottom bg-light">
                <h6 class="fw-bold mb-3">Acciones rápidas</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" id="runAllBtn">
                                Ejecutar todos los workflows
                            </button>
                            <small class="text-muted text-center">
                                Ejecuta todos los workflows activos
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <a href="{{ route('settings.suppliers.automation.logs') }}" class="btn btn-secondary">
                                Ver logs del sistema
                            </a>
                            <small class="text-muted text-center">
                                Consulta registros de actividad
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="clearFailedBtn">
                                Limpiar ejecuciones fallidas
                            </button>
                            <small class="text-muted text-center">
                                Elimina <span id="failedCount">{{ $stats['failed_executions_today'] ?? 0 }}</span> ejecuciones fallidas
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="workflows-tab" data-bs-toggle="tab"
                                data-bs-target="#workflows" type="button" role="tab">
                            Workflows
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="executions-tab" data-bs-toggle="tab"
                                data-bs-target="#executions" type="button" role="tab">
                            Ejecuciones
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="triggers-tab" data-bs-toggle="tab"
                                data-bs-target="#triggers" type="button" role="tab">
                            Disparadores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="alerts-tab" data-bs-toggle="tab"
                                data-bs-target="#alerts" type="button" role="tab">
                            Alertas
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Workflows Tab -->
                    <div class="tab-pane fade show active" id="workflows" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="workflowsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Proveedor</th>
                                        <th>Tipo</th>
                                        <th>Última ejecución</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Executions Tab -->
                    <div class="tab-pane fade" id="executions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="executionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Workflow</th>
                                        <th>Inicio</th>
                                        <th>Duración</th>
                                        <th class="text-center">Estado</th>
                                        <th>Resultado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Triggers Tab -->
                    <div class="tab-pane fade" id="triggers" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="triggersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Workflow</th>
                                        <th>Tipo de disparador</th>
                                        <th>Configuración</th>
                                        <th>Próxima ejecución</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Alerts Tab -->
                    <div class="tab-pane fade" id="alerts" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="alertsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Workflow</th>
                                        <th>Mensaje</th>
                                        <th>Severidad</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Run All Workflows Modal -->
    <div class="modal fade" id="runAllModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Ejecutar todos los workflows?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esto iniciará todos los workflows activos.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmRunAllBtn">Sí, ejecutar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Failed Executions Modal -->
    <div class="modal fade" id="clearFailedModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Limpiar ejecuciones fallidas?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esto eliminará todas las ejecuciones fallidas del registro.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmClearFailedBtn">Sí, limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Execution Details Modal -->
    <div class="modal fade" id="executionDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="executionDetailsTitle">Detalles de Ejecución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="executionDetailsBody">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ url('theme/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('theme/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<link rel="stylesheet" href="{{ url('theme/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

<script>
$(document).ready(function() {
    let workflowsTable, executionsTable, triggersTable, alertsTable;

    // Initialize modals
    const runAllModal = new bootstrap.Modal(document.getElementById('runAllModal'));
    const clearFailedModal = new bootstrap.Modal(document.getElementById('clearFailedModal'));
    const executionDetailsModal = new bootstrap.Modal(document.getElementById('executionDetailsModal'));

    // Initialize DataTables
    function initTables() {
        workflowsTable = $('#workflowsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("settings.suppliers.automation.workflows.data") }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'supplier', name: 'supplier' },
                { data: 'type', name: 'type' },
                { data: 'last_execution', name: 'last_execution' },
                { data: 'is_active', name: 'is_active', orderable: false, className: 'text-center' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        executionsTable = $('#executionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("settings.suppliers.automation.executions.data") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'workflow', name: 'workflow' },
                { data: 'started_at', name: 'started_at' },
                { data: 'duration', name: 'duration' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'result', name: 'result' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[2, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        triggersTable = $('#triggersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("settings.suppliers.automation.triggers.data") }}',
            columns: [
                { data: 'workflow', name: 'workflow' },
                { data: 'type', name: 'type' },
                { data: 'config', name: 'config' },
                { data: 'next_run', name: 'next_run' },
                { data: 'is_active', name: 'is_active', orderable: false, className: 'text-center' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        alertsTable = $('#alertsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("settings.suppliers.automation.alerts.data") }}',
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'type', name: 'type' },
                { data: 'workflow', name: 'workflow' },
                { data: 'message', name: 'message' },
                { data: 'severity', name: 'severity' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }

    initTables();

    // Refresh stats
    function refreshStats() {
        $.ajax({
            url: '{{ route("settings.suppliers.automation.stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#activeWorkflows').text(response.data.active_workflows);
                    $('#pendingExecutions').text(response.data.pending_executions);
                    $('#failedExecutions').text(response.data.failed_executions);
                    $('#failedCount').text(response.data.failed_executions || 0);
                }
            }
        });
    }

    $('#refreshStatsBtn').on('click', function() {
        refreshStats();
        workflowsTable.ajax.reload();
        executionsTable.ajax.reload();
        triggersTable.ajax.reload();
        alertsTable.ajax.reload();
        toastr.info('Datos actualizados', 'Automatización');
    });

    // Create workflow
    $('#createWorkflowBtn').on('click', function() {
        window.location.href = '{{ route("settings.suppliers.automation.workflows.create") }}';
    });

    // Run all workflows - Show modal
    $('#runAllBtn').on('click', function() {
        runAllModal.show();
    });

    // Run all workflows - Confirm
    $('#confirmRunAllBtn').on('click', function() {
        const btn = $('#runAllBtn');
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('Ejecutando...');
        runAllModal.hide();

        $.ajax({
            url: '{{ route("settings.suppliers.automation.workflows.run-all") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Automatización');
                    refreshStats();
                    executionsTable.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Error al ejecutar workflows', 'Error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    // Clear failed executions - Show modal
    $('#clearFailedBtn').on('click', function() {
        clearFailedModal.show();
    });

    // Clear failed executions - Confirm
    $('#confirmClearFailedBtn').on('click', function() {
        clearFailedModal.hide();

        $.ajax({
            url: '{{ route("settings.suppliers.automation.executions.clear-failed") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Automatización');
                    refreshStats();
                    executionsTable.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Error al limpiar ejecuciones', 'Error');
            }
        });
    });

    // Run specific workflow
    $(document).on('click', '.run-workflow', function() {
        const id = $(this).data('id');

        $.ajax({
            url: '{{ route("settings.suppliers.automation.workflows.run", ":id") }}'.replace(':id', id),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Workflow');
                    refreshStats();
                    executionsTable.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Error al ejecutar workflow', 'Error');
            }
        });
    });

    // View execution details
    $(document).on('click', '.view-execution', function() {
        const id = $(this).data('id');

        // Show loading state
        $('#executionDetailsBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);

        executionDetailsModal.show();

        $.ajax({
            url: '{{ route("settings.suppliers.automation.executions.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const execution = response.data;
                    $('#executionDetailsTitle').text('Detalles de Ejecución #' + execution.id);

                    let html = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Workflow</h6>
                                <p class="text-muted">${execution.workflow_name}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Estado</h6>
                                <p class="text-muted">${execution.status}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Inicio</h6>
                                <p class="text-muted">${execution.started_at}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Fin</h6>
                                <p class="text-muted">${execution.finished_at || 'N/A'}</p>
                            </div>
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Duración</h6>
                                <p class="text-muted">${execution.duration || 'N/A'}</p>
                            </div>
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Resultado</h6>
                                <pre class="bg-light p-3 rounded">${execution.result || 'Sin resultado'}</pre>
                            </div>
                    `;

                    if (execution.error) {
                        html += `
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Error</h6>
                                <pre class="bg-light p-3 rounded text-danger">${execution.error}</pre>
                            </div>
                        `;
                    }

                    html += '</div>';

                    $('#executionDetailsBody').html(html);
                }
            },
            error: function() {
                $('#executionDetailsBody').html(`
                    <div class="alert alert-danger">
                        Error al cargar los detalles de la ejecución
                    </div>
                `);
            }
        });
    });

    // Auto-refresh stats every 30 seconds
    setInterval(refreshStats, 30000);
});
</script>
@endpush
