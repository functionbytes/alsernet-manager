@extends('layouts.managers')

@section('content')

    <div class="card w-100">

        <form id="formValidationCondition" method="POST" action="{{ route('manager.settings.documents.conditions.store') }}">

            {{ csrf_field() }}

            <div class="card-body">
                <div class="d-flex no-block align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="fas fa-filter me-2" style="color: #90bb13;"></i>
                            Crear condición de validación
                        </h4>
                        <p class="card-subtitle text-muted mb-0">
                            Define condiciones personalizadas para controlar cuándo se ejecutan las etapas de validación
                        </p>
                    </div>
                </div>

                <!-- Tipo de Condición -->
                <div class="card bg-primary-subtle border-primary mb-4">
                    <div class="card-body">
                        <label class="form-label fw-semibold mb-3">
                            <i class="fas fa-shapes me-2"></i>Tipo de Condición
                            <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            @foreach(\App\Models\Document\DocumentValidationCondition::AVAILABLE_TYPES as $typeKey => $typeLabel)
                                <div class="col-md-4">
                                    <div class="form-check card mb-0 condition-type-card {{ $loop->first ? 'active' : '' }}"
                                         data-type="{{ $typeKey }}">
                                        <div class="card-body">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="condition_type"
                                                   id="type_{{ $typeKey }}"
                                                   value="{{ $typeKey }}"
                                                   {{ old('condition_type', $loop->first ? $typeKey : '') == $typeKey ? 'checked' : '' }}>
                                            <label class="form-check-label w-100 ms-2" for="type_{{ $typeKey }}">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-{{ $typeKey === 'sale_type_match' ? 'tags' : ($typeKey === 'model_field' ? 'database' : 'code') }} fa-2x me-3 text-primary"></i>
                                                    <div>
                                                        <strong class="d-block">{{ $typeLabel }}</strong>
                                                        <small class="text-muted">
                                                            @if($typeKey === 'sale_type_match')
                                                                Mapea tipos de venta del producto
                                                            @elseif($typeKey === 'model_field')
                                                                Valida campos del modelo
                                                            @else
                                                                Expresiones y lógica personalizada
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('condition_type')
                            <div class="text-danger mt-2">
                                <i class="fa fa-circle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Información Básica -->
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-key me-1 text-muted"></i>Clave
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('key') is-invalid @enderror"
                                   name="key"
                                   value="{{ old('key') }}"
                                   required
                                   placeholder="is_weapon, is_national, is_priority"
                                   pattern="[a-z0-9_\-]+"
                                   title="Solo letras minúsculas, números, guiones y guiones bajos">
                            @error('key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Identificador único (snake_case)</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1 text-muted"></i>Nombre
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="Es un arma, Cliente nacional, Pedido prioritario">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Nombre descriptivo para la interfaz</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1 text-muted"></i>Descripción
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      name="description"
                                      rows="2"
                                      placeholder="Describe cuándo se debe aplicar esta condición...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Explicación detallada (opcional)</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Campos Dinámicos según Tipo -->

                <!-- Sale Type Match Fields -->
                <div class="condition-fields" id="fields_sale_type_match" style="display: none;">
                    <div class="card border-info">
                        <div class="card-header bg-info-subtle">
                            <h6 class="mb-0">
                                <i class="fas fa-tags me-2"></i>Configuración de Sale Types
                            </h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-semibold">
                                Sale Types
                                <span class="text-danger">*</span>
                            </label>
                            <select multiple
                                    class="form-select select2-tags"
                                    id="sale_types"
                                    name="sale_types[]"
                                    data-placeholder="Seleccionar o agregar sale types...">
                                @foreach($validSaleTypes as $saleType)
                                    <option value="{{ $saleType }}" {{ in_array($saleType, old('sale_types', [])) ? 'selected' : '' }}>
                                        {{ $saleType }}
                                    </option>
                                @endforeach
                                @if(old('sale_types'))
                                    @foreach(old('sale_types') as $oldSaleType)
                                        @if(!in_array($oldSaleType, $validSaleTypes))
                                            <option value="{{ $oldSaleType }}" selected>{{ $oldSaleType }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="form-text text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Tipos de venta que deben coincidir con <code>blockade_type</code> en <code>document_product_blockades</code>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Model Field Fields -->
                <div class="condition-fields" id="fields_model_field" style="display: none;">
                    <div class="card border-success">
                        <div class="card-header bg-success-subtle">
                            <h6 class="mb-0">
                                <i class="fas fa-database me-2"></i>Configuración de Campo del Modelo
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Campo del Modelo
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               name="model_field"
                                               value="{{ old('model_field') }}"
                                               placeholder="customer.country_id, order.total">
                                        <small class="form-text text-muted">
                                            Usa notación de punto para relaciones: <code>customer.country_id</code>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Valor(es) Esperado(s)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               name="expected_value_text"
                                               value="{{ old('expected_value_text') }}"
                                               placeholder="1, 2, 3 (separados por coma)">
                                        <small class="form-text text-muted">
                                            Valores separados por coma para comparación
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Ejemplo:</strong> Campo: <code>customer.country_id</code>, Valor: <code>1</code> → valida si el país del cliente es España (ID 1)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Expression Fields -->
                <div class="condition-fields" id="fields_custom_expression" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning-subtle">
                            <h6 class="mb-0">
                                <i class="fas fa-code me-2"></i>Configuración de Expresión Personalizada
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Expresión de Validación
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control font-monospace"
                                          name="validation_expression"
                                          rows="4"
                                          placeholder="order_total > 1000&#10;customer.vip == true&#10;product_count >= 5">{{ old('validation_expression') }}</textarea>
                                <small class="form-text text-muted">
                                    Expresión que retorna verdadero/falso
                                </small>
                            </div>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Avanzado:</strong> Esta expresión se evaluará en el contexto del documento. Asegúrate de que la lógica sea correcta.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado y Orden -->
                <div class="row mt-3">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-toggle-on me-1 text-muted"></i>Estado
                            </label>
                            <select class="form-select @error('is_active') is-invalid @enderror"
                                    name="is_active">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                                    <i class="fas fa-check-circle"></i> Activa
                                </option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                                    <i class="fas fa-times-circle"></i> Inactiva
                                </option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Solo las condiciones activas se evaluarán</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-sort-numeric-up me-1 text-muted"></i>Orden de visualización
                            </label>
                            <input type="number"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   name="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   placeholder="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Orden en listados (menor primero)</small>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer bg-light">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 flex-grow-1">
                        <i class="fas fa-save me-2"></i>Guardar Condición
                    </button>
                    <a href="{{ route('manager.settings.documents.conditions') }}" class="btn btn-secondary px-4">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
<style>
.condition-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.condition-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.condition-type-card.active {
    border-color: #90bb13 !important;
    background-color: rgba(144, 187, 19, 0.05);
}

.condition-type-card label {
    cursor: pointer;
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2 for sale types with tags
    $('.select2-tags').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: 'Seleccionar o agregar sale types...',
        allowClear: true
    });

    // Handle condition type selection
    function showConditionFields(type) {
        // Hide all condition fields
        $('.condition-fields').hide();

        // Show selected type fields
        $('#fields_' + type).show();

        // Update card styling
        $('.condition-type-card').removeClass('active');
        $('.condition-type-card[data-type="' + type + '"]').addClass('active');

        // Enable/disable required fields based on type
        updateRequiredFields(type);
    }

    function updateRequiredFields(type) {
        // Remove all required from dynamic fields first
        $('#sale_types, input[name="model_field"], input[name="expected_value_text"], textarea[name="validation_expression"]')
            .removeAttr('required');

        // Add required to relevant fields
        if (type === 'sale_type_match') {
            $('#sale_types').attr('required', 'required');
        } else if (type === 'model_field') {
            $('input[name="model_field"], input[name="expected_value_text"]').attr('required', 'required');
        } else if (type === 'custom_expression') {
            $('textarea[name="validation_expression"]').attr('required', 'required');
        }
    }

    // Handle radio button change
    $('input[name="condition_type"]').on('change', function() {
        showConditionFields($(this).val());
    });

    // Handle card click
    $('.condition-type-card').on('click', function() {
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true).trigger('change');
    });

    // Initialize on page load
    const initialType = $('input[name="condition_type"]:checked').val() || 'sale_type_match';
    showConditionFields(initialType);

    // Handle form submission - convert expected_value_text to array
    $('#formValidationCondition').on('submit', function(e) {
        const type = $('input[name="condition_type"]:checked').val();

        if (type === 'model_field') {
            const expectedValueText = $('input[name="expected_value_text"]').val();
            if (expectedValueText) {
                // Remove the text input from submission
                $('input[name="expected_value_text"]').attr('name', '_expected_value_text');

                // Create hidden inputs for each value
                const values = expectedValueText.split(',').map(v => v.trim()).filter(v => v);
                values.forEach(value => {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'expected_value[]',
                        value: value
                    }).appendTo('#formValidationCondition');
                });
            }
        }
    });
});
</script>
@endpush
