@extends('layouts.theme')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form action="{{ route('settings.suppliers.prompts.update', $prompt->uid) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">Editar prompt: {{ $prompt->label }}</h5>
                                <p class="card-subtitle mb-0 mt-2">
                                    Actualice la configuración y template del prompt de IA
                                    <span class="badge bg-secondary ms-2">
                                        <i class="fas fa-code-branch me-1"></i>v{{ $prompt->version }}
                                    </span>
                                </p>
                            </div>
                            <a href="{{ route('settings.suppliers.prompts.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>

                        <!-- Warning Alert about version -->
                        <div class="alert bg-light border-start border-4 ">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-3 mt-1 fs-5"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Control de versiones automático</h6>
                                    <small class="mb-0">Si modificas el <strong>template del prompt</strong>, la versión se incrementará automáticamente. Esto permite rastrear cambios y revertir si es necesario. Los demás campos no afectan la versión.</small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <!-- Left Column: Configuration -->
                            <div class="col-lg-12 mb-3">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    Configuración básica
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Etiqueta <span class="text-danger">*</span>
                                        <i class="fas fa-circle-question ms-1 text-muted" data-bs-toggle="tooltip" title="Nombre identificativo que aparecerá en la lista de prompts"></i>
                                    </label>
                                    <input type="text" class="form-control @error('label') is-invalid @enderror"
                                           name="label" value="{{ old('label', $prompt->label) }}" required
                                           placeholder="ej: Descripción de productos para marketplace">
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-lightbulb text-warning me-1"></i>
                                        Elige un nombre descriptivo que identifique claramente el propósito del prompt
                                    </small>
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Alcance <span class="text-danger">*</span>
                                        <i class="fas fa-circle-question ms-1 text-muted" data-bs-toggle="tooltip" title="Define dónde se aplicará este prompt"></i>
                                    </label>
                                    <select class="form-select @error('scope') is-invalid @enderror"
                                            name="scope" id="promptScope" required>
                                        <option value="">Seleccionar alcance...</option>
                                        <option value="global" {{ old('scope', $prompt->scope) === 'global' ? 'selected' : '' }}>
                                            🌍 Global - Todos los productos y proveedores
                                        </option>
                                        <option value="supplier" {{ old('scope', $prompt->scope) === 'supplier' ? 'selected' : '' }}>
                                            🏢 Por Proveedor - Solo productos de un proveedor específico
                                        </option>
                                        <option value="category" {{ old('scope', $prompt->scope) === 'category' ? 'selected' : '' }}>
                                            📂 Por Categoría - Solo productos de ciertas categorías
                                        </option>
                                        <option value="supplier_category" {{ old('scope', $prompt->scope) === 'supplier_category' ? 'selected' : '' }}>
                                            🎯 Por Proveedor+Categoría - Combinación específica
                                        </option>
                                        <option value="source" {{ old('scope', $prompt->scope) === 'source' ? 'selected' : '' }}>
                                            🔗 Por Fuente - Solo productos de fuentes específicas
                                        </option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <strong>Global:</strong> se aplica a todos los productos automáticamente<br>
                                        <strong>Específico:</strong> solo se usa cuando coincide el criterio seleccionado
                                    </small>
                                    @error('scope')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="supplierContainer" style="display: none;">
                                    <label class="form-label fw-semibold">Proveedor específico</label>
                                    <select class="form-select" name="supplier_id" id="supplierSelect">
                                        <option value="">Seleccionar proveedor...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $prompt->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Este prompt solo se usará para productos de este proveedor</small>
                                </div>

                                <div class="mb-3" id="categoryContainer" style="display: none;">
                                    <label class="form-label fw-semibold">Categoría específica</label>
                                    <select class="form-select" name="category_id" id="categorySelect">
                                        <option value="">Seleccionar categoría...</option>
                                        @foreach(\App\Models\Category::orderBy('title')->get() as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $prompt->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Este prompt solo se usará para productos de esta categoría</small>
                                </div>

                                <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4">
                                    Parámetros de salida
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Tipo de contenido <span class="text-danger">*</span>
                                        <i class="fas fa-circle-question ms-1 text-muted" data-bs-toggle="tooltip" title="Qué tipo de contenido generará este prompt"></i>
                                    </label>
                                    <select class="form-select @error('content_type') is-invalid @enderror"
                                            name="content_type" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="description" {{ old('content_type', $prompt->content_type) === 'description' ? 'selected' : '' }}>
                                            📝 Descripción completa
                                        </option>
                                        <option value="short_description" {{ old('content_type', $prompt->content_type) === 'short_description' ? 'selected' : '' }}>
                                            📄 Descripción corta
                                        </option>
                                        <option value="title" {{ old('content_type', $prompt->content_type) === 'title' ? 'selected' : '' }}>
                                            🏷️ Título del producto
                                        </option>
                                        <option value="seo_title" {{ old('content_type', $prompt->content_type) === 'seo_title' ? 'selected' : '' }}>
                                            🔍 Título SEO
                                        </option>
                                        <option value="seo_description" {{ old('content_type', $prompt->content_type) === 'seo_description' ? 'selected' : '' }}>
                                            🔍 Meta descripción SEO
                                        </option>
                                        <option value="seo_keywords" {{ old('content_type', $prompt->content_type) === 'seo_keywords' ? 'selected' : '' }}>
                                            🔑 Palabras clave SEO
                                        </option>
                                        <option value="features" {{ old('content_type', $prompt->content_type) === 'features' ? 'selected' : '' }}>
                                            ⭐ Características destacadas
                                        </option>
                                        <option value="specifications" {{ old('content_type', $prompt->content_type) === 'specifications' ? 'selected' : '' }}>
                                            📊 Especificaciones técnicas
                                        </option>
                                        <option value="benefits" {{ old('content_type', $prompt->content_type) === 'benefits' ? 'selected' : '' }}>
                                            ✨ Beneficios del producto
                                        </option>
                                        <option value="metadata" {{ old('content_type', $prompt->content_type) === 'metadata' ? 'selected' : '' }}>
                                            📋 Metadata personalizada
                                        </option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Puedes crear múltiples prompts con diferentes tipos de contenido para el mismo alcance
                                    </small>
                                    @error('content_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Idioma de salida <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('output_language') is-invalid @enderror"
                                            name="output_language" required>
                                        <option value="es" {{ old('output_language', $prompt->output_language) === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                                        <option value="en" {{ old('output_language', $prompt->output_language) === 'en' ? 'selected' : '' }}>🇬🇧 Inglés</option>
                                        <option value="fr" {{ old('output_language', $prompt->output_language) === 'fr' ? 'selected' : '' }}>🇫🇷 Francés</option>
                                        <option value="de" {{ old('output_language', $prompt->output_language) === 'de' ? 'selected' : '' }}>🇩🇪 Alemán</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">El contenido generado será escrito en este idioma</small>
                                    @error('output_language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Tono de comunicación <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('tone') is-invalid @enderror" name="tone" required>
                                        <option value="professional" {{ old('tone', $prompt->tone) === 'professional' ? 'selected' : '' }}>
                                            💼 Profesional - Formal y empresarial
                                        </option>
                                        <option value="casual" {{ old('tone', $prompt->tone) === 'casual' ? 'selected' : '' }}>
                                            😊 Casual - Relajado y cercano
                                        </option>
                                        <option value="technical" {{ old('tone', $prompt->tone) === 'technical' ? 'selected' : '' }}>
                                            🔧 Técnico - Detallado y especializado
                                        </option>
                                        <option value="friendly" {{ old('tone', $prompt->tone) === 'friendly' ? 'selected' : '' }}>
                                            🤝 Amigable - Cálido y acogedor
                                        </option>
                                        <option value="formal" {{ old('tone', $prompt->tone) === 'formal' ? 'selected' : '' }}>
                                            🎩 Formal - Serio y corporativo
                                        </option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        Define cómo se comunicará la IA con tus clientes en el contenido generado
                                    </small>
                                    @error('tone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Prioridad de ejecución
                                        <i class="fas fa-circle-question ms-1 text-muted" data-bs-toggle="tooltip" title="Cuando varios prompts coinciden, se usa el de mayor prioridad"></i>
                                    </label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror"
                                           name="priority" value="{{ old('priority', $prompt->priority) }}" min="0" max="100">
                                    <small class="text-muted d-block mt-1">
                                        <strong>0-25:</strong> Baja · <strong>26-75:</strong> Media · <strong>76-100:</strong> Alta
                                    </small>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Optimización SEO
                                        <i class="fas fa-circle-question ms-1 text-muted" data-bs-toggle="tooltip" title="Genera contenido optimizado para motores de búsqueda"></i>
                                    </label>
                                    <select class="form-select" name="seo_focus">
                                        <option value="0" {{ old('seo_focus', $prompt->seo_focus ? '1' : '0') === '0' ? 'selected' : '' }}>No - Contenido estándar</option>
                                        <option value="1" {{ old('seo_focus', $prompt->seo_focus ? '1' : '0') === '1' ? 'selected' : '' }}>Sí - Incluir keywords y estructura SEO</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        Activa esto para incluir palabras clave y técnicas de SEO en el contenido
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Estado</label>
                                    <select class="form-select" name="is_active">
                                        <option value="1" {{ old('is_active', $prompt->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>✓ Activo - En uso</option>
                                        <option value="0" {{ old('is_active', $prompt->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>✗ Inactivo - Pausado</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Los prompts inactivos no se usan en la generación automática</small>
                                </div>

                                <!-- Version Info Card -->
                                <div class="card bg-light border-0 mt-4">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            Información de versión
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted">Versión actual:</span>
                                            <span class="badge bg-primary">v{{ $prompt->version }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted">Creado:</span>
                                            <span class="small">{{ $prompt->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-muted">Última modificación:</span>
                                            <span class="small">{{ $prompt->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Template Editor -->
                            <div class="col-lg-12 mb-3">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    Template del prompt
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Instrucciones para la IA <span class="text-danger">*</span>
                                        <span class="badge bg-primary text-white ms-2">
                                            <i class="fas fa-bolt me-1"></i>Versión {{ $prompt->version }}
                                        </span>
                                    </label>
                                    <textarea class="form-control font-monospace @error('prompt_template') is-invalid @enderror"
                                              name="prompt_template" id="promptTemplate"
                                              rows="28" required>{{ old('prompt_template', $prompt->prompt_template) }}</textarea>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Escribe instrucciones claras y específicas. Usa <code>@{{ variable }}</code> para insertar datos dinámicos del producto.
                                    </small>
                                    @error('prompt_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="alert bg-light border-start border-4">
                                    <h6 class="alert-heading mb-2">
                                        Variables disponibles
                                    </h6>
                                    <div class="row g-2 small">
                                        <div class="col-md-6">
                                            <code>@{{ product_name }}</code> - Nombre del producto<br>
                                            <code>@{{ category }}</code> - Categoría principal<br>
                                            <code>@{{ supplier_name }}</code> - Nombre del proveedor<br>
                                            <code>@{{ sku }}</code> - Código/SKU del producto
                                        </div>
                                        <div class="col-md-6">
                                            <code>@{{ price }}</code> - Precio actual<br>
                                            <code>@{{ description }}</code> - Descripción original<br>
                                            <code>@{{ features }}</code> - Características técnicas<br>
                                            <code>@{{ brand }}</code> - Marca del producto
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            Consejos para mejorar este prompt
                                        </h6>
                                        <ul class="small mb-0 ps-3">
                                            <li class="mb-2">
                                                <strong>Sé específico:</strong> Define claramente qué quieres que genere la IA (longitud, formato, estilo)
                                            </li>
                                            <li class="mb-2">
                                                <strong>Proporciona contexto:</strong> Describe el tipo de audiencia y el objetivo del contenido
                                            </li>
                                            <li class="mb-2">
                                                <strong>Usa ejemplos:</strong> Muestra el formato exacto que esperas en la respuesta
                                            </li>
                                            <li class="mb-2">
                                                <strong>Establece límites:</strong> Indica qué NO debe incluir el contenido generado
                                            </li>
                                            <li class="mb-0">
                                                <strong>Prueba y ajusta:</strong> Los mejores prompts surgen de iterar basándose en resultados reales
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="alert bg-light border-start border-4 mt-3">
                                    <small>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Recuerda:</strong> Si modificas el template, la versión pasará de <strong>{{ $prompt->version }}</strong> a <strong>{{ $prompt->version + 1 }}</strong> automáticamente.
                                        Esto permite mantener un historial de cambios y revertir si es necesario.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer border-top">

                                <button type="submit" class="btn btn-primary  w-100 mb-1">
                                    Actualizar
                                </button>
                                <a href="{{ route('settings.suppliers.prompts.index') }}" class="btn btn-secondary w-100">
                                    Cancelar
                                </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Scope change handler
    $('#promptScope').on('change', function() {
        const scope = $(this).val();

        // Hide all containers first
        $('#supplierContainer').hide();
        $('#categoryContainer').hide();

        // Show relevant containers based on scope
        if (scope === 'supplier' || scope === 'supplier_category') {
            $('#supplierContainer').slideDown(200);
        }

        if (scope === 'category' || scope === 'supplier_category') {
            $('#categoryContainer').slideDown(200);
        }
    });

    // Trigger on page load
    $('#promptScope').trigger('change');

    // Auto-resize textarea
    const textarea = document.getElementById('promptTemplate');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Warn about version change
    const originalTemplate = textarea.value;
    $('form').on('submit', function(e) {
        if (textarea.value !== originalTemplate) {
            const currentVersion = {{ $prompt->version }};
            const newVersion = currentVersion + 1;

            toastr.info(
                `El template ha cambiado. La versión se actualizará de v${currentVersion} a v${newVersion}`,
                'Control de versiones',
                {timeOut: 3000}
            );
        }
    });
});
</script>
@endpush
