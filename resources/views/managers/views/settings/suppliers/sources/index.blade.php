@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Fuentes de Datos - ' . $supplier->name])

    <div class="widget-content searchable-container list">

        <!-- Breadcrumb -->
        <div class="card card-body mb-3 bg-light-secondary">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('manager.settings.suppliers.index') }}">
                            <i class="fas fa-arrow-left me-2"></i> Proveedores
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Fuentes: {{ $supplier->name }}</li>
                </ol>
            </nav>
        </div>

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
                    <button type="button" class="btn btn-primary w-100" id="createSourceBtn">
                        <i class="fas fa-plus me-2"></i> Nueva Fuente
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary w-100" id="refreshTableBtn">
                        <i class="fas fa-sync me-2"></i> Actualizar
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-primary w-100" id="testAllSourcesBtn">
                        <i class="fas fa-flask me-2"></i> Probar Todas
                    </button>
                </div>
            </div>
        </div>

        <!-- Sources Table -->
        <div class="card card-body">
            <h5 class="mb-3">Fuentes Configuradas</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="sourcesTable">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>URL/Host</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Última Conexión</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="sourceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sourceModalTitle">Nueva Fuente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="sourceForm">
                    <input type="hidden" id="sourceId" name="id">
                    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sourceName" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Fuente <span class="text-danger">*</span></label>
                                <select class="form-select" id="sourceType" name="source_type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="website">Sitio Web</option>
                                    <option value="ftp">FTP</option>
                                    <option value="api">API REST</option>
                                    <option value="upload">Carga Manual</option>
                                    <option value="database">Base de Datos</option>
                                </select>
                            </div>
                            <div class="col-md-12" id="configContainer">
                                <!-- Dynamic configuration fields will be loaded here -->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prioridad</label>
                                <input type="number" class="form-control" id="sourcePriority" name="priority" value="10" min="1" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <select class="form-select" id="sourceIsActive" name="is_active">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ url('managers/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('managers/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<link rel="stylesheet" href="{{ url('managers/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

<script>
const supplierId = {{ $supplier->id }};

$(document).ready(function() {
    let table;

    // Initialize DataTable
    function initTable() {
        table = $('#sourcesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("manager.settings.suppliers.sources.data", $supplier->id) }}',
            columns: [
                { data: 'source_type', name: 'source_type' },
                { data: 'name', name: 'name' },
                { data: 'url', name: 'url' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'priority', name: 'priority' },
                { data: 'last_connection_at', name: 'last_connection_at' },
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
        toastr.info('Tabla actualizada', 'Fuentes');
    });

    // Create source
    $('#createSourceBtn').on('click', function() {
        $('#sourceForm')[0].reset();
        $('#sourceId').val('');
        $('#sourceModalTitle').text('Nueva Fuente');
        $('#configContainer').html('');
        $('#sourceModal').modal('show');
    });

    // Source type change - load dynamic config fields
    $('#sourceType').on('change', function() {
        const type = $(this).val();
        loadConfigFields(type);
    });

    function loadConfigFields(type) {
        let html = '';

        switch(type) {
            case 'website':
                html = `
                    <label class="form-label fw-semibold">URL del Sitio Web</label>
                    <input type="url" class="form-control mb-2" name="config[url]" placeholder="https://example.com">
                    <label class="form-label fw-semibold">Selectores CSS (JSON)</label>
                    <textarea class="form-control font-monospace" name="config[selectors]" rows="3" placeholder='{"title": ".product-title", "price": ".price"}'></textarea>
                `;
                break;
            case 'ftp':
                html = `
                    <label class="form-label fw-semibold">Host FTP</label>
                    <input type="text" class="form-control mb-2" name="config[host]" placeholder="ftp.example.com">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Usuario</label>
                            <input type="text" class="form-control mb-2" name="config[username]">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control mb-2" name="config[password]">
                        </div>
                    </div>
                    <label class="form-label fw-semibold">Directorio</label>
                    <input type="text" class="form-control" name="config[directory]" placeholder="/uploads">
                `;
                break;
            case 'api':
                html = `
                    <label class="form-label fw-semibold">URL de la API</label>
                    <input type="url" class="form-control mb-2" name="config[api_url]" placeholder="https://api.example.com/v1">
                    <label class="form-label fw-semibold">API Key</label>
                    <input type="text" class="form-control mb-2" name="config[api_key]">
                    <label class="form-label fw-semibold">Encabezados (JSON)</label>
                    <textarea class="form-control font-monospace" name="config[headers]" rows="2" placeholder='{"Content-Type": "application/json"}'></textarea>
                `;
                break;
            case 'database':
                html = `
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Host</label>
                            <input type="text" class="form-control mb-2" name="config[db_host]" placeholder="localhost">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Puerto</label>
                            <input type="number" class="form-control mb-2" name="config[db_port]" value="3306">
                        </div>
                    </div>
                    <label class="form-label fw-semibold">Nombre de la Base de Datos</label>
                    <input type="text" class="form-control mb-2" name="config[db_name]">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Usuario</label>
                            <input type="text" class="form-control mb-2" name="config[db_username]">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control mb-2" name="config[db_password]">
                        </div>
                    </div>
                `;
                break;
            case 'upload':
                html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Las cargas manuales no requieren configuración adicional.
                    </div>
                `;
                break;
        }

        $('#configContainer').html(html);
    }

    // Save source
    $('#sourceForm').on('submit', function(e) {
        e.preventDefault();

        const id = $('#sourceId').val();
        const url = id
            ? '{{ route("manager.settings.suppliers.sources.update", [$supplier->id, ":id"]) }}'.replace(':id', id)
            : '{{ route("manager.settings.suppliers.sources.store", $supplier->id) }}';
        const method = id ? 'PUT' : 'POST';

        const formData = $(this).serialize();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Fuentes');
                    $('#sourceModal').modal('hide');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al guardar la fuente';
                toastr.error(message, 'Error');
            }
        });
    });

    // Test connection
    $(document).on('click', '.test-source', function() {
        const id = $(this).data('id');
        const btn = $(this);
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("manager.settings.suppliers.sources.test", [$supplier->id, ":id"]) }}'.replace(':id', id),
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Prueba de Conexión');
                } else {
                    toastr.error(response.message, 'Prueba de Conexión');
                }
            },
            error: function() {
                toastr.error('Error al probar la conexión', 'Error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    // Delete source
    $(document).on('click', '.delete-source', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará la fuente "${name}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("manager.settings.suppliers.sources.destroy", [$supplier->id, ":id"]) }}'.replace(':id', id),
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Fuentes');
                            table.ajax.reload();
                        }
                    },
                    error: function() {
                        toastr.error('Error al eliminar la fuente', 'Error');
                    }
                });
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
