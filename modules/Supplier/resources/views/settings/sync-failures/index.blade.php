@extends('layouts.theme')

@section('title', 'Fallos de Sincronización')

@push('styles')
<style>
    /* Stats Cards with Pulse Animation */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid transparent;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .stat-card.stat-danger { border-left-color: #fa896b; }
    .stat-card.stat-warning { border-left-color: #fec90f; }
    .stat-card.stat-info { border-left-color: #539bff; }
    .stat-card.stat-primary { border-left-color: #90bb13; }

    .stat-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 24px;
    }
    .stat-icon.icon-danger { background: linear-gradient(135deg, #fa896b15, #fa896b25); color: #fa896b; }
    .stat-icon.icon-warning { background: linear-gradient(135deg, #fec90f15, #fec90f25); color: #fec90f; }
    .stat-icon.icon-info { background: linear-gradient(135deg, #539bff15, #539bff25); color: #539bff; }
    .stat-icon.icon-primary { background: linear-gradient(135deg, #90bb1315, #90bb1325); color: #90bb13; }

    /* Tab Navigation Refinement */
    .nav-tabs-modern {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 1.5rem;
    }
    .nav-tabs-modern .nav-link {
        border: none;
        color: #5a6a85;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        position: relative;
        transition: all 0.2s ease;
    }
    .nav-tabs-modern .nav-link:hover {
        color: #90bb13;
        background: transparent;
    }
    .nav-tabs-modern .nav-link.active {
        color: #90bb13;
        background: transparent;
    }
    .nav-tabs-modern .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #90bb13, #a5d216);
        border-radius: 3px 3px 0 0;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    /* Table Enhancements */
    .table-modern {
        font-size: 0.9rem;
    }
    .table-modern thead th {
        background: linear-gradient(180deg, #f8f9fa, #f3f4f6);
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #5a6a85;
        border-bottom: 2px solid #e9ecef;
        padding: 1rem 0.75rem;
    }
    .table-modern tbody tr {
        transition: background 0.15s ease;
    }
    .table-modern tbody tr:hover {
        background: #f8f9fa;
    }

    /* Status Badges */
    .badge-status {
        padding: 0.5em 0.85em;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        border-radius: 6px;
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.4rem 0.9rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Bulk Actions Bar */
    .bulk-actions-bar {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        display: none;
        animation: fadeInDown 0.3s ease;
    }
    .bulk-actions-bar.active {
        display: flex;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Error Message Truncation */
    .error-message {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #dc3545;
        background: #dc354510;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    /* Filter Panel */
    .filter-panel {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')

@include('core::components.card', ['title' => $pageTitle])

<div class="container-fluid">

    @include('core::components.alerts')

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-danger me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total de fallos</span>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_failures']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-warning me-3">
                            <i class="fas fa-rotate-right"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Fallos reintentables</span>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['retryable_failures']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-info me-3">
                            <i class="fas fa-code-merge"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Conflictos totales</span>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_conflicts']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-primary me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Sin resolver</span>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['unresolved_conflicts']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-body">

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-tabs-modern" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === 'failures' ? 'active' : '' }}"
                       href="?tab=failures" role="tab">
                        <i class="fas fa-triangle-exclamation me-2"></i>Fallos de sincronización
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === 'conflicts' ? 'active' : '' }}"
                       href="?tab=conflicts" role="tab">
                        <i class="fas fa-code-branch me-2"></i>Conflictos detectados
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Tab 1: Failures -->
                <div class="tab-pane fade {{ $tab === 'failures' ? 'show active' : '' }}">

                    <!-- Filter Panel -->
                    <div class="filter-panel">
                        <form method="GET" action="{{ route('settings.suppliers.sync-failures.index') }}">
                            <input type="hidden" name="tab" value="failures">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small mb-1">Tipo de sincronización</label>
                                    <select name="sync_type" class="form-select">
                                        <option value="">Todos los tipos</option>
                                        <option value="price" {{ $syncType === 'price' ? 'selected' : '' }}>Precio</option>
                                        <option value="product" {{ $syncType === 'product' ? 'selected' : '' }}>Producto</option>
                                        <option value="provider" {{ $syncType === 'provider' ? 'selected' : '' }}>Proveedor</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small mb-1">Buscar</label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="ID, mensaje de error..."
                                           value="{{ $searchKey }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($failures->count() > 0)
                        <!-- Bulk Actions Bar -->
                        <div class="bulk-actions-bar align-items-center justify-content-between" id="bulkActionsBar">
                            <div>
                                <span class="fw-bold text-primary">
                                    <span id="selectedCount">0</span> elementos seleccionados
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-action" onclick="bulkRetry()">
                                    <i class="fas fa-rotate-right me-2"></i>Reintentar seleccionados
                                </button>
                                <button type="button" class="btn btn-danger btn-action" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-2"></i>Eliminar seleccionados
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-action" onclick="clearSelection()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Failures Table -->
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                                        </th>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Supplier ID</th>
                                        <th>ERP ID</th>
                                        <th>Mensaje de error</th>
                                        <th class="text-center">Reintentos</th>
                                        <th>Última vez</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failures as $failure)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input failure-checkbox"
                                                       value="{{ $failure->id }}" onchange="updateBulkActions()">
                                            </td>
                                            <td class="fw-semibold">#{{ $failure->id }}</td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'price' => 'bg-info',
                                                        'product' => 'bg-warning',
                                                        'provider' => 'bg-primary'
                                                    ];
                                                    $typeIcons = [
                                                        'price' => 'fa-dollar-sign',
                                                        'product' => 'fa-box',
                                                        'provider' => 'fa-truck'
                                                    ];
                                                @endphp
                                                <span class="badge badge-status {{ $typeColors[$failure->sync_type] ?? 'bg-secondary' }}">
                                                    <i class="fas {{ $typeIcons[$failure->sync_type] ?? 'fa-question' }} me-1"></i>
                                                    {{ $failure->type_name }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $failure->supplier_id }}</td>
                                            <td class="text-muted">{{ $failure->erp_id ?? '-' }}</td>
                                            <td>
                                                <span class="error-message" title="{{ $failure->error_message }}">
                                                    {{ $failure->error_message }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $failure->retry_count >= 3 ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ $failure->retry_count }}/5
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $failure->last_retry_at ? $failure->last_retry_at->diffForHumans() : 'Nunca' }}
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    @if($failure->canRetry())
                                                        <button type="button" class="btn btn-success btn-sm btn-action"
                                                                onclick="retryFailure({{ $failure->id }})"
                                                                title="Reintentar">
                                                            <i class="fas fa-rotate-right"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-secondary btn-sm btn-action"
                                                                disabled title="No se puede reintentar">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-danger btn-sm btn-action"
                                                            onclick="deleteFailure({{ $failure->id }})"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Mostrando {{ $failures->firstItem() }} - {{ $failures->lastItem() }} de {{ $failures->total() }} fallos
                            </div>
                            <div>
                                {{ $failures->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-check-circle text-success"></i>
                            <h5 class="mt-3 mb-2">No hay fallos de sincronización</h5>
                            <p class="text-muted">Todas las sincronizaciones se han completado exitosamente.</p>
                        </div>
                    @endif
                </div>

                <!-- Tab 2: Conflicts -->
                <div class="tab-pane fade {{ $tab === 'conflicts' ? 'show active' : '' }}">

                    <!-- Filter Panel -->
                    <div class="filter-panel">
                        <form method="GET" action="{{ route('settings.suppliers.sync-failures.index') }}">
                            <input type="hidden" name="tab" value="conflicts">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small mb-1">Tipo de entidad</label>
                                    <select name="sync_type" class="form-select">
                                        <option value="">Todos los tipos</option>
                                        <option value="price" {{ $syncType === 'price' ? 'selected' : '' }}>Precio</option>
                                        <option value="product" {{ $syncType === 'product' ? 'selected' : '' }}>Producto</option>
                                        <option value="provider" {{ $syncType === 'provider' ? 'selected' : '' }}>Proveedor</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small mb-1">Estado</label>
                                    <select name="status" class="form-select">
                                        <option value="">Todos los estados</option>
                                        <option value="resolved">Resueltos</option>
                                        <option value="unresolved">Sin resolver</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($conflicts->count() > 0)
                        <!-- Conflicts Table -->
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Entity ID</th>
                                        <th>Estrategia</th>
                                        <th>Detectado</th>
                                        <th>Resuelto</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conflicts as $conflict)
                                        <tr>
                                            <td class="fw-semibold">#{{ $conflict->id }}</td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'price' => 'bg-info',
                                                        'product' => 'bg-warning',
                                                        'provider' => 'bg-primary'
                                                    ];
                                                    $typeIcons = [
                                                        'price' => 'fa-dollar-sign',
                                                        'product' => 'fa-box',
                                                        'provider' => 'fa-truck'
                                                    ];
                                                @endphp
                                                <span class="badge badge-status {{ $typeColors[$conflict->entity_type] ?? 'bg-secondary' }}">
                                                    <i class="fas {{ $typeIcons[$conflict->entity_type] ?? 'fa-question' }} me-1"></i>
                                                    {{ $conflict->entity_type_name }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $conflict->entity_id }}</td>
                                            <td>
                                                @php
                                                    $strategyColors = [
                                                        'erp_wins' => 'bg-primary',
                                                        'local_wins' => 'bg-info',
                                                        'manual' => 'bg-warning',
                                                        'merge' => 'bg-success'
                                                    ];
                                                @endphp
                                                <span class="badge badge-status {{ $strategyColors[$conflict->resolution_strategy] ?? 'bg-secondary' }}">
                                                    {{ $conflict->resolution_strategy_name }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $conflict->conflict_detected_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="text-muted small">
                                                {{ $conflict->resolved_at ? $conflict->resolved_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($conflict->isResolved())
                                                    <span class="badge badge-status bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Resuelto
                                                    </span>
                                                @else
                                                    <span class="badge badge-status bg-danger">
                                                        <i class="fas fa-clock me-1"></i>Sin resolver
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm btn-action"
                                                        onclick="viewConflictDetails({{ $conflict->id }})"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye me-1"></i>Ver detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Mostrando {{ $conflicts->firstItem() }} - {{ $conflicts->lastItem() }} de {{ $conflicts->total() }} conflictos
                            </div>
                            <div>
                                {{ $conflicts->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-shield-check text-success"></i>
                            <h5 class="mt-3 mb-2">No hay conflictos detectados</h5>
                            <p class="text-muted">Las sincronizaciones se están ejecutando sin conflictos.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Conflict Details Modal -->
<div class="modal fade" id="conflictDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-code-branch me-2"></i>Detalles del conflicto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conflictDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Bulk Selection Management
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.failure-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.failure-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = count;

    if (count > 0) {
        bulkBar.classList.add('active');
    } else {
        bulkBar.classList.remove('active');
        document.getElementById('selectAll').checked = false;
    }
}

function clearSelection() {
    document.querySelectorAll('.failure-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.failure-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Single Retry
function retryFailure(id) {
    if (!confirm('¿Deseas reintentar esta sincronización?')) return;

    fetch(`/manager/suppliers/sync-failures/${id}/retry`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            location.reload();
        } else {
            alert('✗ ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al reintentar: ' + error.message);
    });
}

// Single Delete
function deleteFailure(id) {
    if (!confirm('¿Estás seguro de eliminar este fallo?')) return;

    fetch(`/manager/suppliers/sync-failures/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            location.reload();
        } else {
            alert('✗ ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al eliminar: ' + error.message);
    });
}

// Bulk Retry
function bulkRetry() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Selecciona al menos un fallo para reintentar.');
        return;
    }

    if (!confirm(`¿Deseas reintentar ${ids.length} fallos seleccionados?`)) return;

    fetch('/manager/suppliers/sync-failures/bulk-retry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ids })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        alert('Error en reintentos masivos: ' + error.message);
    });
}

// Bulk Delete
function bulkDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Selecciona al menos un fallo para eliminar.');
        return;
    }

    if (!confirm(`¿Estás seguro de eliminar ${ids.length} fallos seleccionados?`)) return;

    fetch('/manager/suppliers/sync-failures/bulk-delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ids })
    })
    .then(response => response.json())
    .then(data => {
        alert('✓ ' + data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        alert('Error en eliminación masiva: ' + error.message);
    });
}

// View Conflict Details
function viewConflictDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('conflictDetailsModal'));
    const content = document.getElementById('conflictDetailsContent');

    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    fetch(`/manager/suppliers/sync-failures/conflicts/${id}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const conflict = data.conflict;
            content.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-database me-2"></i>Datos locales</h6>
                                <pre class="mb-0" style="font-size: 0.85rem;">${JSON.stringify(conflict.local_data, null, 2)}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-server me-2"></i>Datos ERP</h6>
                                <pre class="mb-0" style="font-size: 0.85rem;">${JSON.stringify(conflict.erp_data, null, 2)}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-success bg-opacity-10">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-check me-2"></i>Datos resueltos</h6>
                                <pre class="mb-0" style="font-size: 0.85rem;">${JSON.stringify(conflict.resolved_data, null, 2)}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <strong>Estrategia de resolución:</strong> ${conflict.resolution_strategy_name}<br>
                            <strong>Detectado:</strong> ${new Date(conflict.conflict_detected_at).toLocaleString()}<br>
                            ${conflict.resolved_at ? `<strong>Resuelto:</strong> ${new Date(conflict.resolved_at).toLocaleString()}` : ''}
                        </div>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles del conflicto.</div>';
        }
    })
    .catch(error => {
        content.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    });
}
</script>
@endpush
