@extends('layouts.theme')

@section('content')

  @include('theme.components.card', ['title' => 'Configuración PrestaShop'])

  <div class="widget-content searchable-container list">

      @if(session('success'))
          <div class="alert alert-light alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      @endif

      <!-- Acciones y Controles -->
    <div class="card card-body mb-3">

      <div class="row g-2">
        <!-- Configuración -->
        <div class="col-md-3">
          <a href="{{ route('manager.backups.prestashop.edit') }}" class="btn btn-primary w-100">
            Configurar
          </a>
        </div>

        <!-- Actualizar -->
        <div class="col-md-3">
          <button type="button" class="btn btn-secondary w-100" id="refreshBtn">
            Actualizar
          </button>
        </div>

        <!-- Test Sincronización -->
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-primary w-100" id="testSyncBtn">
            Sincronizar
          </button>
        </div>

        <!-- Resetear Stats -->
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-primary w-100" id="resetStatsBtn">
            Restablecer
          </button>
        </div>
      </div>
    </div>

    <!-- Estado del Servicio -->
    <div class="card card-body mb-3">
      <h5 class="mb-3">Estado del Servicio</h5>

      <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card  bg-light-secondary ">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fa-duotone fa-power-off fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted">Estado</p>
                  <h5 class="mb-0" id="serviceStatus">
                    @if($settings['prestashop_enabled'] === 'yes')
                      <span class="badge bg-success">Habilitado</span>
                    @else
                      <span class="badge bg-danger">Deshabilitado</span>
                    @endif
                  </h5>
                </div>
              </div>
              <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-dark w-100" id="toggleServiceBtn">
                  {{ $settings['prestashop_enabled'] === 'yes' ? 'Deshabilitar' : 'Habilitar' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card  bg-light-secondary ">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fa-duotone fa-database fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted">Conexión DB</p>
                  <h5 class="mb-0" id="connectionStatus">
                    @if($stats['last_sync_status'] === 'online')
                      <span class="badge bg-success">Online</span>
                    @elseif($stats['last_sync_status'] === 'offline')
                      <span class="badge bg-danger">Offline</span>
                    @else
                      <span class="badge bg-danger">Sin verificar</span>
                    @endif
                  </h5>
                </div>
              </div>
              <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-dark w-100" id="checkConnectionBtn">
                  <span class="btn-text">Verificar</span>
                  <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card  bg-light-secondary ">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fa-duotone fa-sync fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted">Sincronizaciones</p>
                  <h5 class="mb-0" id="totalSyncs">
                    {{ number_format((int)$stats['total_syncs']) }}
                  </h5>
                  <p class="text-xs mb-0">
                    <span class="text-muted" id="failedSyncs">{{ (int)$stats['failed_syncs'] }}</span> errores
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card  bg-light-secondary ">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fa-duotone fa-check-circle fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted">Tasa de Éxito</p>
                  <h5 class="mb-0" id="successRate">
                    {{ number_format((float)$stats['success_rate'], 2) }}%
                  </h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Información de Configuración -->
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card card-body h-100">
          <h5 class="mb-3">Configuración base de datos</h5>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Host:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_db_host'] }}</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Puerto:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_db_port'] }}</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Base de Datos:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_db_database'] }}</p>
          </div>
          <div class="mb-0">
            <label class="form-label fw-semibold text-primary">URL PrestaShop:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_url'] }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card card-body h-100">
          <h5 class="mb-3">Parámetros de sincronización</h5>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Timeout:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_timeout'] }} segundos</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Timeout Conexión:</label>
            <p class="text-muted mb-0">{{ $settings['prestashop_connect_timeout'] }} segundos</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-primary">Sincronización habilitada:</label>
            <p class="text-muted mb-0">
              {{ $settings['prestashop_sync_enabled'] === 'yes' ? 'Sí' : 'No' }}
            </p>
          </div>
          <div class="mb-0">
            <label class="form-label fw-semibold text-primary">Última sincronización:</label>
            <p class="text-muted mb-0">
              @if($stats['last_sync_check'])
                {{ \Carbon\Carbon::parse($stats['last_sync_check'])->diffForHumans() }}
              @else
                Nunca
              @endif
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Blockades Synchronization -->
    <div class="card card-body mb-3">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-bold text-dark">
          Sincronización de Bloqueos de Productos
        </h5>
        <span class="badge bg-primary">Datos</span>
      </div>

      <p class="text-muted mb-3">
        Sincroniza los bloqueos de productos (DNI, ESCOPETA, RIFLE, CORTA) desde la base de datos externa de PrestaShop.
      </p>

      <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="card bg-light-secondary">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas fa-shield-alt fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted small">Total Bloqueos</p>
                  <h5 class="mb-0" id="totalBlockades">-</h5>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
          <div class="card bg-light-secondary">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas fa-sync fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted small">Sincronizaciones</p>
                  <h5 class="mb-0" id="blockadesSyncCount">-</h5>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
          <div class="card bg-light-secondary">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas fa-clock fs-7 text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted small">Última Sincronización</p>
                  <h6 class="mb-0 text-truncate" id="blockagesLastSync" title="-">-</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Etiquetas a Sincronizar</label>
        <input type="text" class="form-control form-control-sm" id="blockadeLabels"
               value="{{ setting('product_blockade_labels', 'DNI,ESCOPETA,RIFLE,CORTA') }}"
               placeholder="DNI,ESCOPETA,RIFLE,CORTA">
        <small class="text-muted">Separa las etiquetas con comas. Estas etiquetas se buscarán en la columna 'etiqueta' de las tablas.</small>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="saveBlockadeLabelsBtn">
          <i class="fas fa-save me-1"></i> Guardar Etiquetas
        </button>
      </div>

      <div class="alert alert-info alert-sm py-2 px-3 mb-4" role="alert">
        <strong>Etiquetas actuales:</strong> <span id="currentLabels">{{ setting('product_blockade_labels', 'DNI,ESCOPETA,RIFLE,CORTA') }}</span>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <button type="button" class="btn btn-primary w-100" id="syncBlockadesBtn">
            <i class="fas fa-sync-alt me-2"></i> Sincronizar Bloqueos
          </button>
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-danger w-100" id="syncBlockadesFreshBtn">
            <i class="fas fa-redo me-2"></i> Sincronizar (Limpiar y volver a sincronizar)
          </button>
        </div>
      </div>

      <!-- Add Manual Blockade Section -->
      <div class="border-top pt-3">
        <button class="btn btn-sm btn-outline-primary w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addBlockadeForm">
          <i class="fas fa-plus me-2"></i> Agregar Bloqueo Manual
        </button>

        <div class="collapse" id="addBlockadeForm">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="mb-3">Agregar Nuevo Bloqueo</h6>
              <form id="formAddBlockade">
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Source ID</label>
                    <input type="number" class="form-control form-control-sm" id="blockadeSourceId" name="source_id" required>
                    <small class="text-muted">ID de origen del producto</small>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Tipo de Producto</label>
                    <div class="btn-group w-100" role="group">
                      <input type="radio" class="btn-check" name="productType" id="productTypeSimple" value="simple" checked>
                      <label class="btn btn-outline-primary btn-sm" for="productTypeSimple">Producto Simple</label>

                      <input type="radio" class="btn-check" name="productType" id="productTypeCombination" value="combination">
                      <label class="btn btn-outline-primary btn-sm" for="productTypeCombination">Combinación</label>
                    </div>
                  </div>
                  <div class="col-md-6" id="productIdContainer">
                    <label class="form-label small">ID Producto</label>
                    <input type="number" class="form-control form-control-sm" id="blockadeIdProduct" name="id_product">
                    <small class="text-muted">Para productos simples</small>
                  </div>
                  <div class="col-md-6 d-none" id="productAttributeIdContainer">
                    <label class="form-label small">ID Atributo de Producto</label>
                    <input type="number" class="form-control form-control-sm" id="blockadeIdProductAttribute" name="id_product_attribute">
                    <small class="text-muted">Para combinaciones</small>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label small">Tipo de Bloqueo</label>
                    <select class="form-select form-select-sm" id="blockadeType" name="blockade_type" required>
                      <option value="">Seleccionar...</option>
                      <option value="dni">DNI</option>
                      <option value="escopeta">ESCOPETA</option>
                      <option value="rifle">RIFLE</option>
                      <option value="corta">CORTA</option>
                      <option value="custom">Personalizado...</option>
                    </select>
                  </div>
                  <div class="col-md-12 d-none" id="customBlockadeTypeContainer">
                    <label class="form-label small">Tipo Personalizado</label>
                    <input type="text" class="form-control form-control-sm" id="customBlockadeType" placeholder="Ingrese el tipo de bloqueo personalizado">
                  </div>
                  <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-success w-100">
                      <i class="fas fa-plus me-2"></i> Agregar Bloqueo
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

@push('scripts')
<script>
$(document).ready(function() {
    // Verificar conexión
    $('#checkConnectionBtn').on('click', function() {
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');

        btn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');

        $.ajax({
            url: '{{ route("manager.backups.prestashop.check-connection") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#connectionStatus').html('<span class="badge bg-success">Online</span>');
                    toastr.success(response.message, 'PrestaShop');
                } else {
                    $('#connectionStatus').html('<span class="badge bg-danger">Offline</span>');
                    toastr.error(response.message, 'PrestaShop');
                }
            },
            error: function(xhr) {
                $('#connectionStatus').html('<span class="badge bg-danger">Error</span>');
                toastr.error('Error al verificar conexión', 'PrestaShop');
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Toggle servicio
    $('#toggleServiceBtn').on('click', function() {
        $.ajax({
            url: '{{ route("manager.backups.prestashop.toggle-active") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.enabled) {
                        $('#serviceStatus').html('<span class="badge bg-success">Habilitado</span>');
                        $('#toggleServiceBtn').text('Deshabilitar');
                    } else {
                        $('#serviceStatus').html('<span class="badge bg-danger">Deshabilitado</span>');
                        $('#toggleServiceBtn').text('Habilitar');
                    }
                    toastr.success(response.message, 'PrestaShop');
                }
            },
            error: function() {
                toastr.error('Error al cambiar estado', 'PrestaShop');
            }
        });
    });

    // Resetear estadísticas
    $('#resetStatsBtn').on('click', function() {
        if (!confirm('¿Estás seguro de que deseas resetear las estadísticas?')) {
            return;
        }

        $.ajax({
            url: '{{ route("manager.backups.prestashop.reset-stats") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#totalSyncs').text('0');
                    $('#failedSyncs').text('0');
                    $('#successRate').text('100.00%');
                    toastr.success(response.message, 'PrestaShop');
                }
            },
            error: function() {
                toastr.error('Error al resetear estadísticas', 'PrestaShop');
            }
        });
    });

    // Test sincronización
    $('#testSyncBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("manager.backups.prestashop.test-sync") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message + ' (' + response.pending_orders + ' órdenes pendientes)', 'PrestaShop');
                } else {
                    toastr.error(response.message, 'PrestaShop');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error en test de sincronización';
                toastr.error(message, 'PrestaShop');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Refrescar estadísticas
    $('#refreshBtn').on('click', function() {
        $.ajax({
            url: '{{ route("manager.backups.prestashop.get-stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#totalSyncs').text(data.total_syncs.toLocaleString());
                    $('#failedSyncs').text(data.failed_syncs);
                    $('#successRate').text(data.success_rate.toFixed(2) + '%');

                    if (data.enabled) {
                        $('#serviceStatus').html('<span class="badge bg-success">Habilitado</span>');
                    } else {
                        $('#serviceStatus').html('<span class="badge bg-danger">Deshabilitado</span>');
                    }

                    toastr.info('Estadísticas actualizadas', 'PrestaShop');
                }
            }
        });
    });

    // ========== Product Blockades Sync ==========

    // Load blockades status on page load
    loadBlockadesStatus();

    function loadBlockadesStatus() {
        $.ajax({
            url: '{{ route("manager.backups.prestashop.blockades-status") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#totalBlockades').text(response.total_blockades.toLocaleString());
                    $('#blockadesSyncCount').text(response.sync_count.toLocaleString());
                    $('#blockagesLastSync').text(response.last_sync).attr('title', response.last_sync);
                }
            },
            error: function() {
                $('#totalBlockades').text('Error');
                $('#blockadesSyncCount').text('Error');
                $('#blockagesLastSync').text('Error');
            }
        });
    }

    // Sync blockades
    $('#syncBlockadesBtn').on('click', function() {
        syncBlockades(false);
    });

    // Sync blockades fresh (delete all and re-sync)
    $('#syncBlockadesFreshBtn').on('click', function() {
        if (!confirm('¿Estás seguro de que deseas eliminar todos los bloqueos existentes y volver a sincronizar? Esta acción no se puede deshacer.')) {
            return;
        }
        syncBlockades(true);
    });

    function syncBlockades(fresh) {
        const btn = fresh ? $('#syncBlockadesFreshBtn') : $('#syncBlockadesBtn');
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Sincronizando...');

        $.ajax({
            url: '{{ route("manager.backups.prestashop.sync-blockades") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                fresh: fresh
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Bloqueos de Productos');
                    loadBlockadesStatus();
                } else {
                    toastr.warning(response.message, 'Bloqueos de Productos');
                }

                // Show output in console for debugging
                if (response.output) {
                    console.log('Sync Output:', response.output);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al sincronizar bloqueos';
                toastr.error(message, 'Bloqueos de Productos');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    }

    // ========== Manual Blockade Management ==========

    // Save blockade labels
    $('#saveBlockadeLabelsBtn').on('click', function() {
        const labels = $('#blockadeLabels').val().trim();

        if (!labels) {
            toastr.error('Por favor ingresa al menos una etiqueta', 'Etiquetas');
            return;
        }

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: '{{ route("manager.backups.prestashop.save-blockade-labels") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                labels: labels
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Etiquetas');
                    $('#currentLabels').text(labels);
                } else {
                    toastr.error(response.message, 'Etiquetas');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al guardar las etiquetas';
                toastr.error(message, 'Etiquetas');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    // Toggle between simple product and combination
    $('input[name="productType"]').on('change', function() {
        const isSimple = $(this).val() === 'simple';

        if (isSimple) {
            $('#productIdContainer').removeClass('d-none');
            $('#productAttributeIdContainer').addClass('d-none');
            $('#blockadeIdProduct').prop('required', true);
            $('#blockadeIdProductAttribute').prop('required', false).val('');
        } else {
            $('#productIdContainer').addClass('d-none');
            $('#productAttributeIdContainer').removeClass('d-none');
            $('#blockadeIdProduct').prop('required', false).val('');
            $('#blockadeIdProductAttribute').prop('required', true);
        }
    });

    // Show/hide custom blockade type input
    $('#blockadeType').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customBlockadeTypeContainer').removeClass('d-none');
            $('#customBlockadeType').prop('required', true);
        } else {
            $('#customBlockadeTypeContainer').addClass('d-none');
            $('#customBlockadeType').prop('required', false);
        }
    });

    // Handle add blockade form submission
    $('#formAddBlockade').on('submit', function(e) {
        e.preventDefault();

        const blockadeType = $('#blockadeType').val();
        const finalBlockadeType = blockadeType === 'custom' ? $('#customBlockadeType').val() : blockadeType;
        const productType = $('input[name="productType"]:checked').val();

        const data = {
            source_id: $('#blockadeSourceId').val(),
            blockade_type: finalBlockadeType
        };

        // Add either product_id or product_attribute_id based on product type
        if (productType === 'simple') {
            data.product_id = $('#blockadeIdProduct').val();
        } else {
            data.product_attribute_id = $('#blockadeIdProductAttribute').val();
        }

        $.ajax({
            url: '{{ route("manager.backups.prestashop.create-blockade") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: data,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Bloqueo Manual');
                    $('#formAddBlockade')[0].reset();
                    $('#customBlockadeTypeContainer').addClass('d-none');
                    $('#addBlockadeForm').collapse('hide');
                    loadBlockadesStatus();
                } else {
                    toastr.error(response.message, 'Bloqueo Manual');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al crear el bloqueo';
                toastr.error(message, 'Bloqueo Manual');
            }
        });
    });
});
</script>
@endpush
@endsection
