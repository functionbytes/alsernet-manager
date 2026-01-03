@extends('layouts.theme')

@section('content')

    <div class="card w-100">

        <form id="formSource" method="POST" action="{{ route('settings.suppliers.sources.update', [$supplier->uid, $source->uid]) }}">

            {{ csrf_field() }}
            @method('PUT')

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Editar fuente de datos</h5>
                        <p class="card-subtitle mb-0 mt-2">
                            Modificando la fuente <strong>{{ $source->label }}</strong> del proveedor <strong>{{ $supplier->label }}</strong>.
                        </p>
                    </div>
                    <a href="{{ route('settings.suppliers.sources.index', $supplier->uid) }}" class="btn btn-light">
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
                                Nombre de la fuente
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label', $source->label) }}" required placeholder="Ej: FTP Productos">
                            <small class="form-text text-muted">Nombre descriptivo para identificar la fuente</small>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Tipo de fuente
                                <span class="text-danger">*</span>
                            </label>
                            <select name="source_type" id="sourceType" class="form-select @error('source_type') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                <option value="website" {{ old('source_type', $source->source_type) == 'website' ? 'selected' : '' }}>Sitio Web (Scraping)</option>
                                <option value="ftp" {{ old('source_type', $source->source_type) == 'ftp' ? 'selected' : '' }}>FTP</option>
                                <option value="sftp" {{ old('source_type', $source->source_type) == 'sftp' ? 'selected' : '' }}>SFTP</option>
                                <option value="api" {{ old('source_type', $source->source_type) == 'api' ? 'selected' : '' }}>API REST</option>
                                <option value="file" {{ old('source_type', $source->source_type) == 'file' ? 'selected' : '' }}>Archivo Local</option>
                                <option value="upload" {{ old('source_type', $source->source_type) == 'upload' ? 'selected' : '' }}>Carga Manual</option>
                            </select>
                            <small class="form-text text-muted">Método de extracción de datos</small>
                            @error('source_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Descripción</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3" placeholder="Descripción de la fuente y su uso">{{ old('description', $source->description) }}</textarea>
                            <small class="form-text text-muted">Notas sobre esta fuente de datos</small>
                            @error('description')
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
                                Nivel de confianza
                            </label>
                            <select name="trust_level" class="form-select @error('trust_level') is-invalid @enderror">
                                <option value="high" {{ old('trust_level', $source->trust_level) == 'high' ? 'selected' : '' }}>Alto - Datos de alta calidad</option>
                                <option value="medium" {{ old('trust_level', $source->trust_level) == 'medium' ? 'selected' : '' }}>Medio - Datos normales</option>
                                <option value="low" {{ old('trust_level', $source->trust_level) == 'low' ? 'selected' : '' }}>Bajo - Verificar manualmente</option>
                            </select>
                            <small class="form-text text-muted">Nivel de confianza de los datos extraídos</small>
                            @error('trust_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Prioridad
                            </label>
                            <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror"
                                   value="{{ old('priority', $source->priority) }}" min="1" max="100">
                            <small class="form-text text-muted">Orden de ejecución (1 = más alto)</small>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Estado
                            </label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', $source->is_active) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active', $source->is_active) == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Notas de uso</label>
                            <textarea name="usage_notes" class="form-control @error('usage_notes') is-invalid @enderror"
                                      rows="2" placeholder="Ej: Usar solo para inspiración, no copiar directamente">{{ old('usage_notes', $source->usage_notes) }}</textarea>
                            <small class="form-text text-muted">Restricciones o instrucciones especiales</small>
                            @error('usage_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Information -->
                    @if($source->last_accessed_at)
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Último acceso:</small>
                                        <strong>{{ $source->last_accessed_at->format('d/m/Y H:i') }}</strong>
                                        <small class="text-muted">({{ $source->last_accessed_at->diffForHumans() }})</small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Creado:</small>
                                        <strong>{{ $source->created_at->format('d/m/Y H:i') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Dynamic Configuration Fields -->
                    <div class="col-12" id="dynamicConfigSection">
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            Configuración específica
                        </h6>
                        <div id="dynamicConfigFields"></div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            Guardar
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mb-1" id="testConnectionBtn">
                            Probar
                        </button>
                        <a href="{{ route('settings.suppliers.sources.index', $supplier->uid) }}" class="btn btn-light w-100">
                            Cancelar
                        </a>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
<script>
// Existing config data (passed from controller)
const existingConfig = @json($source->configurations ?? []);

$(document).ready(function() {
    // Dynamic configuration fields based on source type
    $('#sourceType').on('change', function() {
        const type = $(this).val();
        loadDynamicConfig(type);
    });

    // Load config on page load
    const initialType = $('#sourceType').val();
    if (initialType) {
        loadDynamicConfig(initialType);
    }

    function getConfigValue(key, defaultValue = '') {
        // Try to get from old() first, then from existingConfig
        const oldValue = '{{ old("config.' + key + '") }}';
        if (oldValue) return oldValue;

        // Search in existingConfig array for matching key
        const config = existingConfig.find(c => c.config_type === 'connection' || c.config_type === 'authentication');
        if (config && config.config_data && config.config_data[key]) {
            return config.config_data[key];
        }

        return defaultValue;
    }

    function loadDynamicConfig(type) {
        if (!type) {
            $('#dynamicConfigFields').html('');
            return;
        }

        let html = '';

        switch(type) {
            case 'website':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">URL del Sitio Web</label>
                            <input type="url" class="form-control" name="configuration[url]" placeholder="https://example.com/products" value="${getConfigValue('url')}">
                            <small class="text-muted">URL principal para scraping</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Selectores CSS (JSON)</label>
                            <textarea class="form-control font-monospace" name="configuration[selectors]" rows="4" placeholder='{"title": ".product-title", "price": ".price"}'>${getConfigValue('selectors')}</textarea>
                            <small class="text-muted">Mapa JSON de campo → selector CSS</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timeout (segundos)</label>
                            <input type="number" class="form-control" name="configuration[timeout]" value="${getConfigValue('timeout', 30)}" min="5" max="300">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rate Limit (segundos)</label>
                            <input type="number" class="form-control" name="configuration[rate_limit]" value="${getConfigValue('rate_limit', 2)}" min="0" max="60">
                        </div>
                    </div>
                `;
                break;
            case 'ftp':
            case 'sftp':
                html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="configuration[host]" placeholder="ftp.example.com" value="${getConfigValue('host')}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" class="form-control" name="configuration[port]" value="${getConfigValue('port', type === 'sftp' ? 22 : 21)}" min="1" max="65535">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="configuration[username]" value="${getConfigValue('username')}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="configuration[password]" placeholder="Dejar vacío para mantener">
                            <small class="text-muted">Solo completar si desea cambiarla</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Directorio Remoto</label>
                            <input type="text" class="form-control" name="configuration[directory]" value="${getConfigValue('directory', '/')}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Patrón de Archivo (regex)</label>
                            <input type="text" class="form-control font-monospace" name="configuration[file_pattern]" value="${getConfigValue('file_pattern')}">
                        </div>
                    </div>
                `;
                break;
            case 'api':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">URL Base de la API <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="configuration[base_url]" value="${getConfigValue('base_url')}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" name="configuration[api_key]" placeholder="Dejar vacío para mantener">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Header</label>
                            <input type="text" class="form-control" name="configuration[api_key_header]" value="${getConfigValue('api_key_header', 'Authorization')}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Headers Adicionales (JSON)</label>
                            <textarea class="form-control font-monospace" name="configuration[headers]" rows="3">${getConfigValue('headers')}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Endpoint de Productos</label>
                            <input type="text" class="form-control" name="configuration[products_endpoint]" value="${getConfigValue('products_endpoint')}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timeout (segundos)</label>
                            <input type="number" class="form-control" name="configuration[timeout]" value="${getConfigValue('timeout', 30)}" min="5" max="300">
                        </div>
                    </div>
                `;
                break;
            case 'file':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Ruta del Archivo</label>
                            <input type="text" class="form-control" name="configuration[file_path]" value="${getConfigValue('file_path')}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Formato</label>
                            <select class="form-select" name="configuration[format]">
                                <option value="csv" ${getConfigValue('format') === 'csv' ? 'selected' : ''}>CSV</option>
                                <option value="json" ${getConfigValue('format') === 'json' ? 'selected' : ''}>JSON</option>
                                <option value="xml" ${getConfigValue('format') === 'xml' ? 'selected' : ''}>XML</option>
                                <option value="excel" ${getConfigValue('format') === 'excel' ? 'selected' : ''}>Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Encoding</label>
                            <input type="text" class="form-control" name="configuration[encoding]" value="${getConfigValue('encoding', 'UTF-8')}">
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

        $('#dynamicConfigFields').html(html);
    }

    // Test connection button
    $('#testConnectionBtn').on('click', function() {
        const btn = $(this);
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Probando...');

        $.ajax({
            url: '{{ route("backups.suppliers.sources.test", [$supplier->uid, $source->uid]) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message + (response.response_time ? ` (${response.response_time}ms)` : ''), 'Conexión exitosa');
                } else {
                    toastr.error(response.message, 'Error de conexión');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al probar la conexión', 'Error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    // Form submission
    $('#formSource').on('submit', function(e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Cambios guardados');
                    setTimeout(function() {
                        window.location.href = '{{ route("backups.suppliers.sources.index", $supplier->uid) }}';
                    }, 1000);
                } else {
                    toastr.error(response.message, 'Error');
                    btn.prop('disabled', false);
                    btn.html(originalHtml);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al guardar los cambios';
                toastr.error(message, 'Error');
                btn.prop('disabled', false);
                btn.html(originalHtml);

                // Show validation errors
                if (xhr.responseJSON?.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                        const input = $('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + xhr.responseJSON.errors[key][0] + '</div>');
                    });
                }
            }
        });
    });
});
</script>
@endpush
