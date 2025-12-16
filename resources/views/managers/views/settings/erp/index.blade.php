@extends('layouts.managers')

@section('content')

  @include('managers.includes.card', ['title' => 'Configuración ERP'])

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
          <a href="{{ route('manager.settings.erp.edit') }}" class="btn btn-primary w-100">
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

        <!-- Limpiar Cache -->
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-primary w-100" id="clearCacheBtn">
           Cache
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
                  <i class="fa-duotone fa-power-off fs-7 text-dark"></i>
                </div>
                <div>
                  <h6 class="mb-1 text-muted">Estado</h6>
                  <h5 class="mb-0" id="serviceStatus">
                   @if($settings['erp_is_active'] === 'yes')
                      <span class="badge bg-success">Activo</span>
                    @else
                      <span class="badge bg-danger">Inactivo</span>
                    @endif
                  </h5>
                </div>
              </div>
              <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-dark w-100" id="toggleServiceBtn">
                  {{ $settings['erp_is_active'] === 'yes' ? 'Desactivar' : 'Activar' }}
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
                  <i class="fa-duotone fa-signal fs-7 text-dark"></i>
                </div>
                <div>
                  <h6 class="mb-1 text-muted">Conexión</h6>
                  <h5 class="mb-0" id="connectionStatus">
                    @if($settings['erp_last_connection_status'] === 'online')
                      <span class="badge bg-success">Online</span>
                    @elseif($settings['erp_last_connection_status'] === 'offline')
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
                  <i class="fa-duotone fa-chart-line fs-7 text-dark"></i>
                </div>
                <div>
                  <h6 class="mb-1 text-muted">Peticiones</h6>
                  <h5 class="mb-0" id="totalRequests">
                    {{ number_format((int)$settings['erp_total_requests']) }}
                  </h5>
                  <p class="text-xs mb-0">
                    <span class="text-muted" id="failedRequests">{{ (int)$settings['erp_failed_requests'] }}</span> errores
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
                  <i class="fa-duotone fa-check-circle fs-7 text-dark"></i>
                </div>
                <div>
                  <h6 class="mb-1 text-muted">Tasa de Éxito</h6>
                  <h5 class="mb-0" id="successRate">
                    {{ number_format((float)((int)$settings['erp_total_requests'] > 0 ? (((int)$settings['erp_total_requests'] - (int)$settings['erp_failed_requests']) / (int)$settings['erp_total_requests']) * 100 : 100), 2) }}%
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
          <h5 class="mb-3">URLs configuradas</h5>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">API REST:</label>
            <p class="text-muted mb-0">{{ $settings['erp_api_url'] }}</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Sincronización:</label>
            <p class="text-muted mb-0">{{ $settings['erp_sync_url'] }}</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">XML-RPC:</label>
            <p class="text-muted mb-0">{{ $settings['erp_xmlrpc_url'] }}</p>
          </div>
          <div class="mb-0">
            <label class="form-label fw-semibold text-dark">SMS:</label>
            <p class="text-muted mb-0">{{ $settings['erp_sms_url'] }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card card-body h-100">
          <h5 class="mb-3">Parámetros de conexión</h5>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Timeout:</label>
            <p class="text-muted mb-0">{{ $settings['erp_timeout'] }} segundos</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Timeout de conexión:</label>
            <p class="text-muted mb-0">{{ $settings['erp_connect_timeout'] }} segundos</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Reintentos:</label>
            <p class="text-muted mb-0">{{ $settings['erp_retry_attempts'] }} intentos</p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Cache:</label>
            <p class="text-muted mb-0">
              {{ $settings['erp_enable_cache'] === 'yes' ? 'Activado' : 'Desactivado' }}
              @if($settings['erp_enable_cache'] === 'yes')
                (TTL: {{ $settings['erp_cache_ttl'] }}s)
              @endif
            </p>
          </div>
          <div class="mb-0">
            <label class="form-label fw-semibold text-dark">Última verificación:</label>
            <p class="text-muted mb-0">
              @if($settings['erp_last_connection_check'])
                {{ \Carbon\Carbon::parse($settings['erp_last_connection_check'])->diffForHumans() }}
              @else
                Nunca
              @endif
            </p>
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
            url: '{{ route("manager.settings.erp.check-connection") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#connectionStatus').html('<span class="badge bg-success">Online</span>');
                    if (response.response_time_ms) {
                        toastr.success('Conexión exitosa (' + response.response_time_ms + 'ms)', 'ERP');
                    } else {
                        toastr.success(response.message, 'ERP');
                    }
                } else {
                    $('#connectionStatus').html('<span class="badge bg-danger">Offline</span>');
                    toastr.error(response.message, 'ERP');
                }
            },
            error: function(xhr) {
                $('#connectionStatus').html('<span class="badge bg-danger">Error</span>');
                toastr.error('Error al verificar conexión', 'ERP');
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Toggle servicio activo
    $('#toggleServiceBtn').on('click', function() {
        $.ajax({
            url: '{{ route("manager.settings.erp.toggle-active") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.is_active) {
                        $('#serviceStatus').html('<span class="badge bg-success">Activo</span>');
                        $('#toggleServiceBtn').text('Desactivar Servicio');
                    } else {
                        $('#serviceStatus').html('<span class="badge bg-danger">Inactivo</span>');
                        $('#toggleServiceBtn').text('Activar Servicio');
                    }
                    toastr.success(response.message, 'ERP');
                }
            },
            error: function() {
                toastr.error('Error al cambiar estado del servicio', 'ERP');
            }
        });
    });

    // Limpiar cache
    $('#clearCacheBtn').on('click', function() {
        if (!confirm('¿Estás seguro de que deseas limpiar el cache del ERP?')) {
            return;
        }

        $.ajax({
            url: '{{ route("manager.settings.erp.clear-cache") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'ERP');
                }
            },
            error: function() {
                toastr.error('Error al limpiar cache', 'ERP');
            }
        });
    });

    // Resetear estadísticas
    $('#resetStatsBtn').on('click', function() {
        if (!confirm('¿Estás seguro de que deseas resetear las estadísticas?')) {
            return;
        }

        $.ajax({
            url: '{{ route("manager.settings.erp.reset-stats") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#totalRequests').text('0');
                    $('#failedRequests').text('0');
                    $('#successRate').text('100.00%');
                    toastr.success(response.message, 'ERP');
                }
            },
            error: function() {
                toastr.error('Error al resetear estadísticas', 'ERP');
            }
        });
    });

    // Test sincronización
    $('#testSyncBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("manager.settings.erp.test-sync") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message + ' (' + response.pending_changes + ' cambios pendientes)', 'Sincronización');
                } else {
                    toastr.error(response.message, 'Sincronización');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error en test de sincronización';
                toastr.error(message, 'Sincronización');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Refrescar estadísticas
    $('#refreshBtn').on('click', function() {
        $.ajax({
            url: '{{ route("manager.settings.erp.get-stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#totalRequests').text(data.total_requests.toLocaleString());
                    $('#failedRequests').text(data.failed_requests);
                    $('#successRate').text(data.success_rate.toFixed(2) + '%');

                    if (data.is_active) {
                        $('#serviceStatus').html('<span class="badge bg-success">Activo</span>');
                    } else {
                        $('#serviceStatus').html('<span class="badge bg-danger">Inactivo</span>');
                    }

                    toastr.info('Estadísticas actualizadas', 'ERP');
                }
            }
        });
    });
});
</script>
@endpush
@endsection
