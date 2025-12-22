@extends('layouts.managers')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formDocumentType" action="{{ route('manager.settings.documents.types.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">Crear tipo de documento</h5>
                                <p class="card-subtitle mb-0 mt-2">Complete la información para registrar un nuevo tipo de documento.</p>
                            </div>
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-light">
                                Volver
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       name="slug" value="{{ old('slug') }}"
                                       placeholder="ej: dni, pasaporte, licencia-armas"
                                       pattern="[a-z0-9_\-]+" required>
                                <small class="text-muted">Identificador único, solo minúsculas, números y guiones</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Etiqueta (Label)</label>
                                <input type="text" class="form-control @error('label') is-invalid @enderror"
                                       name="label" value="{{ old('label') }}"
                                       placeholder="ej: Documento de Identidad">
                                <small class="text-muted">Etiqueta mostrada en la interfaz</small>
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Icono (Font Awesome)</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                       id="icon" name="icon" value="{{ old('icon', 'fa fa-file') }}"
                                       placeholder="fa fa-file">
                                <small class="text-muted">Vista previa: <i id="iconPreview" class="{{ old('icon', 'fa fa-file') }} ms-1"></i></small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                       name="color" value="{{ old('color', '#6c757d') }}"
                                       style="height: 38px; width: 100%;">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Orden de visualización</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       name="sort_order" value="{{ old('sort_order', 0) }}"
                                       min="0" placeholder="0">
                                <small class="text-muted">Menor número aparece primero</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Multiplicador SLA</label>
                                <input type="number" class="form-control @error('sla_multiplier') is-invalid @enderror"
                                       name="sla_multiplier" value="{{ old('sla_multiplier', 1.0) }}"
                                       min="0" max="100" step="0.1" placeholder="1.0">
                                <small class="text-muted">Factor de tiempo para SLA</small>
                                @error('sla_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-12 mb-3">
                                <div class="alert alert-info mb-0">
                                    <strong>Traducciones:</strong> Las etiquetas y descripciones se configuran en
                                    <code>resources/lang/{locale}/documents.php</code> usando la clave <code>types.{slug}</code>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Types Configuration -->
                        <div class="border-top pt-4 mt-4">
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fas fa-tags text-primary me-2"></i>Configuración de tipos de venta</h6>
                                <small class="text-muted">Define qué tipos de bloqueo (sale_types) califican para cada condición</small>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-shield-alt text-muted me-1"></i>Tipos que califican como "Arma"
                                    </label>
                                    <input type="text"
                                           class="form-control sale-types-input @error('weapon_sale_types') is-invalid @enderror"
                                           id="weapon_sale_types_input"
                                           value="{{ old('weapon_sale_types_raw', 'escopeta,rifle,corta') }}"
                                           placeholder="escopeta,rifle,corta">
                                    <small class="text-muted">
                                        Separa los tipos con comas. Estos valores se comparan con <code>getSaleType()</code>
                                    </small>
                                    @error('weapon_sale_types')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-id-card text-muted me-1"></i>Tipos que califican como "DNI solo"
                                    </label>
                                    <input type="text"
                                           class="form-control sale-types-input @error('dni_only_sale_types') is-invalid @enderror"
                                           id="dni_only_sale_types_input"
                                           value="{{ old('dni_only_sale_types_raw', 'dni') }}"
                                           placeholder="dni">
                                    <small class="text-muted">
                                        Separa los tipos con comas. Usualmente solo "dni"
                                    </small>
                                    @error('dni_only_sale_types')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-12">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Nota:</strong> Estos valores se comparan con el resultado de <code>getSaleType()</code>
                                        que consulta la tabla <code>document_product_blockades</code>. Asegúrate de usar los mismos valores
                                        que están en la columna <code>blockade_type</code>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Stages -->
                        <div class="border-top pt-4 mt-4">
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fas fa-route text-primary me-2"></i>Etapas de validación</h6>
                                <small class="text-muted">Configura el flujo de validación con orden y condiciones</small>
                            </div>

                            <div id="validationStagesContainer" class="mb-3">
                                @if($validatorGroups->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No hay grupos validadores configurados.
                                        <a href="{{ route('manager.settings.documents.groups') }}" class="alert-link">Crear grupos primero</a>
                                    </div>
                                @else
                                    <div id="stagesList">
                                        @foreach($validatorGroups as $index => $group)
                                        <div class="card border mb-3 stage-item" data-key="{{ $group->key }}">
                                            <div class="card-header bg-light-subtle border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">
                                                            <i class="fas fa-layer-group me-2" style="color: #90bb13;"></i>
                                                            {{ $group->name }}
                                                        </h6>
                                                        @if($group->description)
                                                            <small class="text-muted">{{ $group->description }}</small>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-primary-subtle text-primary">{{ $group->key }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <!-- Order -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-sort-numeric-up me-1 text-muted"></i>Orden
                                                        </label>
                                                        <input type="number"
                                                               class="form-control stage-order"
                                                               value="{{ $index + 1 }}"
                                                               min="1">
                                                        <small class="text-muted">Las etapas se ejecutan del menor al mayor</small>
                                                    </div>

                                                    <!-- Estado dropdown -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-toggle-on me-1 text-muted"></i>Estado
                                                        </label>
                                                        <select class="form-select stage-enabled-select">
                                                            <option value="0" selected>Deshabilitada</option>
                                                            <option value="1">Habilitada</option>
                                                        </select>
                                                    </div>

                                                    <!-- Conditions multi-select -->
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-filter me-1" style="color: #90bb13;"></i>Condiciones
                                                            <small class="text-muted fw-normal d-block mt-1">Cuándo ejecutar esta etapa (opcional)</small>
                                                        </label>

                                                        @if($validationConditions->isEmpty())
                                                            <div class="alert alert-warning bg-warning-subtle border-warning mb-0">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                No hay condiciones configuradas.
                                                                <a href="{{ route('manager.settings.documents.conditions') }}" class="alert-link">Crear condiciones</a>
                                                            </div>
                                                        @else
                                                            <select multiple class="form-select select2-conditions stage-conditions-select" data-placeholder="Seleccionar condiciones...">
                                                                @foreach($validationConditions as $validationCondition)
                                                                    <option value="{{ $validationCondition->key }}"
                                                                            data-description="{{ $validationCondition->description }}"
                                                                            data-sale-types="{{ implode(', ', $validationCondition->sale_types) }}">
                                                                        {{ $validationCondition->name }} ({{ $validationCondition->key }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="alert alert-info bg-info-subtle border-info mt-3 mb-0">
                                                                <i class="fas fa-lightbulb me-2"></i>
                                                                <small>Si no seleccionas condiciones, la etapa siempre se ejecutará</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        <strong>Tip:</strong> Marca las etapas que deseas incluir y configura su orden y condiciones.
                                        Las condiciones permiten que ciertas etapas solo se ejecuten en documentos específicos.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Requisitos -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-paperclip text-primary me-2"></i>Requisitos de documentos
                                    </h6>
                                    <small class="text-muted">Archivos que el usuario debe subir para este tipo</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="addRequirement">
                                    <i class="fas fa-plus me-1"></i>Agregar requisito
                                </button>
                            </div>

                            <div id="requirementsContainer">
                                <div class="text-center py-5" id="noRequirementsMessage">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-1">No hay requisitos de documentos configurados</p>
                                    <small class="text-muted">Haz clic en <strong>"Agregar requisito"</strong> para comenzar</small>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="border-top pt-3 mt-4">
                            <button type="submit" class="btn btn-primary w-100 mb-1">
                                Crear
                            </button>
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-secondary w-100">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let requirementIndex = 0;

    // Icon preview
    $('#icon').on('input', function() {
        $('#iconPreview').attr('class', $(this).val() + ' ms-1');
    });

    // Add requirement
    $('#addRequirement').on('click', function() {
        $('#noRequirementsMessage').hide();

        const index = requirementIndex++;

        // Build language tabs
        let langTabs = '';
        let langContent = '';

        @foreach($langs as $langIndex => $lang)
        langTabs += `
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $langIndex === 0 ? 'active' : '' }}"
                        id="req${index}-lang{{ $lang->id }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#req${index}-lang{{ $lang->id }}"
                        type="button" role="tab">
                    {{ $lang->title }}
                </button>
            </li>
        `;

        langContent += `
            <div class="tab-pane fade {{ $langIndex === 0 ? 'show active' : '' }}"
                 id="req${index}-lang{{ $lang->id }}"
                 role="tabpanel">
                <input type="hidden" name="requirements[${index}][translations][{{ $langIndex }}][lang_id]" value="{{ $lang->id }}">
                <div class="row">
                    <div class="col-lg-6 mb-2">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"
                               name="requirements[${index}][translations][{{ $langIndex }}][name]" required>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label">Texto de ayuda</label>
                        <input type="text" class="form-control"
                               name="requirements[${index}][translations][{{ $langIndex }}][help_text]">
                    </div>
                </div>
            </div>
        `;
        @endforeach

        const html = `
            <div class="border rounded p-3 bg-light mb-3 requirement-item" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary">Requisito #${index + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-requirement">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar
                    </button>
                </div>

                <!-- Basic Fields -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-4">
                        <label class="form-label">
                            <i class="fas fa-key text-muted me-1"></i>Clave <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="requirements[${index}][key]" placeholder="ej: dni_frontal" required>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">
                            <i class="fas fa-weight-hanging text-muted me-1"></i>Tamaño máx (KB)
                        </label>
                        <input type="number" class="form-control" name="requirements[${index}][max_file_size]" value="5120" min="1" placeholder="5120">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">
                            <i class="fas fa-file-alt text-muted me-1"></i>Extensiones permitidas
                        </label>
                        <input type="text" class="form-control extensions-input" name="requirements[${index}][allowed_extensions][]" value="pdf,jpg,jpeg,png" placeholder="pdf,jpg,jpeg,png">
                    </div>
                </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="requirements[${index}][is_required]" value="1" checked id="is_required_${index}">
                                <label class="form-check-label" for="is_required_${index}">Obligatorio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="requirements[${index}][accepts_multiple]" value="1" id="accepts_multiple_${index}">
                                <label class="form-check-label" for="accepts_multiple_${index}">Múltiples archivos</label>
                            </div>
                        </div>
                    </div>

                    <!-- Language Translations -->
                    <div class="border-top pt-3">
                        <h6 class="mb-2">Traducciones</h6>
                        <ul class="nav nav-tabs mb-3" id="req${index}Tabs" role="tablist">
                            ${langTabs}
                        </ul>
                        <div class="tab-content">
                            ${langContent}
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#requirementsContainer').append(html);

        $('.requirement-item').last().find('.remove-requirement').on('click', function() {
            $(this).closest('.requirement-item').remove();
            if ($('.requirement-item').length === 0) {
                $('#noRequirementsMessage').show();
            }
        });
    });

    // Initialize Select2 for conditions with custom templates
    $('.select2-conditions').select2({
        placeholder: 'Seleccionar condiciones...',
        allowClear: true,
        width: '100%',
        templateResult: formatConditionOption,
        templateSelection: formatConditionSelection
    });

    // Format option for dropdown with full details
    function formatConditionOption(option) {
        if (!option.id) {
            return option.text;
        }

        const $option = $(option.element);
        const description = $option.data('description');
        const saleTypes = $option.data('sale-types');

        let html = '<div class="select2-option-content">';
        html += '<div class="fw-semibold">' + option.text + '</div>';

        if (description) {
            html += '<small class="text-muted d-block">' + description + '</small>';
        }

        if (saleTypes) {
            html += '<div class="mt-1">';
            saleTypes.split(', ').forEach(function(type) {
                if (type.trim()) {
                    html += '<span class="badge bg-info-subtle text-info me-1 small">' + type + '</span>';
                }
            });
            html += '</div>';
        }

        html += '</div>';
        return $(html);
    }

    // Format selected item (just the name)
    function formatConditionSelection(option) {
        return option.text;
    }

    // Fix for HTML5 validation: Manage 'required' attribute in tabs
    // This prevents "An invalid form control is not focusable" error
    function updateRequiredInTabs() {
        // Remove required from all hidden tab inputs
        $('.tab-pane:not(.active) input').each(function() {
            if ($(this).attr('required') !== undefined) {
                $(this).removeAttr('required').attr('data-was-required', 'true');
            }
        });

        // Restore required to active tab inputs
        $('.tab-pane.active input[data-was-required="true"]').each(function() {
            $(this).attr('required', 'required').removeAttr('data-was-required');
        });
    }

    // Run on page load
    updateRequiredInTabs();

    // Run whenever a tab is shown (Bootstrap 5 tab event)
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        updateRequiredInTabs();
    });

    // Handle extensions array conversion and validation stages
    $('#formDocumentType').on('submit', function(e) {
        // Handle extensions array conversion
        $('.extensions-input').each(function() {
            const value = $(this).val();
            const match = $(this).attr('name').match(/requirements\[(\d+)\]/);
            if (!match) return;

            const index = match[1];
            $(this).remove();

            if (value) {
                value.split(',').map(ext => ext.trim()).filter(ext => ext).forEach(ext => {
                    $('<input>').attr({
                        type: 'hidden',
                        name: `requirements[${index}][allowed_extensions][]`,
                        value: ext
                    }).appendTo('#formDocumentType');
                });
            }
        });

        // Build validation_stages array with order and conditions
        const validationStages = [];
        $('.stage-item').each(function() {
            const $stageItem = $(this);
            const key = $stageItem.data('key');
            const isEnabled = $stageItem.find('.stage-enabled-select').val() === '1';

            // Only include enabled stages
            if (!isEnabled) return;

            const order = parseInt($stageItem.find('.stage-order').val()) || 1;

            // Build conditions object from selected conditions
            const conditions = {};
            const selectedConditions = $stageItem.find('.stage-conditions-select').val() || [];

            selectedConditions.forEach(function(conditionKey) {
                conditions[conditionKey] = true;
            });

            validationStages.push({
                key: key,
                order: order,
                conditions: conditions
            });
        });

        // Sort by order
        validationStages.sort((a, b) => a.order - b.order);

        // Remove old validation_stages inputs if any
        $('input[name^="validation_stages"]').remove();

        // Add validation_stages as JSON array
        validationStages.forEach((stage, index) => {
            $('<input>').attr({
                type: 'hidden',
                name: `validation_stages[${index}][key]`,
                value: stage.key
            }).appendTo('#formDocumentType');

            $('<input>').attr({
                type: 'hidden',
                name: `validation_stages[${index}][order]`,
                value: stage.order
            }).appendTo('#formDocumentType');

            // Add conditions dynamically
            Object.keys(stage.conditions).forEach(conditionKey => {
                $('<input>').attr({
                    type: 'hidden',
                    name: `validation_stages[${index}][conditions][${conditionKey}]`,
                    value: stage.conditions[conditionKey] ? '1' : '0'
                }).appendTo('#formDocumentType');
            });
        });
    });
});
</script>
@endpush
