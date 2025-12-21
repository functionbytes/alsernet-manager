@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Biblioteca de Prompts'])

    <div class="widget-content searchable-container list">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Actions -->
        <div class="card card-body mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="createPromptBtn">
                        <i class="fas fa-plus me-2"></i> Nuevo Prompt
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary w-100" id="refreshTableBtn">
                        <i class="fas fa-sync me-2"></i> Actualizar
                    </button>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterScope">
                        <option value="">Todos los alcances</option>
                        <option value="global">Global</option>
                        <option value="supplier">Por Proveedor</option>
                        <option value="category">Por Categoría</option>
                        <option value="source">Por Fuente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterSupplier">
                        <option value="">Todos los proveedores</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-globe fs-7 text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Prompts Globales</p>
                                <h5 class="mb-0" id="globalCount">{{ $stats['global'] ?? 0 }}</h5>
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
                                <i class="fas fa-truck fs-7 text-success"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Por Proveedor</p>
                                <h5 class="mb-0" id="supplierCount">{{ $stats['supplier'] ?? 0 }}</h5>
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
                                <i class="fas fa-tags fs-7 text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Por Categoría</p>
                                <h5 class="mb-0" id="categoryCount">{{ $stats['category'] ?? 0 }}</h5>
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
                                <p class="mb-1 text-muted">Activos</p>
                                <h5 class="mb-0" id="activeCount">{{ $stats['active'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prompts Table -->
        <div class="card card-body">
            <h5 class="mb-3">Biblioteca de Prompts</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="promptsTable">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Alcance</th>
                            <th>Proveedor/Categoría</th>
                            <th>Tipo de Contenido</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vista Previa del Prompt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Template Original</h6>
                            <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code id="previewTemplate"></code></pre>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Variables Disponibles</h6>
                            <div id="previewVariables" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
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
    let table;

    // Initialize DataTable
    function initTable() {
        table = $('#promptsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("manager.settings.suppliers.prompts.data") }}',
                data: function(d) {
                    d.scope = $('#filterScope').val();
                    d.supplier_id = $('#filterSupplier').val();
                }
            },
            columns: [
                { data: 'label', name: 'label' },
                { data: 'scope', name: 'scope' },
                { data: 'target', name: 'target', orderable: false },
                { data: 'content_type', name: 'content_type' },
                { data: 'priority', name: 'priority' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[4, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }

    initTable();

    // Refresh table
    $('#refreshTableBtn').on('click', function() {
        table.ajax.reload();
        toastr.info('Tabla actualizada', 'Prompts');
    });

    // Filter change
    $('#filterScope, #filterSupplier').on('change', function() {
        table.ajax.reload();
    });

    // Create prompt
    $('#createPromptBtn').on('click', function() {
        window.location.href = '{{ route("manager.settings.suppliers.prompts.create") }}';
    });

    // Edit prompt
    $(document).on('click', '.edit-prompt', function() {
        const id = $(this).data('id');
        window.location.href = '{{ route("manager.settings.suppliers.prompts.edit", ":id") }}'.replace(':id', id);
    });

    // Preview prompt
    $(document).on('click', '.preview-prompt', function() {
        const id = $(this).data('id');

        $.ajax({
            url: '{{ route("manager.settings.suppliers.prompts.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const prompt = response.data;
                    $('#previewTemplate').text(prompt.template);

                    // Display variables
                    let varsHtml = '<ul class="list-unstyled">';
                    if (prompt.variables && prompt.variables.length > 0) {
                        prompt.variables.forEach(v => {
                            varsHtml += `<li class="mb-2">
                                <code>{{ ${v.key} }}</code>
                                <small class="text-muted d-block">${v.description || 'Sin descripción'}</small>
                            </li>`;
                        });
                    } else {
                        varsHtml += '<li class="text-muted">No hay variables definidas</li>';
                    }
                    varsHtml += '</ul>';
                    $('#previewVariables').html(varsHtml);

                    $('#previewModal').modal('show');
                }
            },
            error: function() {
                toastr.error('Error al cargar el prompt', 'Error');
            }
        });
    });

    // Toggle active status
    $(document).on('click', '.toggle-prompt', function() {
        const id = $(this).data('id');

        $.ajax({
            url: '{{ route("manager.settings.suppliers.prompts.toggle", ":id") }}'.replace(':id', id),
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Prompts');
                    table.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Error al cambiar estado', 'Error');
            }
        });
    });

    // Duplicate prompt
    $(document).on('click', '.duplicate-prompt', function() {
        const id = $(this).data('id');

        $.ajax({
            url: '{{ route("manager.settings.suppliers.prompts.duplicate", ":id") }}'.replace(':id', id),
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Prompts');
                    table.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Error al duplicar el prompt', 'Error');
            }
        });
    });

    // Delete prompt
    $(document).on('click', '.delete-prompt', function() {
        const id = $(this).data('id');
        const label = $(this).data('label');

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el prompt "${label}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("manager.settings.suppliers.prompts.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Prompts');
                            table.ajax.reload();
                        }
                    },
                    error: function() {
                        toastr.error('Error al eliminar el prompt', 'Error');
                    }
                });
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
