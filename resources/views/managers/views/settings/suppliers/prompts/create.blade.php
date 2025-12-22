@extends('layouts.managers')

@section('title', 'Crear Prompt')

@section('content')

    @include('managers.includes.card', ['title' => 'Crear Prompt'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <!-- Breadcrumb -->
        <div class="card card-body mb-3 bg-light-secondary">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('manager.settings.suppliers.prompts.index') }}">
                            <i class="fas fa-arrow-left me-2"></i> Prompts
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Crear</li>
                </ol>
            </nav>
        </div>

        <form method="POST" action="{{ route('manager.settings.suppliers.prompts.store') }}">
            @csrf

            <div class="row g-3">

                <!-- Left Column: Configuration -->
                <div class="col-lg-4">
                    <div class="card card-body">
                        <h5 class="mb-3">Configuración</h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Etiqueta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('label') is-invalid @enderror" name="label"
                                   value="{{ old('label') }}" required>
                            <small class="text-muted">Nombre descriptivo del prompt</small>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alcance <span class="text-danger">*</span></label>
                            <select class="form-select @error('scope') is-invalid @enderror" name="scope" id="promptScope" required>
                                <option value="">Seleccionar...</option>
                                <option value="global" {{ old('scope') === 'global' ? 'selected' : '' }}>Global (Todos)</option>
                                <option value="supplier" {{ old('scope') === 'supplier' ? 'selected' : '' }}>Por Proveedor</option>
                                <option value="category" {{ old('scope') === 'category' ? 'selected' : '' }}>Por Categoría</option>
                                <option value="source" {{ old('scope') === 'source' ? 'selected' : '' }}>Por Fuente</option>
                            </select>
                            @error('scope')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="supplierContainer" style="display: none;">
                            <label class="form-label fw-semibold">Proveedor</label>
                            <select class="form-select" name="supplier_id">
                                <option value="">Seleccionar...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Idioma de Salida <span class="text-danger">*</span></label>
                            <select class="form-select @error('output_language') is-invalid @enderror" name="output_language" required>
                                <option value="es" {{ old('output_language', 'es') === 'es' ? 'selected' : '' }}>Español</option>
                                <option value="en" {{ old('output_language') === 'en' ? 'selected' : '' }}>Inglés</option>
                                <option value="fr" {{ old('output_language') === 'fr' ? 'selected' : '' }}>Francés</option>
                                <option value="de" {{ old('output_language') === 'de' ? 'selected' : '' }}>Alemán</option>
                            </select>
                            @error('output_language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tono <span class="text-danger">*</span></label>
                            <select class="form-select @error('tone') is-invalid @enderror" name="tone" required>
                                <option value="professional" {{ old('tone', 'professional') === 'professional' ? 'selected' : '' }}>Profesional</option>
                                <option value="casual" {{ old('tone') === 'casual' ? 'selected' : '' }}>Casual</option>
                                <option value="technical" {{ old('tone') === 'technical' ? 'selected' : '' }}>Técnico</option>
                                <option value="friendly" {{ old('tone') === 'friendly' ? 'selected' : '' }}>Amigable</option>
                                <option value="formal" {{ old('tone') === 'formal' ? 'selected' : '' }}>Formal</option>
                            </select>
                            @error('tone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <input type="number" class="form-control @error('priority') is-invalid @enderror" name="priority"
                                   value="{{ old('priority', 0) }}" min="0" max="100">
                            <small class="text-muted">Mayor número = mayor prioridad</small>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Enfoque SEO</label>
                            <select class="form-select" name="seo_focus">
                                <option value="0" {{ old('seo_focus', '0') === '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('seo_focus') === '1' ? 'selected' : '' }}>Sí</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Estado</label>
                            <select class="form-select" name="is_active">
                                <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Template Editor -->
                <div class="col-lg-8">
                    <div class="card card-body">
                        <h5 class="mb-3">Template del Prompt</h5>

                        <div class="mb-3">
                            <textarea class="form-control font-monospace @error('prompt_template') is-invalid @enderror" 
                                      name="prompt_template" id="promptTemplate"
                                      rows="20" required>{{ old('prompt_template') }}</textarea>
                            <small class="text-muted">
                                Escribe tu prompt aquí. Usa @{{ variable }} para reemplazar valores dinámicamente.
                            </small>
                            @error('prompt_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Guardar Prompt
                                </button>
                                <a href="{{ route('manager.settings.suppliers.prompts.index') }}" class="btn btn-light">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Scope change handler
    $('#promptScope').on('change', function() {
        const scope = $(this).val();

        $('#supplierContainer').hide();

        if (scope === 'supplier') {
            $('#supplierContainer').show();
        }
    });

    // Trigger on page load
    $('#promptScope').trigger('change');
});
</script>
@endpush
