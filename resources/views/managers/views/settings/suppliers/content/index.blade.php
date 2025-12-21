@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Cola de Revisión de Contenido'])

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
                                <i class="fas fa-clock fs-7 text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Pendiente Revisión</p>
                                <h5 class="mb-0" id="pendingCount">{{ $stats['pending'] ?? 0 }}</h5>
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
                                <i class="fas fa-check-circle fs-7 text-success"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Aprobado</p>
                                <h5 class="mb-0" id="approvedCount">{{ $stats['approved'] ?? 0 }}</h5>
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
                                <i class="fas fa-times-circle fs-7 text-danger"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Rechazado</p>
                                <h5 class="mb-0" id="rejectedCount">{{ $stats['rejected'] ?? 0 }}</h5>
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
                                <i class="fas fa-chart-line fs-7 text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Calidad Promedio</p>
                                <h5 class="mb-0" id="avgQuality">{{ $stats['avg_quality'] ?? 0 }}%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-body mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select class="form-select" id="filterSupplier">
                        <option value="">Todos los proveedores</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filterStatus">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobado</option>
                        <option value="rejected">Rechazado</option>
                        <option value="published">Publicado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filterQuality">
                        <option value="">Todas las calidades</option>
                        <option value="high">Alta (≥ 80%)</option>
                        <option value="medium">Media (50-79%)</option>
                        <option value="low">Baja (< 50%)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="filterDate" placeholder="Fecha">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary w-100" id="refreshTableBtn">
                        <i class="fas fa-sync me-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="card card-body mb-3">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" id="bulkApproveBtn" disabled>
                    <i class="fas fa-check me-2"></i> Aprobar Seleccionados
                </button>
                <button type="button" class="btn btn-danger" id="bulkRejectBtn" disabled>
                    <i class="fas fa-times me-2"></i> Rechazar Seleccionados
                </button>
                <button type="button" class="btn btn-warning" id="bulkRegenerateBtn" disabled>
                    <i class="fas fa-redo me-2"></i> Regenerar Seleccionados
                </button>
                <span class="ms-auto text-muted" id="selectedCount">0 seleccionados</span>
            </div>
        </div>

        <!-- Content Table -->
        <div class="card card-body">
            <h5 class="mb-3">Cola de Contenido</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="contentTable">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Tipo</th>
                            <th>Calidad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
    let table;
    let selectedIds = [];

    // Initialize DataTable
    function initTable() {
        table = $('#contentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("manager.settings.suppliers.content.data") }}',
                data: function(d) {
                    d.supplier_id = $('#filterSupplier').val();
                    d.status = $('#filterStatus').val();
                    d.quality = $('#filterQuality').val();
                    d.date = $('#filterDate').val();
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<input type="checkbox" class="form-check-input content-checkbox" value="${data}">`;
                    }
                },
                { data: 'product', name: 'product' },
                { data: 'supplier', name: 'supplier' },
                { data: 'content_type', name: 'content_type' },
                { data: 'quality_score', name: 'quality_score' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[6, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }

    initTable();

    // Refresh table
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        refreshStats();
        toastr.info('Tabla actualizada', 'Contenido');
    });

    // Filter change
    $('#filterSupplier, #filterStatus, #filterQuality, #filterDate').on('change', function() {
        table.ajax.reload();
    });

    // Select all checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.content-checkbox').prop('checked', isChecked);
        updateSelectedIds();
    });

    // Individual checkbox change
    $(document).on('change', '.content-checkbox', function() {
        updateSelectedIds();
    });

    // Update selected IDs array
    function updateSelectedIds() {
        selectedIds = [];
        $('.content-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        $('#selectedCount').text(selectedIds.length + ' seleccionados');

        const hasSelection = selectedIds.length > 0;
        $('#bulkApproveBtn, #bulkRejectBtn, #bulkRegenerateBtn').prop('disabled', !hasSelection);
    }

    // Refresh stats
    function refreshStats() {
        $.ajax({
            url: '{{ route("manager.settings.suppliers.content.stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#pendingCount').text(response.data.pending);
                    $('#approvedCount').text(response.data.approved);
                    $('#rejectedCount').text(response.data.rejected);
                    $('#avgQuality').text(response.data.avg_quality + '%');
                }
            }
        });
    }

    // Bulk approve
    $('#bulkApproveBtn').on('click', function() {
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: '¿Aprobar contenido?',
            text: `Se aprobarán ${selectedIds.length} elementos seleccionados.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkAction('approve', selectedIds);
            }
        });
    });

    // Bulk reject
    $('#bulkRejectBtn').on('click', function() {
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: '¿Rechazar contenido?',
            text: `Se rechazarán ${selectedIds.length} elementos seleccionados.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkAction('reject', selectedIds);
            }
        });
    });

    // Bulk regenerate
    $('#bulkRegenerateBtn').on('click', function() {
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: '¿Regenerar contenido?',
            text: `Se regenerarán ${selectedIds.length} elementos seleccionados.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, regenerar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkAction('regenerate', selectedIds);
            }
        });
    });

    // Bulk action handler
    function bulkAction(action, ids) {
        $.ajax({
            url: '{{ route("manager.settings.suppliers.content.bulk-action") }}',
            method: 'POST',
            data: {
                action: action,
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Acción Masiva');
                    table.ajax.reload();
                    refreshStats();
                    selectedIds = [];
                    updateSelectedIds();
                    $('#selectAll').prop('checked', false);
                }
            },
            error: function() {
                toastr.error('Error al ejecutar la acción', 'Error');
            }
        });
    }

    // Individual approve
    $(document).on('click', '.approve-content', function() {
        const id = $(this).data('id');
        bulkAction('approve', [id]);
    });

    // Individual reject
    $(document).on('click', '.reject-content', function() {
        const id = $(this).data('id');
        bulkAction('reject', [id]);
    });

    // Individual regenerate
    $(document).on('click', '.regenerate-content', function() {
        const id = $(this).data('id');
        bulkAction('regenerate', [id]);
    });

    // View details
    $(document).on('click', '.view-content', function() {
        const id = $(this).data('id');
        window.location.href = '{{ route("manager.settings.suppliers.content.show", ":id") }}'.replace(':id', id);
    });

    // Publish content
    $(document).on('click', '.publish-content', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Publicar contenido?',
            text: 'El contenido se publicará en la tienda.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, publicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("manager.settings.suppliers.content.publish", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Contenido');
                            table.ajax.reload();
                            refreshStats();
                        }
                    },
                    error: function() {
                        toastr.error('Error al publicar contenido', 'Error');
                    }
                });
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
