@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Panel de Automatización'])

    <div class="widget-content searchable-container list">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-cogs fs-7 text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Workflows Activos</p>
                                <h5 class="mb-0" id="activeWorkflows">{{ $stats['active_workflows'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clock fs-7 text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Ejecuciones Pendientes</p>
                                <h5 class="mb-0" id="pendingExecutions">{{ $stats['pending_executions'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fs-7 text-danger"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Ejecuciones Fallidas</p>
                                <h5 class="mb-0" id="failedExecutions">{{ $stats['failed_executions'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-heartbeat fs-7 text-success"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Estado del Sistema</p>
                                <h5 class="mb-0">
                                    <span class="badge bg-success" id="systemHealth">Saludable</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card card-body mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="createWorkflowBtn">
                        <i class="fas fa-plus me-2"></i> Nuevo Workflow
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary w-100" id="refreshStatsBtn">
                        <i class="fas fa-sync me-2"></i> Actualizar
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-primary w-100" id="runAllBtn">
                        <i class="fas fa-play me-2"></i> Ejecutar Todos
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-danger w-100" id="clearFailedBtn">
                        <i class="fas fa-trash me-2"></i> Limpiar Fallidos
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="workflows-tab" data-bs-toggle="tab"
                            data-bs-target="#workflows" type="button" role="tab">
                        <i class="fas fa-cogs me-2"></i> Workflows
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="executions-tab" data-bs-toggle="tab"
                            data-bs-target="#executions" type="button" role="tab">
                        <i class="fas fa-list me-2"></i> Ejecuciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="triggers-tab" data-bs-toggle="tab"
                            data-bs-target="#triggers" type="button" role="tab">
                        <i class="fas fa-bolt me-2"></i> Disparadores
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="alerts-tab" data-bs-toggle="tab"
                            data-bs-target="#alerts" type="button" role="tab">
                        <i class="fas fa-bell me-2"></i> Alertas
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Workflows Tab -->
                <div class="tab-pane fade show active" id="workflows" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="workflowsTable">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Proveedor</th>
                                    <th>Tipo</th>
                                    <th>Última Ejecución</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Executions Tab -->
                <div class="tab-pane fade" id="executions" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="executionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Workflow</th>
                                    <th>Inicio</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th>Resultado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Triggers Tab -->
                <div class="tab-pane fade" id="triggers" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="triggersTable">
                            <thead>
                                <tr>
                                    <th>Workflow</th>
                                    <th>Tipo de Disparador</th>
                                    <th>Configuración</th>
                                    <th>Próxima Ejecución</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Alerts Tab -->
                <div class="tab-pane fade" id="alerts" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="alertsTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Workflow</th>
                                    <th>Mensaje</th>
                                    <th>Severidad</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ url('managers/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('managers/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<link rel="stylesheet" href="{{ url('managers/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

<script>
$(document).ready(function() {
    let workflowsTable, executionsTable, triggersTable, alertsTable;

    // Initialize DataTables
    function initTables() {
        workflowsTable = $('#workflowsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("manager.settings.suppliers.automation.workflows.data") }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'supplier', name: 'supplier' },
                { data: 'type', name: 'type' },
                { data: 'last_execution', name: 'last_execution' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        executionsTable = $('#executionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("manager.settings.suppliers.automation.executions.data") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'workflow', name: 'workflow' },
                { data: 'started_at', name: 'started_at' },
                { data: 'duration', name: 'duration' },
                { data: 'status', name: 'status' },
                { data: 'result', name: 'result' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[2, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        triggersTable = $('#triggersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("manager.settings.suppliers.automation.triggers.data") }}',
            columns: [
                { data: 'workflow', name: 'workflow' },
                { data: 'type', name: 'type' },
                { data: 'config', name: 'config' },
                { data: 'next_run', name: 'next_run' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });

        alertsTable = $('#alertsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("manager.settings.suppliers.automation.alerts.data") }}',
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'type', name: 'type' },
                { data: 'workflow', name: 'workflow' },
                { data: 'message', name: 'message' },
                { data: 'severity', name: 'severity' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
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
            url: '{{ route("manager.settings.suppliers.automation.stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#activeWorkflows').text(response.data.active_workflows);
                    $('#pendingExecutions').text(response.data.pending_executions);
                    $('#failedExecutions').text(response.data.failed_executions);

                    const health = response.data.system_health;
                    const healthBadge = $('#systemHealth');
                    healthBadge.removeClass('bg-success bg-warning bg-danger');

                    if (health === 'healthy') {
                        healthBadge.addClass('bg-success').text('Saludable');
                    } else if (health === 'warning') {
                        healthBadge.addClass('bg-warning').text('Advertencia');
                    } else {
                        healthBadge.addClass('bg-danger').text('Crítico');
                    }
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
        window.location.href = '{{ route("manager.settings.suppliers.automation.workflows.create") }}';
    });

    // Run all workflows
    $('#runAllBtn').on('click', function() {
        const btn = $(this);
        const originalHtml = btn.html();

        Swal.fire({
            title: '¿Ejecutar todos los workflows?',
            text: 'Esto iniciará todos los workflows activos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, ejecutar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Ejecutando...');

                $.ajax({
                    url: '{{ route("manager.settings.suppliers.automation.workflows.run-all") }}',
                    method: 'POST',
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
            }
        });
    });

    // Clear failed executions
    $('#clearFailedBtn').on('click', function() {
        Swal.fire({
            title: '¿Limpiar ejecuciones fallidas?',
            text: 'Esto eliminará todas las ejecuciones fallidas del registro.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("manager.settings.suppliers.automation.executions.clear-failed") }}',
                    method: 'POST',
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
            }
        });
    });

    // Run specific workflow
    $(document).on('click', '.run-workflow', function() {
        const id = $(this).data('id');

        $.ajax({
            url: '{{ route("manager.settings.suppliers.automation.workflows.run", ":id") }}'.replace(':id', id),
            method: 'POST',
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

        $.ajax({
            url: '{{ route("manager.settings.suppliers.automation.executions.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const execution = response.data;
                    Swal.fire({
                        title: 'Detalles de Ejecución #' + execution.id,
                        html: `
                            <div class="text-start">
                                <p><strong>Workflow:</strong> ${execution.workflow_name}</p>
                                <p><strong>Estado:</strong> ${execution.status}</p>
                                <p><strong>Inicio:</strong> ${execution.started_at}</p>
                                <p><strong>Fin:</strong> ${execution.finished_at || 'N/A'}</p>
                                <p><strong>Duración:</strong> ${execution.duration || 'N/A'}</p>
                                <hr>
                                <p><strong>Resultado:</strong></p>
                                <pre class="bg-light p-2 rounded">${execution.result || 'Sin resultado'}</pre>
                                ${execution.error ? `<p><strong>Error:</strong></p><pre class="bg-danger text-white p-2 rounded">${execution.error}</pre>` : ''}
                            </div>
                        `,
                        width: '80%',
                        showCloseButton: true
                    });
                }
            },
            error: function() {
                toastr.error('Error al cargar detalles', 'Error');
            }
        });
    });

    // Auto-refresh stats every 30 seconds
    setInterval(refreshStats, 30000);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
