@extends('layouts.theme')

@section('content')

    @include('core::components.card', ['title' => 'Logs del Sistema de Automatización'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <!-- System Logs Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light text-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Logs del Sistema de Automatización</h5>
                        <p class="small mb-0 text-black">Visualiza y descarga los registros de eventos del sistema de automatización de proveedores.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.suppliers.automation.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </a>
                        <button type="button" class="btn btn-success" id="downloadLogsBtn">
                            <i class="fa fa-download me-1"></i> Descargar logs
                        </button>
                        <button type="button" class="btn btn-primary" onclick="refreshLogs()" title="Actualiza los logs en tiempo real">
                            <i class="fa fa-arrows-rotate me-1"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Errores</h6>
                                        <h2 id="errorCount" class="text-danger" style="font-weight: 700;">-</h2>
                                        <small class="text-muted">Logs de tipo ERROR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Advertencias</h6>
                                        <h2 id="warningCount" class="text-warning" style="font-weight: 700;">-</h2>
                                        <small class="text-muted">Logs de tipo WARNING</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Información</h6>
                                        <h2 id="infoCount" class="text-info" style="font-weight: 700;">-</h2>
                                        <small class="text-muted">Logs de tipo INFO</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Total de logs</h6>
                                        <h2 id="totalCount" class="text-success" style="font-weight: 700;">-</h2>
                                        <small class="text-muted">Todos los registros</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Content -->
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-bold">Registros del sistema</h6>
                                <p class="text-muted small mb-0">Mostrando los últimos 100 registros. Usa los filtros para ver tipos específicos.</p>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <label class="text-muted small mb-0 me-2">Filtrar por tipo:</label>
                                <div class="btn-group" role="group" aria-label="Filtro de tipo de log">
                                    <input type="radio" class="btn-check" name="logType" id="logError" value="error" checked>
                                    <label class="btn btn-outline-danger btn-sm" for="logError">
                                        <i class="fa fa-times-circle me-1"></i>Errores
                                    </label>

                                    <input type="radio" class="btn-check" name="logType" id="logWarning" value="warning">
                                    <label class="btn btn-outline-warning btn-sm" for="logWarning">
                                        <i class="fa fa-exclamation-triangle me-1"></i>Advertencias
                                    </label>

                                    <input type="radio" class="btn-check" name="logType" id="logInfo" value="info">
                                    <label class="btn btn-outline-info btn-sm" for="logInfo">
                                        <i class="fa fa-info-circle me-1"></i>Información
                                    </label>

                                    <input type="radio" class="btn-check" name="logType" id="logAll" value="all">
                                    <label class="btn btn-outline-secondary btn-sm" for="logAll">
                                        <i class="fa fa-list me-1"></i>Todos
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Info Panel -->
                        <div class="alert alert-info border-0 bg-info-subtle d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-4 align-items-center">
                                <div>
                                    <span class="badge bg-white text-danger me-2">
                                        <i class="fa fa-times-circle me-1"></i> ERROR
                                    </span>
                                    <small class="text-muted">Fallos críticos del sistema</small>
                                </div>
                                <div class="vr"></div>
                                <div>
                                    <span class="badge bg-white text-warning me-2">
                                        <i class="fa fa-exclamation-triangle me-1"></i> WARNING
                                    </span>
                                    <small class="text-muted">Advertencias de funcionamiento</small>
                                </div>
                                <div class="vr"></div>
                                <div>
                                    <span class="badge bg-white text-info me-2">
                                        <i class="fa fa-info-circle me-1"></i> INFO
                                    </span>
                                    <small class="text-muted">Información del sistema</small>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted">
                                    <i class="fa fa-clock me-1"></i> Auto-actualización disponible
                                </small>
                            </div>
                        </div>

                        <!-- Logs Container -->
                        <div class="card border">
                            <div class="card-body p-0">
                                <div id="logsContainer" style="max-height: 600px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px;">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Cargando logs del sistema...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentLogType = 'error';

    // Load logs on page load
    loadLogs(currentLogType);

    // Handle log type filter change
    $('input[name="logType"]').on('change', function() {
        currentLogType = $(this).val();
        loadLogs(currentLogType);
    });

    // Download logs button
    $('#downloadLogsBtn').on('click', function() {
        const downloadUrl = '{{ route("backups.suppliers.automation.logs.download") }}?type=' + currentLogType;
        window.location.href = downloadUrl;
    });

    // Function to load logs via AJAX
    function loadLogs(type = 'error') {
        // Show loading state
        $('#logsContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando logs...</p>
            </div>
        `);

        $.ajax({
            url: '{{ route("backups.suppliers.automation.logs.data") }}',
            method: 'GET',
            data: { type: type },
            success: function(response) {
                if (response.success && response.logs.length > 0) {
                    let logsHtml = '';

                    response.logs.forEach(function(log) {
                        const levelClass = {
                            'error': 'text-danger',
                            'warning': 'text-warning',
                            'info': 'text-info',
                            'debug': 'text-secondary'
                        }[log.level] || 'text-muted';

                        const levelIcon = {
                            'error': 'fa fa-times-circle',
                            'warning': 'fa fa-exclamation-triangle',
                            'info': 'fa fa-info-circle',
                            'debug': 'fa fa-bug'
                        }[log.level] || 'fa fa-file-alt';

                        const levelBadge = {
                            'error': 'danger',
                            'warning': 'warning',
                            'info': 'info',
                            'debug': 'secondary'
                        }[log.level] || 'secondary';

                        logsHtml += `
                            <div class="border-bottom p-3 log-entry">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="${levelIcon} ${levelClass} fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-${levelBadge} me-2">${log.level.toUpperCase()}</span>
                                                <small class="text-muted">${log.timestamp}</small>
                                            </div>
                                        </div>
                                        <div class="log-message" style="white-space: pre-wrap; word-break: break-word;">
                                            ${escapeHtml(log.message)}
                                        </div>
                                        ${log.context ? `
                                            <details class="mt-2">
                                                <summary class="text-muted" style="cursor: pointer;">
                                                    <small><i class="fa fa-code me-1"></i>Ver contexto</small>
                                                </summary>
                                                <pre class="bg-light p-2 mt-2 rounded" style="font-size: 11px;">${escapeHtml(log.context)}</pre>
                                            </details>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#logsContainer').html(logsHtml);

                    // Update counters
                    updateCounters(response.counts);
                } else {
                    $('#logsContainer').html(`
                        <div class="text-center py-5">
                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron logs del tipo seleccionado.</p>
                        </div>
                    `);
                    updateCounters({ error: 0, warning: 0, info: 0, total: 0 });
                }

                // Auto-scroll to top
                $('#logsContainer').scrollTop(0);
            },
            error: function(xhr, status, error) {
                $('#logsContainer').html(`
                    <div class="text-center py-5">
                        <i class="fa fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <p class="text-danger">Error al cargar los logs: ${error}</p>
                        <button class="btn btn-sm btn-primary" onclick="loadLogs('${type}')">
                            <i class="fa fa-arrows-rotate me-1"></i>Reintentar
                        </button>
                    </div>
                `);
            }
        });
    }

    // Update counter badges
    function updateCounters(counts) {
        $('#errorCount').text(counts.error || 0);
        $('#warningCount').text(counts.warning || 0);
        $('#infoCount').text(counts.info || 0);
        $('#totalCount').text(counts.total || 0);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Make functions accessible globally
    window.loadLogs = loadLogs;
    window.refreshLogs = function() {
        loadLogs(currentLogType);
        toastr.success('Logs actualizados', 'Sistema');
    };
});
</script>
@endpush
