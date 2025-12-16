@extends('layouts.managers')

@section('content')

  @include('managers.includes.card', ['title' => 'Configuración PrestaShop'])

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
          <a href="{{ route('manager.settings.prestashop.edit') }}" class="btn btn-primary w-100">
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
            url: '{{ route("manager.settings.prestashop.check-connection") }}',
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
            url: '{{ route("manager.settings.prestashop.toggle-active") }}',
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
            url: '{{ route("manager.settings.prestashop.reset-stats") }}',
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
            url: '{{ route("manager.settings.prestashop.test-sync") }}',
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
            url: '{{ route("manager.settings.prestashop.get-stats") }}',
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
});
</script>
@endpush
@endsection
