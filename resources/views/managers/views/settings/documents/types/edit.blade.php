@extends('layouts.managers')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formDocumentType" action="{{ route('manager.settings.documents.types.update', $documentType->slug) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">Editar tipo de documento</h5>
                                <p class="card-subtitle mb-0 mt-2">Actualice la información del tipo de documento.</p>
                            </div>
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-light">
                                Volver
                            </a>
                        </div>

                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs mb-4" id="documentTypeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active"
                                        id="basic-info-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#basic-info"
                                        type="button" role="tab">
                                    <i class="fas fa-info-circle me-1"></i> Información básica
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link"
                                        id="validation-stages-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#validation-stages"
                                        type="button" role="tab">
                                    <i class="fas fa-route me-1"></i> Etapas de validación
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link"
                                        id="requirements-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#requirements"
                                        type="button" role="tab">
                                    <i class="fas fa-paperclip me-1"></i> Requisitos de documentos
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="documentTypeTabsContent">
                            <!-- Tab 1: Información Básica -->
                            <div class="tab-pane fade show active"
                                 id="basic-info"
                                 role="tabpanel">

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       name="slug" value="{{ old('slug', $documentType->slug) }}"
                                       pattern="[a-z0-9_\-]+" required>
                                <small class="text-muted">Identificador único, solo minúsculas, números y guiones</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Etiqueta (Label)</label>
                                <input type="text" class="form-control @error('label') is-invalid @enderror"
                                       name="label" value="{{ old('label', $documentType->label) }}"
                                       placeholder="ej: Documento de Identidad">
                                <small class="text-muted">Etiqueta mostrada en la interfaz</small>
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                                    <option value="1" {{ old('is_active', $documentType->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active', $documentType->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Icono (Font Awesome)</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                       id="icon" name="icon" value="{{ old('icon', $documentType->icon) }}"
                                       placeholder="fa fa-file">
                                <small class="text-muted">Vista previa: <i id="iconPreview" class="{{ old('icon', $documentType->icon) }} ms-1"></i></small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                       name="color" value="{{ old('color', $documentType->color) }}"
                                       style="height: 38px; width: 100%;">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Orden de visualización</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       name="sort_order" value="{{ old('sort_order', $documentType->sort_order) }}"
                                       min="0" placeholder="0">
                                <small class="text-muted">Menor número aparece primero</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Multiplicador SLA</label>
                                <input type="number" class="form-control @error('sla_multiplier') is-invalid @enderror"
                                       name="sla_multiplier" value="{{ old('sla_multiplier', $documentType->sla_multiplier) }}"
                                       min="0" max="100" step="0.1" placeholder="1.0">
                                <small class="text-muted">Factor de tiempo para SLA</small>
                                @error('sla_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-12 mb-3">
                                <div class="alert alert-info mb-0">
                                    <strong>Traducciones:</strong> Clave actual: <code>types.{{ $documentType->slug }}</code>
                                    <br>
                                    <small>Etiqueta actual: {{ $documentType->getLabel() }} | Descripción: {{ Str::limit($documentType->getDescription(), 50) ?: '(Sin descripción)' }}</small>
                                </div>
                            </div>
                        </div>

                            </div>
                            <!-- End Tab 1: Información Básica -->

                            <!-- Tab 2: Etapas de Validación -->
                            <div class="tab-pane fade"
                                 id="validation-stages"
                                 role="tabpanel">

                        <div class="mb-3">
                            <h6 class="mb-0">Etapas de validación</h6>
                            <small class="text-muted d-block mb-3">Configura el flujo de validación con orden y condiciones</small>
                        </div>

                            <div id="validationStagesContainer" class="mb-3">
                                @if($validatorGroups->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No hay grupos validadores configurados.
                                        <a href="{{ route('manager.settings.documents.groups') }}" class="alert-link">Crear grupos primero</a>
                                    </div>
                                @else
                                    @php
                                        // Get existing stages with conditions
                                        $existingStages = $documentType->getValidationStagesWithConditions();
                                        $stagesMap = collect($existingStages)->keyBy('key');
                                    @endphp

                                    <div id="stagesList">
                                        @foreach($validatorGroups as $index => $group)
                                            @php
                                                $existingStage = $stagesMap->get($group->key);
                                                $isEnabled = $existingStage !== null;
                                                $order = $existingStage['order'] ?? ($index + 1);
                                                $conditions = $existingStage['conditions'] ?? [];
                                                $selectedConditions = array_keys(array_filter($conditions));
                                            @endphp

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
                                                    <!-- Orden -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-sort-numeric-up me-1 text-muted"></i>
                                                            Orden
                                                        </label>
                                                        <input type="number"
                                                               class="form-control stage-order"
                                                               name="stage_order_{{ $group->key }}"
                                                               value="{{ $order }}"
                                                               min="1"
                                                               placeholder="1">
                                                        <small class="text-muted">Menor número = mayor prioridad</small>
                                                    </div>

                                                    <!-- Estado de activación -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-toggle-on me-1 text-muted"></i>
                                                            Estado
                                                        </label>
                                                        <select class="form-select stage-enabled-select" data-key="{{ $group->key }}">
                                                            <option value="0" {{ !$isEnabled ? 'selected' : '' }}>Deshabilitada</option>
                                                            <option value="1" {{ $isEnabled ? 'selected' : '' }}>Habilitada</option>
                                                        </select>
                                                        <small class="text-muted">Activar/desactivar esta etapa</small>
                                                    </div>

                                                    <!-- Condiciones -->
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-filter me-1" style="color: #90bb13;"></i>
                                                            Condiciones de ejecución
                                                        </label>

                                                        @if($validationConditions->isEmpty())
                                                            <div class="alert alert-warning bg-warning-subtle border-warning mb-0">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                No hay condiciones configuradas.
                                                                <a href="{{ route('manager.settings.documents.conditions') }}" class="alert-link">Crear condiciones</a>
                                                            </div>
                                                        @else
                                                            <select multiple
                                                                    class="form-select select2-conditions stage-conditions-select"
                                                                    id="conditions_{{ $group->key }}"
                                                                    name="stage_conditions_{{ $group->key }}[]"
                                                                    data-key="{{ $group->key }}"
                                                                    data-placeholder="Seleccionar condiciones para ejecutar esta etapa...">
                                                                @foreach($validationConditions as $validationCondition)
                                                                    <option value="{{ $validationCondition->key }}"
                                                                            data-description="{{ $validationCondition->description ?? '' }}"
                                                                            data-sale-types="{{ is_array($validationCondition->sale_types) && !empty($validationCondition->sale_types) ? implode(', ', $validationCondition->sale_types) : '' }}"
                                                                            {{ in_array($validationCondition->key, $selectedConditions) ? 'selected' : '' }}>
                                                                        {{ $validationCondition->name }} ({{ $validationCondition->key }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-muted d-block mt-2">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Sin condiciones = la etapa siempre se ejecuta. Con condiciones = solo se ejecuta si se cumplen.
                                                            </small>
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
                            <!-- End Tab 2: Etapas de Validación -->

                            <!-- Tab 3: Requisitos de Documentos -->
                            <div class="tab-pane fade"
                                 id="requirements"
                                 role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="mb-0">
                                    Requisitos de documentos
                                </h6>
                                <small class="text-muted d-block">Archivos que el usuario debe subir para este tipo</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="addRequirement">
                                <i class="fas fa-plus me-1"></i>Agregar requisito
                            </button>
                        </div>

                            <div id="requirementsContainer">
                                @forelse($documentType->requirements as $reqIndex => $requirement)
                                    <div class="border rounded p-3 bg-light mb-3 requirement-item" data-index="{{ $reqIndex }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-primary">Requisito #{{ $reqIndex + 1 }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-requirement">
                                                <i class="fas fa-trash-alt me-1"></i>Eliminar
                                            </button>
                                        </div>
                                            <input type="hidden" name="requirements[{{ $reqIndex }}][id]" value="{{ $requirement->id }}">

                                            <!-- Basic Fields -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-lg-4">
                                                    <label class="form-label">
                                                        <i class="fas fa-key text-muted me-1"></i>Clave <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" name="requirements[{{ $reqIndex }}][key]"
                                                           value="{{ old("requirements.{$reqIndex}.key", $requirement->key) }}"
                                                           placeholder="ej: dni_frontal" required>
                                                </div>
                                                <div class="col-lg-4">
                                                    <label class="form-label">
                                                        <i class="fas fa-weight-hanging text-muted me-1"></i>Tamaño máx (KB)
                                                    </label>
                                                    <input type="number" class="form-control" name="requirements[{{ $reqIndex }}][max_file_size]"
                                                           value="{{ old("requirements.{$reqIndex}.max_file_size", $requirement->max_file_size) }}"
                                                           min="1" placeholder="5120">
                                                </div>
                                                <div class="col-lg-4">
                                                    <label class="form-label">
                                                        <i class="fas fa-file-alt text-muted me-1"></i>Extensiones permitidas
                                                    </label>
                                                    @php
                                                        $oldExtensions = old("requirements.{$reqIndex}.allowed_extensions");
                                                        $extensionsValue = '';

                                                        if ($oldExtensions !== null) {
                                                            // old() returned something (could be array or string)
                                                            $extensionsValue = is_array($oldExtensions) ? implode(',', $oldExtensions) : $oldExtensions;
                                                        } elseif (is_array($requirement->allowed_extensions) && !empty($requirement->allowed_extensions)) {
                                                            // Use model value
                                                            $extensionsValue = implode(',', $requirement->allowed_extensions);
                                                        }
                                                    @endphp
                                                    <input type="text" class="form-control extensions-input"
                                                           name="requirements[{{ $reqIndex }}][allowed_extensions][]"
                                                           value="{{ $extensionsValue }}"
                                                           placeholder="pdf,jpg,jpeg,png">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="requirements[{{ $reqIndex }}][is_required]" value="1"
                                                               {{ old("requirements.{$reqIndex}.is_required", $requirement->is_required) ? 'checked' : '' }}
                                                               id="is_required_{{ $reqIndex }}">
                                                        <label class="form-check-label" for="is_required_{{ $reqIndex }}">
                                                            <i class="fas fa-exclamation-circle text-danger me-1"></i>Obligatorio
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="requirements[{{ $reqIndex }}][accepts_multiple]" value="1"
                                                               {{ old("requirements.{$reqIndex}.accepts_multiple", $requirement->accepts_multiple) ? 'checked' : '' }}
                                                               id="accepts_multiple_{{ $reqIndex }}">
                                                        <label class="form-check-label" for="accepts_multiple_{{ $reqIndex }}">
                                                            <i class="fas fa-copy text-info me-1"></i>Múltiples archivos
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Language Translations -->
                                            <div class="border-top pt-3 mt-3">
                                                <h6 class="mb-3">
                                                    <i class="fas fa-language text-primary me-2"></i>Traducciones
                                                </h6>
                                                <ul class="nav nav-pills mb-3" id="req{{ $reqIndex }}Tabs" role="tablist">
                                                    @foreach($langs as $langIndex => $lang)
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link {{ $langIndex === 0 ? 'active' : '' }}"
                                                                    id="req{{ $reqIndex }}-lang{{ $lang->id }}-tab"
                                                                    data-bs-toggle="tab"
                                                                    data-bs-target="#req{{ $reqIndex }}-lang{{ $lang->id }}"
                                                                    type="button" role="tab">
                                                                {{ $lang->title }}
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="tab-content">
                                                    @foreach($langs as $langIndex => $lang)
                                                        @php
                                                            $translation = $requirement->translate($lang->id);
                                                        @endphp
                                                        <div class="tab-pane fade {{ $langIndex === 0 ? 'show active' : '' }}"
                                                             id="req{{ $reqIndex }}-lang{{ $lang->id }}"
                                                             role="tabpanel">
                                                            <input type="hidden" name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][lang_id]" value="{{ $lang->id }}">
                                                            <div class="row">
                                                                <div class="col-lg-6 mb-2">
                                                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control"
                                                                           name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][name]"
                                                                           value="{{ old("requirements.{$reqIndex}.translations.{$langIndex}.name", $translation?->name) }}" required>
                                                                </div>
                                                                <div class="col-lg-6 mb-2">
                                                                    <label class="form-label">Texto de ayuda</label>
                                                                    <input type="text" class="form-control"
                                                                           name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][help_text]"
                                                                           value="{{ old("requirements.{$reqIndex}.translations.{$langIndex}.help_text", $translation?->help_text) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5" id="noRequirementsMessage">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-1">No hay requisitos de documentos configurados</p>
                                        <small class="text-muted">Haz clic en <strong>"Agregar requisito"</strong> para comenzar</small>
                                    </div>
                                @endforelse
                            </div>

                            </div>
                            <!-- End Tab 3: Requisitos de Documentos -->

                        </div>
                        <!-- End Tab Content -->

                        <!-- Footer -->
                        <div class="border-top pt-3 mt-4">
                            <button type="submit" class="btn btn-info px-4">
                                Guardar cambios
                            </button>
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-light px-4">
                                Cancelar
                            </a>
                            <span class="text-muted ms-3 small">Última actualización: {{ $documentType->updated_at->format('d/m/Y H:i') }}</span>
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
    let requirementIndex = {{ $documentType->requirements->count() }};

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
            <div class="card mb-3 requirement-item" data-index="${index}">
                <div class="card-body">
                    <!-- Basic Fields -->
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <label class="form-label">Clave <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="requirements[${index}][key]" placeholder="doc_1" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Tamaño máximo (KB)</label>
                            <input type="number" class="form-control" name="requirements[${index}][max_file_size]" value="10240" min="1">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Extensiones</label>
                            <input type="text" class="form-control extensions-input" name="requirements[${index}][allowed_extensions][]" value="pdf,jpg,jpeg,png">
                        </div>
                        <div class="col-lg-2 text-end align-self-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-requirement w-100">Eliminar</button>
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

    // Remove existing requirements
    $('.remove-requirement').on('click', function() {
        $(this).closest('.requirement-item').remove();
        if ($('.requirement-item').length === 0) {
            $('#noRequirementsMessage').show();
        }
    });

    // Initialize Select2 for conditions
    $('.select2-conditions').select2({
        placeholder: 'Seleccionar condiciones...',
        allowClear: true,
        width: '100%',
        templateResult: formatConditionOption,
        templateSelection: formatConditionSelection
    });

    function formatConditionOption(option) {
        if (!option.id) return option.text;

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
                html += '<span class="badge bg-info-subtle text-info me-1 small">' + type + '</span>';
            });
            html += '</div>';
        }

        html += '</div>';
        return $(html);
    }

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
        });
    });
});
</script>
@endpush
