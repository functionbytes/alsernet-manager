@extends('layouts.theme')

@section('content')

    <div class="card w-100">

        <form id="formTemplate" method="POST" action="{{ route('settings.suppliers.templates.update', $template->uid) }}">

            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Editar plantilla de prompt</h5>
                        <p class="card-subtitle mb-0 mt-2">
                            Modifica los parámetros de la plantilla <strong>{{ $template->label }}</strong>.
                        </p>
                    </div>
                    <a href="{{ route('settings.suppliers.templates.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>

                <div class="row">

                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            Información básica
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Nombre de la plantilla
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label', $template->label) }}" required placeholder="Ej: Template: Descripción Producto E-commerce">
                            <small class="form-text text-muted">Nombre descriptivo para identificar la plantilla</small>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Categoría de la plantilla</label>
                            <input type="text" name="template_category" class="form-control @error('template_category') is-invalid @enderror"
                                   value="{{ old('template_category', $template->template_category) }}" placeholder="Ej: ecommerce, seo, marketing">
                            <small class="form-text text-muted">Opcional. Ayuda a organizar las plantillas</small>
                            @error('template_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Tipo de contenido
                                <span class="text-danger">*</span>
                            </label>
                            <select name="content_type" class="form-select @error('content_type') is-invalid @enderror" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="description" {{ old('content_type', $template->content_type) == 'description' ? 'selected' : '' }}>📝 Descripción completa</option>
                                <option value="short_description" {{ old('content_type', $template->content_type) == 'short_description' ? 'selected' : '' }}>📄 Descripción corta</option>
                                <option value="title" {{ old('content_type', $template->content_type) == 'title' ? 'selected' : '' }}>🏷️ Título del producto</option>
                                <option value="seo_title" {{ old('content_type', $template->content_type) == 'seo_title' ? 'selected' : '' }}>🔍 Título SEO</option>
                                <option value="seo_description" {{ old('content_type', $template->content_type) == 'seo_description' ? 'selected' : '' }}>📊 Meta Description SEO</option>
                                <option value="seo_keywords" {{ old('content_type', $template->content_type) == 'seo_keywords' ? 'selected' : '' }}>🔑 Keywords SEO</option>
                                <option value="features" {{ old('content_type', $template->content_type) == 'features' ? 'selected' : '' }}>⭐ Características</option>
                                <option value="specifications" {{ old('content_type', $template->content_type) == 'specifications' ? 'selected' : '' }}>📋 Especificaciones técnicas</option>
                                <option value="benefits" {{ old('content_type', $template->content_type) == 'benefits' ? 'selected' : '' }}>✅ Beneficios</option>
                                <option value="metadata" {{ old('content_type', $template->content_type) == 'metadata' ? 'selected' : '' }}>📎 Metadatos</option>
                            </select>
                            <small class="form-text text-muted">Tipo de contenido que generará esta plantilla</small>
                            @error('content_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Idioma de salida
                                <span class="text-danger">*</span>
                            </label>
                            <select name="output_language" class="form-select @error('output_language') is-invalid @enderror" required>
                                <option value="es" {{ old('output_language', $template->output_language) == 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                                <option value="en" {{ old('output_language', $template->output_language) == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                <option value="fr" {{ old('output_language', $template->output_language) == 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                                <option value="de" {{ old('output_language', $template->output_language) == 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                            </select>
                            @error('output_language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Plantilla del prompt
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="prompt_template" class="form-control @error('prompt_template') is-invalid @enderror"
                                      rows="12" required
                                      placeholder="Escribe aquí el prompt. Puedes usar variables como @{{ product_name }}, @{{ category }}, @{{ price }}, etc.">{{ old('prompt_template', $template->prompt_template) }}</textarea>
                            <small class="form-text text-muted">Variables disponibles: @{{ product_name }}, @{{ category }}, @{{ supplier_name }}, @{{ price }}, @{{ description }}, @{{ features }}, @{{ brand }}, @{{ sku }}, @{{ reference }}, @{{ ean }}</small>
                            @error('prompt_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuration Section -->
                    <div class="col-12">
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            Configuración
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Tono
                                <span class="text-danger">*</span>
                            </label>
                            <select name="tone" class="form-select @error('tone') is-invalid @enderror" required>
                                <option value="professional" {{ old('tone', $template->tone) == 'professional' ? 'selected' : '' }}>Profesional</option>
                                <option value="casual" {{ old('tone', $template->tone) == 'casual' ? 'selected' : '' }}>Casual</option>
                                <option value="enthusiastic" {{ old('tone', $template->tone) == 'enthusiastic' ? 'selected' : '' }}>Entusiasta</option>
                                <option value="formal" {{ old('tone', $template->tone) == 'formal' ? 'selected' : '' }}>Formal</option>
                                <option value="friendly" {{ old('tone', $template->tone) == 'friendly' ? 'selected' : '' }}>Amigable</option>
                                <option value="persuasive" {{ old('tone', $template->tone) == 'persuasive' ? 'selected' : '' }}>Persuasivo</option>
                                <option value="technical" {{ old('tone', $template->tone) == 'technical' ? 'selected' : '' }}>Técnico</option>
                                <option value="elegant" {{ old('tone', $template->tone) == 'elegant' ? 'selected' : '' }}>Elegante</option>
                            </select>
                            @error('tone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Prioridad</label>
                            <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror"
                                   value="{{ old('priority', $template->priority) }}" min="0" max="100">
                            <small class="form-text text-muted">0-100. Mayor prioridad = se selecciona primero</small>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Enfoque SEO</label>
                            <select name="seo_focus" class="form-select @error('seo_focus') is-invalid @enderror">
                                <option value="0" {{ old('seo_focus', $template->seo_focus) == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('seo_focus', $template->seo_focus) == '1' ? 'selected' : '' }}>Sí</option>
                            </select>
                            <small class="form-text text-muted">El contenido estará optimizado para SEO</small>
                            @error('seo_focus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Notas</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Notas internas sobre esta plantilla (opcional)">{{ old('notes', $template->notes) }}</textarea>
                            <small class="form-text text-muted">Notas de uso interno</small>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100 mb-1">
                        Actualizar
                    </button>
                    <a href="{{ route('settings.suppliers.templates.index') }}" class="btn btn-secondary w-100">
                        Cancelar
                    </a>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
