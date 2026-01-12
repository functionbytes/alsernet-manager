@extends('layouts.theme')

@section('content')

    <div class="card w-100">

        <form id="formSource" method="POST" action="{{ route('settings.suppliers.sources.store', $supplier->uid) }}">

            {{ csrf_field() }}

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Crear fuente de datos</h5>
                        <p class="card-subtitle mb-0 mt-2">
                            Define una nueva fuente de datos para el proveedor <strong>{{ $supplier->label }}</strong>.
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
                                   value="{{ old('label') }}" required placeholder="Ej: FTP Productos">
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
                            <select name="source_type" id="sourceType" class="form-select select2  @error('source_type') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                <option value="website" {{ old('source_type') == 'website' ? 'selected' : '' }}>Sitio Web (Scraping)</option>
                                <option value="ftp" {{ old('source_type') == 'ftp' ? 'selected' : '' }}>FTP</option>
                                <option value="sftp" {{ old('source_type') == 'sftp' ? 'selected' : '' }}>SFTP</option>
                                <option value="api" {{ old('source_type') == 'api' ? 'selected' : '' }}>API REST</option>
                                <option value="file" {{ old('source_type') == 'file' ? 'selected' : '' }}>Archivo Local</option>
                                <option value="upload" {{ old('source_type') == 'upload' ? 'selected' : '' }}>Carga Manual</option>
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
                                      rows="3" placeholder="Descripción de la fuente y su uso">{{ old('description') }}</textarea>
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
                                <option value="high" {{ old('trust_level', 'medium') == 'high' ? 'selected' : '' }}>Alto - Datos de alta calidad</option>
                                <option value="medium" {{ old('trust_level', 'medium') == 'medium' ? 'selected' : '' }}>Medio - Datos normales</option>
                                <option value="low" {{ old('trust_level') == 'low' ? 'selected' : '' }}>Bajo - Verificar manualmente</option>
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
                                   value="{{ old('priority', 10) }}" min="1" max="100">
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
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
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
                                      rows="2" placeholder="Ej: Usar solo para inspiración, no copiar directamente">{{ old('usage_notes') }}</textarea>
                            <small class="form-text text-muted">Restricciones o instrucciones especiales</small>
                            @error('usage_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dynamic Configuration Fields -->
                    <div class="col-12" id="dynamicConfigSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                           Configuración específica
                        </h6>
                        <div id="dynamicConfigFields"></div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">

                    <button type="submit" class="btn btn-primary w-100 mb-1">
                        <i class="fas fa-save me-2"></i>Guardar fuente
                    </button>
                    <a href="{{ route('settings.suppliers.sources.index', $supplier->uid) }}" class="btn btn-secondary ">
                        Cancelar
                    </a>
                </div>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Dynamic configuration fields based on source type
    $('#sourceType').on('change', function() {
        const type = $(this).val();
        loadDynamicConfig(type);
    });

    // Load config on page load if source_type is selected
    const initialType = $('#sourceType').val();
    if (initialType) {
        loadDynamicConfig(initialType);
    }

    function loadDynamicConfig(type) {
        if (!type) {
            $('#dynamicConfigSection').hide();
            $('#dynamicConfigFields').html('');
            return;
        }

        $('#dynamicConfigSection').show();
        let html = '';

        switch(type) {
            case 'website':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Configuraciones de URLs</label>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addWebsiteConfig()">
                                    <i class="fas fa-plus me-1"></i>Agregar URL
                                </button>
                            </div>
                            <small class="text-muted d-block mb-3">Puedes agregar múltiples URLs con diferentes configuraciones de scraping</small>
                            <div id="websiteConfigsContainer">
                                <div class="website-config-item border rounded p-3 mb-3" data-index="0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-semibold">Configuración #1</h6>
                                        <button type="button" class="btn btn-sm btn-danger remove-config" onclick="removeWebsiteConfig(0)" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label">URL del Sitio Web</label>
                                            <input type="url" class="form-control" name="configuration[urls][0][url]" placeholder="https://example.com/products" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Selectores CSS (JSON)</label>
                                            <textarea class="form-control font-monospace" name="configuration[urls][0][selectors]" rows="4" placeholder='{"title": ".product-title", "price": ".price", "image": "img.product@src"}'></textarea>
                                            <small class="text-muted">Mapa JSON de campo → selector CSS</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Timeout (segundos)</label>
                                            <input type="number" class="form-control" name="configuration[urls][0][timeout]" value="30" min="5" max="300">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Rate Limit (segundos)</label>
                                            <input type="number" class="form-control" name="configuration[urls][0][rate_limit]" value="2" min="0" max="60">
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <input type="text" class="form-control" name="configuration[host]" placeholder="ftp.example.com" value="{{ old('configuration.host') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" class="form-control" name="configuration[port]" value="${type === 'sftp' ? 22 : 21}" min="1" max="65535">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="configuration[username]" value="{{ old('configuration.username') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="configuration[password]" value="{{ old('configuration.password') }}" required>
                            <small class="text-muted">Será almacenada de forma segura</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Directorio Remoto</label>
                            <input type="text" class="form-control" name="configuration[directory]" placeholder="/uploads/products" value="{{ old('configuration.directory', '/') }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Patrón de Archivo (regex)</label>
                            <input type="text" class="form-control font-monospace" name="configuration[file_pattern]" placeholder="^products_.*\\.csv$" value="{{ old('configuration.file_pattern') }}">
                            <small class="text-muted">Expresión regular para filtrar archivos</small>
                        </div>
                    </div>
                `;
                break;
            case 'api':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">URL Base de la API <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="configuration[base_url]" placeholder="https://api.example.com/v1" value="{{ old('configuration.base_url') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" name="configuration[api_key]" value="{{ old('configuration.api_key') }}">
                            <small class="text-muted">Token de autenticación</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Header</label>
                            <input type="text" class="form-control" name="configuration[api_key_header]" placeholder="X-API-Key" value="{{ old('configuration.api_key_header', 'Authorization') }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Headers Adicionales (JSON)</label>
                            <textarea class="form-control font-monospace" name="configuration[headers]" rows="3" placeholder='{"Content-Type": "application/json"}'>{{ old('configuration.headers') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Endpoint de Productos</label>
                            <input type="text" class="form-control" name="configuration[products_endpoint]" placeholder="/products" value="{{ old('configuration.products_endpoint') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timeout (segundos)</label>
                            <input type="number" class="form-control" name="configuration[timeout]" value="{{ old('configuration.timeout', 30) }}" min="5" max="300">
                        </div>
                    </div>
                `;
                break;
            case 'file':
                html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Configuraciones de Archivos</label>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addFileConfig()">
                                    <i class="fas fa-plus me-1"></i>Agregar Archivo
                                </button>
                            </div>
                            <small class="text-muted d-block mb-3">Puedes agregar múltiples archivos con diferentes formatos y configuraciones</small>
                            <div id="fileConfigsContainer">
                                <div class="file-config-item border rounded p-3 mb-3" data-index="0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-semibold">Archivo #1</h6>
                                        <button type="button" class="btn btn-sm btn-danger remove-config" onclick="removeFileConfig(0)" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Ruta del Archivo</label>
                                            <input type="text" class="form-control" name="configuration[files][0][file_path]" placeholder="/var/data/products.csv" required>
                                            <small class="text-muted">Ruta absoluta o relativa al archivo</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Formato</label>
                                            <select class="form-select" name="configuration[files][0][format]">
                                                <option value="csv" selected>CSV</option>
                                                <option value="json">JSON</option>
                                                <option value="xml">XML</option>
                                                <option value="excel">Excel</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Encoding</label>
                                            <input type="text" class="form-control" name="configuration[files][0][encoding]" value="UTF-8">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
            case 'upload':
                html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Las cargas manuales no requieren configuración adicional. Los archivos serán subidos directamente por usuarios autorizados.
                    </div>
                `;
                break;
        }

        $('#dynamicConfigFields').html(html);
    }

    // Form validation
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
                    toastr.success(response.message, 'Fuente creada');
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
                const message = xhr.responseJSON?.message || 'Error al crear la fuente';
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

    // Website configurations management
    window.websiteConfigIndex = 0;

    window.addWebsiteConfig = function() {
        window.websiteConfigIndex++;
        const index = window.websiteConfigIndex;
        const newConfig = `
            <div class="website-config-item border rounded p-3 mb-3" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold">Configuración #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-config" onclick="removeWebsiteConfig(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">URL del Sitio Web</label>
                        <input type="url" class="form-control" name="configuration[urls][${index}][url]" placeholder="https://example.com/products" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Selectores CSS (JSON)</label>
                        <textarea class="form-control font-monospace" name="configuration[urls][${index}][selectors]" rows="4" placeholder='{"title": ".product-title", "price": ".price", "image": "img.product@src"}'></textarea>
                        <small class="text-muted">Mapa JSON de campo → selector CSS</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Timeout (segundos)</label>
                        <input type="number" class="form-control" name="configuration[urls][${index}][timeout]" value="30" min="5" max="300">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rate Limit (segundos)</label>
                        <input type="number" class="form-control" name="configuration[urls][${index}][rate_limit]" value="2" min="0" max="60">
                    </div>
                </div>
            </div>
        `;
        $('#websiteConfigsContainer').append(newConfig);
        updateWebsiteRemoveButtons();
    };

    window.removeWebsiteConfig = function(index) {
        $(`.website-config-item[data-index="${index}"]`).remove();
        updateWebsiteRemoveButtons();
        renumberWebsiteConfigs();
    };

    function updateWebsiteRemoveButtons() {
        const count = $('.website-config-item').length;
        if (count > 1) {
            $('.website-config-item .remove-config').show();
        } else {
            $('.website-config-item .remove-config').hide();
        }
    }

    function renumberWebsiteConfigs() {
        $('.website-config-item').each(function(index) {
            $(this).find('h6').text(`Configuración #${index + 1}`);
        });
    }

    // File configurations management
    window.fileConfigIndex = 0;

    window.addFileConfig = function() {
        window.fileConfigIndex++;
        const index = window.fileConfigIndex;
        const newConfig = `
            <div class="file-config-item border rounded p-3 mb-3" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold">Archivo #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-config" onclick="removeFileConfig(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Ruta del Archivo</label>
                        <input type="text" class="form-control" name="configuration[files][${index}][file_path]" placeholder="/var/data/products.csv" required>
                        <small class="text-muted">Ruta absoluta o relativa al archivo</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Formato</label>
                        <select class="form-select" name="configuration[files][${index}][format]">
                            <option value="csv" selected>CSV</option>
                            <option value="json">JSON</option>
                            <option value="xml">XML</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Encoding</label>
                        <input type="text" class="form-control" name="configuration[files][${index}][encoding]" value="UTF-8">
                    </div>
                </div>
            </div>
        `;
        $('#fileConfigsContainer').append(newConfig);
        updateFileRemoveButtons();
    };

    window.removeFileConfig = function(index) {
        $(`.file-config-item[data-index="${index}"]`).remove();
        updateFileRemoveButtons();
        renumberFileConfigs();
    };

    function updateFileRemoveButtons() {
        const count = $('.file-config-item').length;
        if (count > 1) {
            $('.file-config-item .remove-config').show();
        } else {
            $('.file-config-item .remove-config').hide();
        }
    }

    function renumberFileConfigs() {
        $('.file-config-item').each(function(index) {
            $(this).find('h6').text(`Archivo #${index + 1}`);
        });
    }
});
</script>
@endpush
