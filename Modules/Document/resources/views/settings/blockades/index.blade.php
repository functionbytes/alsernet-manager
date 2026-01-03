@extends('layouts.theme')

@section('title', 'Configuración de Bloqueos de Productos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">Bloqueos de Productos</h4>
                    <p class="text-muted mb-0">Gestiona los bloqueos de productos por tipo de documento</p>
                </div>
                <a href="{{ route('settings.documents.configurations') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>

            <!-- Sync Statistics Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Sincronización de bloqueos de productos</h6>
                    <p class="text-muted mb-3">
                        Sincroniza los bloqueos de productos (DNI, ESCOPETA, RIFLE, CORTA) desde la base de datos externa de PrestaShop.
                    </p>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-light-secondary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-shield-alt fs-7 text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-muted small">Total bloqueos</p>
                                        <h5 class="mb-0" id="totalBlockades">{{ $totalBlockades }}</h5>
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
                                        <i class="fas fa-box fs-7 text-success"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-muted small">Productos únicos</p>
                                        <h5 class="mb-0" id="uniqueProducts">{{ $uniqueProducts }}</h5>
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
                                        <i class="fas fa-sync fs-7 text-info"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-muted small">Sincronizaciones</p>
                                        <h5 class="mb-0" id="blockadesSyncCount">{{ $syncCount }}</h5>
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
                                        <p class="mb-1 text-muted small">Última sincronización</p>
                                        <h6 class="mb-0 text-truncate" id="blockagesLastSync" title="{{ $lastSync }}">{{ $lastSync }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label small fw-semibold mb-1">Etiquetas a sincronizar</label>
                            <small class="text-muted d-block">Estas etiquetas se buscarán en la columna 'etiqueta' de las tablas del sistema externo.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="addLabelRowBtn">
                            <i class="fa fa-plus me-1"></i> Agregar etiqueta
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="labelsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">#</th>
                                    <th style="width: 70%;">Etiqueta</th>
                                    <th style="width: 15%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="labelsTableBody">
                                <!-- Rows will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 px-3" id="noLabelsMessage">
                        <i class="fa fa-info-circle me-2"></i>
                        <small>Haz clic en "Agregar etiqueta" para comenzar a agregar etiquetas de sincronización</small>
                    </div>

                    <div id="saveLabelsContainer" class="d-none mt-3">
                        <button type="button" class="btn btn-success w-100" id="saveBlockadeLabelsBtn">
                            <i class="fa fa-save me-2"></i> Guardar etiquetas
                        </button>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success w-100" id="syncBlockadesBtn">
                            <i class="fas fa-sync-alt me-2"></i> Sincronizar bloqueos
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-warning w-100" id="syncBlockadesFreshBtn">
                            <i class="fas fa-redo me-2"></i> Sincronizar (limpiar y volver a sincronizar)
                        </button>
                    </div>
                </div>
                </div>
            </div>

            <!-- Manual Blockades Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Agregar bloqueos manuales</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addBlockadeRowBtn">
                            <i class="fa fa-plus me-1"></i> Agregar fila
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="blockadeTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">Source ID</th>
                                    <th style="width: 20%;">Tipo</th>
                                    <th style="width: 15%;">Product ID</th>
                                    <th style="width: 15%;">Attribute ID</th>
                                    <th style="width: 25%;">Tipo de Documento</th>
                                    <th style="width: 10%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="blockadeTableBody">
                                <!-- Rows will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 px-3" id="noRowsMessage">
                        <i class="fa fa-info-circle me-2"></i>
                        <small>Haz clic en "Agregar fila" para comenzar a agregar bloqueos</small>
                    </div>

                    <div id="saveBlockadesContainer" class="d-none mt-3">
                        <button type="button" class="btn btn-success w-100" id="saveAllBlockadesBtn">
                            <i class="fa fa-save me-2"></i> Guardar todos los bloqueos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics by Document Type Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Bloqueos por tipo de documento</h6>
                    <div class="row">
                        @forelse($documentTypes as $docType)
                            <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                                <div class="card border-start border-primary border-4">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-semibold">{{ $docType->label }}</h6>
                                                <small class="text-muted"><code>{{ $docType->slug }}</code></small>
                                            </div>
                                            <div class="text-end">
                                                @if($docType->blockades_count > 0)
                                                    <span class="badge bg-primary fs-5">{{ $docType->blockades_count }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted fs-6">
                                                        <i class="fa fa-minus"></i>
                                                    </span>
                                                @endif
                                                <div class="small text-muted mt-1">productos</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    No hay tipos de documento activos configurados
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Blockades Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Bloqueos recientes</h6>
                    @if($recentBlockades->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo de documento</th>
                                        <th>Producto ID</th>
                                        <th>Atributo ID</th>
                                        <th>Source ID</th>
                                        <th>Fecha creación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBlockades as $blockade)
                                        <tr>
                                            <td>
                                                @if($blockade->documentType)
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        {{ $blockade->documentType->label }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($blockade->product_id)
                                                    <code class="bg-light px-2 py-1 rounded">#{{ $blockade->product_id }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($blockade->product_attribute_id)
                                                    <code class="bg-light px-2 py-1 rounded">#{{ $blockade->product_attribute_id }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">#{{ $blockade->source_id }}</code>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $blockade->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fa fa-circle-info me-2"></i>
                            No hay bloqueos registrados todavía
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let rowCounter = 0;
    let labelRowCounter = 0;

    // ========================================
    // LABELS TABLE MANAGEMENT
    // ========================================

    // Initialize labels table with existing labels
    const currentLabels = '{{ $currentLabels }}';
    if (currentLabels) {
        const labelsArray = currentLabels.split(',').map(l => l.trim()).filter(l => l);
        labelsArray.forEach(label => {
            addLabelRow(label);
        });
    }

    // Add new label row
    $('#addLabelRowBtn').on('click', function() {
        addLabelRow();
    });

    function addLabelRow(labelValue = '') {
        labelRowCounter++;
        const rowId = 'label-row-' + labelRowCounter;

        const row = `
            <tr data-row-id="${rowId}">
                <td class="text-center">
                    <span class="badge bg-secondary">${labelRowCounter}</span>
                </td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm text-uppercase"
                           name="labels[${labelRowCounter}]"
                           value="${labelValue}"
                           placeholder="Ej: DNI, RIFLE, ESCOPETA"
                           required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-label-btn" data-row-id="${rowId}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#labelsTableBody').append(row);
        updateLabelsVisibility();
    }

    // Remove label row
    $(document).on('click', '.remove-label-btn', function() {
        const rowId = $(this).data('row-id');
        $(`tr[data-row-id="${rowId}"]`).remove();
        updateLabelsVisibility();
        renumberLabelRows();
    });

    // Renumber label rows after deletion
    function renumberLabelRows() {
        $('#labelsTableBody tr').each(function(index) {
            $(this).find('.badge').text(index + 1);
        });
    }

    // Update visibility of labels messages and save button
    function updateLabelsVisibility() {
        const rowCount = $('#labelsTableBody tr').length;

        if (rowCount > 0) {
            $('#noLabelsMessage').addClass('d-none');
            $('#saveLabelsContainer').removeClass('d-none');
        } else {
            $('#noLabelsMessage').removeClass('d-none');
            $('#saveLabelsContainer').addClass('d-none');
        }
    }

    // ========================================
    // BLOCKADES TABLE MANAGEMENT
    // ========================================

    // Add new blockade row
    $('#addBlockadeRowBtn').on('click', function() {
        addBlockadeRow();
    });

    function addBlockadeRow() {
        rowCounter++;
        const rowId = 'row-' + rowCounter;

        const documentTypesOptions = `
            <option value="">Seleccionar...</option>
            @foreach($documentTypes as $type)
                <option value="{{ $type->id }}">{{ $type->label }}</option>
            @endforeach
        `;

        const row = `
            <tr data-row-id="${rowId}">
                <td>
                    <input type="number" class="form-control form-control-sm" name="blockades[${rowCounter}][source_id]" required>
                </td>
                <td>
                    <select class="form-select form-select-sm product-type-select" name="blockades[${rowCounter}][type]" data-row="${rowCounter}" required>
                        <option value="simple">Producto simple</option>
                        <option value="combination">Combinación</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm product-id-input" name="blockades[${rowCounter}][product_id]" data-row="${rowCounter}">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm attribute-id-input" name="blockades[${rowCounter}][attribute_id]" data-row="${rowCounter}" disabled>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="blockades[${rowCounter}][document_type_id]" required>
                        ${documentTypesOptions}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-row-btn" data-row-id="${rowId}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#blockadeTableBody').append(row);
        updateRowsVisibility();
    }

    // Remove row
    $(document).on('click', '.remove-row-btn', function() {
        const rowId = $(this).data('row-id');
        $(`tr[data-row-id="${rowId}"]`).remove();
        updateRowsVisibility();
    });

    // Toggle product type
    $(document).on('change', '.product-type-select', function() {
        const row = $(this).data('row');
        const type = $(this).val();
        const $productIdInput = $(`.product-id-input[data-row="${row}"]`);
        const $attributeIdInput = $(`.attribute-id-input[data-row="${row}"]`);

        if (type === 'simple') {
            $productIdInput.prop('disabled', false).prop('required', true);
            $attributeIdInput.prop('disabled', true).prop('required', false).val('');
        } else {
            $productIdInput.prop('disabled', true).prop('required', false).val('');
            $attributeIdInput.prop('disabled', false).prop('required', true);
        }
    });

    // Update visibility of messages and save button
    function updateRowsVisibility() {
        const rowCount = $('#blockadeTableBody tr').length;

        if (rowCount > 0) {
            $('#noRowsMessage').addClass('d-none');
            $('#saveBlockadesContainer').removeClass('d-none');
        } else {
            $('#noRowsMessage').removeClass('d-none');
            $('#saveBlockadesContainer').addClass('d-none');
        }
    }

    // Save all blockades
    $('#saveAllBlockadesBtn').on('click', function() {
        const $rows = $('#blockadeTableBody tr');
        const blockades = [];
        let hasErrors = false;

        $rows.each(function() {
            const $row = $(this);
            const sourceId = $row.find('input[name*="[source_id]"]').val();
            const type = $row.find('select[name*="[type]"]').val();
            const productId = $row.find('input[name*="[product_id]"]').val();
            const attributeId = $row.find('input[name*="[attribute_id]"]').val();
            const documentTypeId = $row.find('select[name*="[document_type_id]"]').val();

            if (!sourceId || !documentTypeId) {
                hasErrors = true;
                return false;
            }

            if (type === 'simple' && !productId) {
                hasErrors = true;
                return false;
            }

            if (type === 'combination' && !attributeId) {
                hasErrors = true;
                return false;
            }

            blockades.push({
                source_id: sourceId,
                product_id: type === 'simple' ? productId : null,
                product_attribute_id: type === 'combination' ? attributeId : null,
                document_type_id: documentTypeId
            });
        });

        if (hasErrors) {
            Swal.fire('Error', 'Por favor completa todos los campos requeridos', 'error');
            return;
        }

        if (blockades.length === 0) {
            Swal.fire('Error', 'No hay bloqueos para guardar', 'error');
            return;
        }

        // Send AJAX request
        $.ajax({
            url: '{{ route('settings.documents.blockades.store-bulk') }}',
            method: 'POST',
            data: {
                blockades: blockades,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: `${response.created} bloqueo(s) creado(s) exitosamente`,
                        icon: 'success',
                        confirmButtonText: 'Recargar página'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear bloqueos', 'error');
            }
        });
    });

    // Save labels
    $('#saveBlockadeLabelsBtn').on('click', function() {
        const $rows = $('#labelsTableBody tr');
        const labelsArray = [];
        let hasErrors = false;

        // Collect all labels from table rows
        $rows.each(function() {
            const $row = $(this);
            const labelValue = $row.find('input[name*="labels"]').val().trim().toUpperCase();

            if (!labelValue) {
                hasErrors = true;
                Swal.fire({
                    title: 'Validación',
                    text: 'Todas las etiquetas deben tener un valor',
                    icon: 'warning'
                });
                return false;
            }

            labelsArray.push(labelValue);
        });

        if (hasErrors) {
            return;
        }

        if (labelsArray.length === 0) {
            Swal.fire({
                title: 'Validación',
                text: 'Debes agregar al menos una etiqueta',
                icon: 'warning'
            });
            return;
        }

        // Convert array to comma-separated string
        const labels = labelsArray.join(',');

        $.ajax({
            url: '{{ route('settings.documents.blockades.save-labels') }}',
            method: 'POST',
            data: {
                labels: labels,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: `${labelsArray.length} etiqueta(s) guardadas exitosamente`,
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar etiquetas', 'error');
            }
        });
    });

    // Sync blockades
    $('#syncBlockadesBtn').on('click', function() {
        syncBlockades(false);
    });

    $('#syncBlockadesFreshBtn').on('click', function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esto eliminará todos los bloqueos actuales y los sincronizará de nuevo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                syncBlockades(true);
            }
        });
    });

    function syncBlockades(fresh) {
        const btn = fresh ? $('#syncBlockadesFreshBtn') : $('#syncBlockadesBtn');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('settings.documents.blockades.sync') }}',
            method: 'POST',
            data: {
                fresh: fresh,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#totalBlockades').text(response.total_blockades);
                    $('#blockadesSyncCount').text(parseInt($('#blockadesSyncCount').text()) + 1);
                    $('#blockagesLastSync').text('hace unos segundos');
                    Swal.fire({
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Recargar página'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire('Advertencia', response.message, 'warning');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al sincronizar', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    }

    // Add blockade
    $('#formAddBlockade').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            source_id: $('#blockadeSourceId').val(),
            document_type_id: $('#blockadeDocumentType').val(),
            _token: '{{ csrf_token() }}'
        };

        if ($('input[name="productType"]:checked').val() === 'simple') {
            formData.product_id = $('#blockadeIdProduct').val();
        } else {
            formData.product_attribute_id = $('#blockadeIdProductAttribute').val();
        }

        $.ajax({
            url: '{{ route('settings.documents.blockades.store') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Recargar página'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear bloqueo', 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
