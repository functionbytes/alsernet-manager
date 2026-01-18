@extends('layouts.theme')

@section('title', 'Logs del Sistema de Automatización')

@section('content')

    @include('core::components.card', ['title' => 'Logs del Sistema de Automatización'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <!-- System Logs Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Logs del sistema de automatización</h5>
                        <p class="small mb-0 text-muted">Visualiza y descarga los registros de eventos del sistema de automatización de proveedores</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.suppliers.automation.index') }}" class="btn btn-secondary">
                            Volver
                        </a>
                        <button type="button" class="btn btn-secondary" id="downloadLogsBtn">
                            Descargar logs
                        </button>
                        <button type="button" class="btn btn-primary" id="refreshLogsBtn">
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Errores</h6>
                                <h2 id="errorCount" class="fw-bold">-</h2>
                                <small class="text-muted">Logs de tipo ERROR</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Advertencias</h6>
                                <h2 id="warningCount" class="fw-bold">-</h2>
                                <small class="text-muted">Logs de tipo WARNING</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Información</h6>
                                <h2 id="infoCount" class="fw-bold">-</h2>
                                <small class="text-muted">Logs de tipo INFO</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-2">Total de logs</h6>
                                <h2 id="totalCount" class="fw-bold">-</h2>
                                <small class="text-muted">Todos los registros</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Section -->
            <div class="card-body border-bottom">
                <div class="row g-3 align-items-end">
                    <!-- Search -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-2">Buscar en logs</label>
                        <input type="text" id="searchLogs" class="form-control" placeholder="Buscar por mensaje...">
                    </div>

                    <!-- Filter by Type -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-2">Filtrar por tipo</label>
                        <select id="logTypeFilter" class="form-select select2">
                            <option value="error" selected>Errores</option>
                            <option value="warning">Advertencias</option>
                            <option value="info">Información</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>

                    <!-- Limit -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-2">Límite</label>
                        <select id="logLimit" class="form-select select2">
                            <option value="50">50 registros</option>
                            <option value="100" selected>100 registros</option>
                            <option value="200">200 registros</option>
                            <option value="500">500 registros</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Logs Content -->
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 fw-bold">Registros del sistema</h6>
                        <p class="text-muted small mb-0 mt-1">
                            Mostrando <span id="currentCount">0</span> registros
                            <span id="lastUpdate" class="ms-2"></span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                            Limpiar filtros
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" id="scrollTopBtn">
                            Ir arriba
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" id="scrollBottomBtn">
                            Ir abajo
                        </button>
                    </div>
                </div>

                <!-- Logs Container -->
                <div class="card border shadow-sm">
                    <div class="card-body p-0">
                        <div id="logsContainer" style="max-height: 600px; overflow-y: auto; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; background-color: #f8f9fa;">
                            <div class="text-center py-5">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3 text-muted">Cargando logs del sistema...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="alert alert-light border-0 mt-3 mb-0">
                    <div class="d-flex gap-3 align-items-center flex-wrap small">
                        <div>
                            <span class="badge bg-black">ERROR</span>
                            <span class="text-muted ms-1">Fallos críticos del sistema</span>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <span class="badge bg-secondary">WARNING</span>
                            <span class="text-muted ms-1">Advertencias de funcionamiento</span>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <span class="badge bg-secondary">INFO</span>
                            <span class="text-muted ms-1">Información del sistema</span>
                        </div>
                        <div class="ms-auto text-muted">
                            Actualización automática cada 30 segundos
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
    let currentSearch = '';
    let currentLimit = 100;
    let autoRefreshInterval;

    // Initialize Select2
    $('#logTypeFilter').select2({
        minimumResultsForSearch: Infinity
    });
    $('#logLimit').select2({
        minimumResultsForSearch: Infinity
    });

    // Load logs on page load
    loadLogs();

    // Handle log type filter change
    $('#logTypeFilter').on('change', function() {
        currentLogType = $(this).val();
        loadLogs();
    });

    // Handle search with debounce
    let searchTimeout;
    $('#searchLogs').on('keyup', function() {
        clearTimeout(searchTimeout);
        currentSearch = $(this).val();
        searchTimeout = setTimeout(function() {
            loadLogs();
        }, 500);
    });

    // Handle limit change
    $('#logLimit').on('change', function() {
        currentLimit = $(this).val();
        loadLogs();
    });

    // Download logs button
    $('#downloadLogsBtn').on('click', function() {
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("settings.suppliers.automation.logs.download") }}',
            html: `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="type" value="${currentLogType}">
            `
        });
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Refresh button
    $('#refreshLogsBtn').on('click', function() {
        loadLogs();
        toastr.success('Logs actualizados', 'Sistema');
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#searchLogs').val('');
        currentSearch = '';
        currentLogType = 'error';
        $('#logError').prop('checked', true);
        loadLogs();
    });

    // Scroll buttons
    $('#scrollTopBtn').on('click', function() {
        $('#logsContainer').animate({ scrollTop: 0 }, 300);
    });

    $('#scrollBottomBtn').on('click', function() {
        const container = $('#logsContainer');
        container.animate({ scrollTop: container[0].scrollHeight }, 300);
    });

    // Function to load logs via AJAX
    function loadLogs() {
        // Show loading state
        $('#logsContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted small">Cargando logs...</p>
            </div>
        `);

        $.ajax({
            url: '{{ route("settings.suppliers.automation.logs.data") }}',
            method: 'GET',
            data: {
                type: currentLogType,
                search: currentSearch,
                limit: currentLimit
            },
            success: function(response) {
                if (response.success && response.logs.length > 0) {
                    let logsHtml = '';

                    response.logs.forEach(function(log, index) {
                        const levelBadge = log.level === 'error' ? 'bg-black' : 'bg-secondary';
                        const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-light';

                        logsHtml += `
                            <div class="border-bottom p-3 log-entry ${rowClass}">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="min-width: 80px;">
                                        <span class="badge ${levelBadge}">${log.level.toUpperCase()}</span>
                                    </div>
                                    <div style="min-width: 140px;" class="text-muted small">
                                        ${log.timestamp}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="log-message" style="white-space: pre-wrap; word-break: break-word; line-height: 1.6;">
                                            ${escapeHtml(log.message)}
                                        </div>
                                        ${log.context ? `
                                            <details class="mt-2">
                                                <summary class="text-primary" style="cursor: pointer; user-select: none;">
                                                    <small>Ver contexto detallado</small>
                                                </summary>
                                                <pre class="bg-black text-white p-3 mt-2 rounded" style="font-size: 11px; overflow-x: auto;">${escapeHtml(log.context)}</pre>
                                            </details>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#logsContainer').html(logsHtml);
                    $('#currentCount').text(response.logs.length);

                    // Update counters
                    updateCounters(response.counts);

                    // Update last update time
                    const now = new Date();
                    $('#lastUpdate').text('Última actualización: ' + now.toLocaleTimeString());
                } else {
                    $('#logsContainer').html(`
                        <div class="text-center py-5">
                            <div class="display-1 text-muted">📋</div>
                            <h6 class="text-muted mt-3">No se encontraron logs</h6>
                            <p class="text-muted small">No hay registros que coincidan con los filtros aplicados</p>
                        </div>
                    `);
                    $('#currentCount').text('0');
                    updateCounters({ error: 0, warning: 0, info: 0, total: 0 });
                }

                // Auto-scroll to top
                $('#logsContainer').scrollTop(0);
            },
            error: function(xhr, status, error) {
                $('#logsContainer').html(`
                    <div class="text-center py-5">
                        <div class="display-1 text-muted">⚠️</div>
                        <h6 class="text-muted mt-3">Error al cargar logs</h6>
                        <p class="text-muted small">${error}</p>
                        <button class="btn btn-sm btn-primary mt-2" onclick="loadLogs()">
                            Reintentar
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
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Auto-refresh every 30 seconds
    autoRefreshInterval = setInterval(function() {
        loadLogs();
    }, 30000);

    // Make functions accessible globally
    window.loadLogs = loadLogs;

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        clearInterval(autoRefreshInterval);
    });
});
</script>
@endpush
