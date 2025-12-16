@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Editar Tipo de Documento'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('manager.settings.documents.types.update', $documentType->slug) }}" method="POST" id="editDocumentTypeForm">
                    @csrf

                    <div class="card-body">
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h5 class="mb-1 fw-bold">Editar: {{ $documentType->translate()?->label ?? $documentType->slug }}</h5>
                                <p class="text-muted small mb-0">
                                    Actualice la información en todos los idiomas disponibles.
                                </p>
                            </div>
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-cog me-2"></i>Información Básica
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">
                                        Slug <span class="text-danger">*</span>
                                        <small class="text-muted">(Identificador único)</small>
                                    </label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                           id="slug" name="slug" value="{{ old('slug', $documentType->slug) }}"
                                           pattern="[a-z0-9_-]+" required>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">
                                        Icono <small class="text-muted">(Font Awesome)</small>
                                    </label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon', $documentType->icon) }}">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Vista previa: <i id="iconPreview" class="{{ old('icon', $documentType->icon) }} ms-2"></i></small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                           id="color" name="color" value="{{ old('color', $documentType->color) }}">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Orden de Visualización</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $documentType->sort_order) }}"
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sla_multiplier" class="form-label">Multiplicador SLA</label>
                                    <input type="number" class="form-control @error('sla_multiplier') is-invalid @enderror"
                                           id="sla_multiplier" name="sla_multiplier" value="{{ old('sla_multiplier', $documentType->sla_multiplier) }}"
                                           min="0" max="100" step="0.1">
                                    @error('sla_multiplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Estado</label>
                                    <select class="form-select @error('is_active') is-invalid @enderror"
                                            id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', $documentType->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('is_active', $documentType->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Translations -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-language me-2"></i>Traducciones
                                </h6>
                            </div>

                            <div class="col-12">
                                <ul class="nav nav-tabs mb-3" id="languageTabs" role="tablist">
                                    @foreach($langs as $index => $lang)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                    id="lang-{{ $lang->id }}-tab"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#lang-{{ $lang->id }}"
                                                    type="button" role="tab">
                                                {{ $lang->title }}
                                                @php
                                                    $translation = $documentType->getTranslationsList()->where('lang_id', $lang->id)->first();
                                                @endphp
                                                @if($translation)
                                                    <i class="fas fa-check-circle text-success ms-1"></i>
                                                @else
                                                    <i class="fas fa-exclamation-circle text-warning ms-1"></i>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content" id="languageTabsContent">
                                    @foreach($langs as $index => $lang)
                                        @php
                                            $translation = $documentType->getTranslationsList()->where('lang_id', $lang->id)->first();
                                        @endphp
                                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                             id="lang-{{ $lang->id }}"
                                             role="tabpanel">

                                            <input type="hidden" name="translations[{{ $index }}][lang_id]" value="{{ $lang->id }}">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="label_{{ $lang->id }}" class="form-label">
                                                            Etiqueta <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text"
                                                               class="form-control @error("translations.{$index}.label") is-invalid @enderror"
                                                               id="label_{{ $lang->id }}"
                                                               name="translations[{{ $index }}][label]"
                                                               value="{{ old("translations.{$index}.label", $translation?->label) }}"
                                                               required>
                                                        @error("translations.{$index}.label")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="description_{{ $lang->id }}" class="form-label">
                                                            Descripción
                                                        </label>
                                                        <textarea class="form-control @error("translations.{$index}.description") is-invalid @enderror"
                                                                  id="description_{{ $lang->id }}"
                                                                  name="translations[{{ $index }}][description]"
                                                                  rows="2">{{ old("translations.{$index}.description", $translation?->description) }}</textarea>
                                                        @error("translations.{$index}.description")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="instructions_{{ $lang->id }}" class="form-label">
                                                            Instrucciones
                                                        </label>
                                                        <textarea class="form-control @error("translations.{$index}.instructions") is-invalid @enderror"
                                                                  id="instructions_{{ $lang->id }}"
                                                                  name="translations[{{ $index }}][instructions]"
                                                                  rows="4">{{ old("translations.{$index}.instructions", $translation?->instructions) }}</textarea>
                                                        @error("translations.{$index}.instructions")
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <h6 class="fw-bold mb-0">
                                        <i class="fas fa-list-check me-2"></i>Requisitos de Documentos
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="addRequirement">
                                        <i class="fas fa-plus me-1"></i> Agregar Requisito
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <div id="requirementsContainer">
                                    @foreach($documentType->requirements as $reqIndex => $requirement)
                                        <div class="card mb-3 requirement-item" data-index="{{ $reqIndex }}">
                                            <div class="card-header bg-light">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-0 fw-bold">Requisito #{{ $reqIndex + 1 }}</h6>
                                                    <button type="button" class="btn btn-sm btn-danger remove-requirement">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <input type="hidden" name="requirements[{{ $reqIndex }}][id]" value="{{ $requirement->id }}">

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Clave <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="requirements[{{ $reqIndex }}][key]"
                                                                   value="{{ old("requirements.{$reqIndex}.key", $requirement->key) }}"
                                                                   required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Tamaño Máximo (KB)</label>
                                                            <input type="number" class="form-control" name="requirements[{{ $reqIndex }}][max_file_size]"
                                                                   value="{{ old("requirements.{$reqIndex}.max_file_size", $requirement->max_file_size) }}"
                                                                   min="1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Extensiones Permitidas</label>
                                                            <input type="text" class="form-control extensions-input"
                                                                   name="requirements[{{ $reqIndex }}][allowed_extensions][]"
                                                                   value="{{ old("requirements.{$reqIndex}.allowed_extensions", is_array($requirement->allowed_extensions) ? implode(',', $requirement->allowed_extensions) : '') }}">
                                                            <small class="text-muted">Separadas por comas</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="requirements[{{ $reqIndex }}][is_required]"
                                                                   value="1" {{ old("requirements.{$reqIndex}.is_required", $requirement->is_required) ? 'checked' : '' }}
                                                                   id="is_required_{{ $reqIndex }}">
                                                            <label class="form-check-label" for="is_required_{{ $reqIndex }}">
                                                                Es obligatorio
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="requirements[{{ $reqIndex }}][accepts_multiple]"
                                                                   value="1" {{ old("requirements.{$reqIndex}.accepts_multiple", $requirement->accepts_multiple) ? 'checked' : '' }}
                                                                   id="accepts_multiple_{{ $reqIndex }}">
                                                            <label class="form-check-label" for="accepts_multiple_{{ $reqIndex }}">
                                                                Acepta múltiples archivos
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="border-top pt-3 mt-3">
                                                    <h6 class="fw-bold mb-3">Traducciones del Requisito</h6>
                                                    <ul class="nav nav-pills mb-3" role="tablist">
                                                        @foreach($langs as $langIndex => $lang)
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link {{ $langIndex === 0 ? 'active' : '' }}"
                                                                        id="req-{{ $reqIndex }}-lang-{{ $lang->id }}-tab"
                                                                        data-bs-toggle="tab"
                                                                        data-bs-target="#req-{{ $reqIndex }}-lang-{{ $lang->id }}"
                                                                        type="button" role="tab">
                                                                    {{ $lang->title }}
                                                                </button>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    <div class="tab-content">
                                                        @foreach($langs as $langIndex => $lang)
                                                            @php
                                                                $reqTranslation = $requirement->getTranslationsList()->where('lang_id', $lang->id)->first();
                                                            @endphp
                                                            <div class="tab-pane fade {{ $langIndex === 0 ? 'show active' : '' }}"
                                                                 id="req-{{ $reqIndex }}-lang-{{ $lang->id }}"
                                                                 role="tabpanel">
                                                                <input type="hidden" name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][lang_id]" value="{{ $lang->id }}">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control"
                                                                                   name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][name]"
                                                                                   value="{{ old("requirements.{$reqIndex}.translations.{$langIndex}.name", $reqTranslation?->name) }}"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Texto de Ayuda</label>
                                                                            <textarea class="form-control"
                                                                                      name="requirements[{{ $reqIndex }}][translations][{{ $langIndex }}][help_text]"
                                                                                      rows="2">{{ old("requirements.{$reqIndex}.translations.{$langIndex}.help_text", $reqTranslation?->help_text) }}</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer border-top">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-light">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const langs = @json($langs);
    let requirementIndex = {{ $documentType->requirements->count() }};

    // Icon preview
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('iconPreview');

    iconInput.addEventListener('input', function() {
        iconPreview.className = this.value + ' ms-2';
    });

    // Add requirement
    const addRequirementBtn = document.getElementById('addRequirement');
    const requirementsContainer = document.getElementById('requirementsContainer');

    addRequirementBtn.addEventListener('click', function() {
        addRequirement();
    });

    function addRequirement() {
        const index = requirementIndex++;
        const requirementHtml = `
            <div class="card mb-3 requirement-item" data-index="${index}">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold">Requisito #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-requirement">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Clave <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="requirements[${index}][key]"
                                       placeholder="doc_1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tamaño Máximo (KB)</label>
                                <input type="number" class="form-control" name="requirements[${index}][max_file_size]"
                                       value="10240" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Extensiones Permitidas</label>
                                <input type="text" class="form-control extensions-input" name="requirements[${index}][allowed_extensions][]"
                                       value="pdf,jpg,jpeg,png" placeholder="pdf,jpg,png">
                                <small class="text-muted">Separadas por comas</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="requirements[${index}][is_required]"
                                       value="1" checked id="is_required_${index}">
                                <label class="form-check-label" for="is_required_${index}">
                                    Es obligatorio
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="requirements[${index}][accepts_multiple]"
                                       value="1" id="accepts_multiple_${index}">
                                <label class="form-check-label" for="accepts_multiple_${index}">
                                    Acepta múltiples archivos
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="fw-bold mb-3">Traducciones del Requisito</h6>
                        <ul class="nav nav-pills mb-3" role="tablist">
                            ${langs.map((lang, langIndex) => `
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link ${langIndex === 0 ? 'active' : ''}"
                                            id="req-${index}-lang-${lang.id}-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#req-${index}-lang-${lang.id}"
                                            type="button" role="tab">
                                        ${lang.title}
                                    </button>
                                </li>
                            `).join('')}
                        </ul>

                        <div class="tab-content">
                            ${langs.map((lang, langIndex) => `
                                <div class="tab-pane fade ${langIndex === 0 ? 'show active' : ''}"
                                     id="req-${index}-lang-${lang.id}"
                                     role="tabpanel">
                                    <input type="hidden" name="requirements[${index}][translations][${langIndex}][lang_id]" value="${lang.id}">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control"
                                                       name="requirements[${index}][translations][${langIndex}][name]"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Texto de Ayuda</label>
                                                <textarea class="form-control"
                                                          name="requirements[${index}][translations][${langIndex}][help_text]"
                                                          rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;

        requirementsContainer.insertAdjacentHTML('beforeend', requirementHtml);

        // Attach remove handler
        const newRequirement = requirementsContainer.lastElementChild;
        newRequirement.querySelector('.remove-requirement').addEventListener('click', function() {
            newRequirement.remove();
        });
    }

    // Attach remove handlers to existing requirements
    document.querySelectorAll('.remove-requirement').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.requirement-item').remove();
        });
    });

    // Handle allowed_extensions array conversion
    document.getElementById('editDocumentTypeForm').addEventListener('submit', function(e) {
        // Convert comma-separated extensions to array
        const extensionInputs = document.querySelectorAll('.extensions-input');
        extensionInputs.forEach(input => {
            const value = input.value;
            const match = input.name.match(/requirements\[(\d+)\]/);
            if (!match) return;

            const index = match[1];

            // Remove the original input
            input.remove();

            // Create hidden inputs for each extension
            if (value) {
                const extensions = value.split(',').map(ext => ext.trim()).filter(ext => ext);
                extensions.forEach(ext => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `requirements[${index}][allowed_extensions][]`;
                    hiddenInput.value = ext;
                    this.appendChild(hiddenInput);
                });
            }
        });
    });
});
</script>
@endpush
