@extends('layouts.theme')

@section('content')
    @include('core::components.card', ['title' => 'Estado del sistema'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Estado del sistema</h5>
                        <p class="small mb-0 text-muted">Monitoreo en tiempo real del estado de salud del sistema</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        @if($overallStatus === 'ok')
                            <div class="badge bg-success-subtle text-success fs-6 px-3 py-2">
                                <i class="fas fa-check-circle me-2"></i>Sistema operativo
                            </div>
                        @elseif($overallStatus === 'warning')
                            <div class="badge bg-warning-subtle text-warning fs-6 px-3 py-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Advertencia detectada
                            </div>
                        @else
                            <div class="badge bg-danger-subtle text-danger fs-6 px-3 py-2">
                                <i class="fas fa-times-circle me-2"></i>Problemas detectados
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats Overview --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ count($results) }}</h4>
                                        <small class="text-muted">Verificaciones</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">Correctos</h6>
                                        <h4 class="mb-1 fw-bold">{{ collect($results)->where('status.value', 'ok')->count() }}</h4>
                                        <small class="text-muted">Funcionando bien</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-warning mb-2">Advertencias</h6>
                                        <h4 class="mb-1 fw-bold">{{ collect($results)->where('status.value', 'warning')->count() }}</h4>
                                        <small class="text-muted">Requieren atención</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-danger mb-2">Errores</h6>
                                        <h4 class="mb-1 fw-bold">{{ collect($results)->whereNotIn('status.value', ['ok', 'warning'])->count() }}</h4>
                                        <small class="text-muted">Críticos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Bar --}}
            <div class="card-body border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Última verificación: <strong id="lastChecked">{{ now()->format('d/m/Y H:i:s') }}</strong>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                            <label class="form-check-label small" for="autoRefresh">
                                Auto-actualizar (30s)
                            </label>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="refreshHealthChecks()">
                            <i class="fas fa-sync-alt me-1"></i>Actualizar ahora
                        </button>
                    </div>
                </div>
            </div>

            {{-- Health Checks Grid --}}
            <div class="card-body">
                <div class="row g-3" id="healthChecksGrid">
                    @foreach($results as $result)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-start border-4
                                @if($result->status->value === 'ok') border-success
                                @elseif($result->status->value === 'warning') border-warning
                                @else border-danger
                                @endif
                                shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-2">
                                                @php
                                                    $icon = match($result->check->getName()) {
                                                        'DatabaseCheck' => 'fas fa-database',
                                                        'RedisCheck' => 'fas fa-server',
                                                        'CacheCheck' => 'fas fa-layer-group',
                                                        'StorageCheck' => 'fas fa-hdd',
                                                        'QueueCheck' => 'fas fa-tasks',
                                                        'EnvironmentCheck' => 'fas fa-cog',
                                                        'DebugModeCheck' => 'fas fa-bug',
                                                        'OptimizedAppCheck' => 'fas fa-tachometer-alt',
                                                        'UsedDiskSpaceCheck' => 'fas fa-chart-pie',
                                                        'DatabaseConnectionCountCheck' => 'fas fa-plug',
                                                        'ScheduleCheck' => 'fas fa-clock',
                                                        default => 'fas fa-check-circle'
                                                    };
                                                @endphp
                                                <i class="{{ $icon }} me-2 text-muted"></i>{{ $result->check->getLabel() }}
                                            </h6>
                                            <p class="mb-2 small text-muted">
                                                {{ $result->shortSummary }}
                                            </p>

                                            @if($result->meta)
                                                <div class="mt-2">
                                                    @foreach($result->meta as $key => $value)
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="text-muted small">{{ str_replace('_', ' ', ucfirst($key)) }}:</span>
                                                            <span class="small fw-bold">
                                                                @if(is_bool($value))
                                                                    <span class="badge bg-{{ $value ? 'success' : 'danger' }}-subtle text-{{ $value ? 'success' : 'danger' }}">
                                                                        {{ $value ? 'Sí' : 'No' }}
                                                                    </span>
                                                                @elseif(is_numeric($value))
                                                                    {{ $value }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($result->notificationMessage)
                                                <div class="alert alert-danger mt-2 mb-0 py-2 px-2">
                                                    <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $result->notificationMessage }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ms-2">
                                            @if($result->status->value === 'ok')
                                                <span class="badge bg-success-subtle text-success rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @elseif($result->status->value === 'warning')
                                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-exclamation"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        let autoRefreshInterval = null;

        function refreshHealthChecks() {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verificando...';
            btn.disabled = true;

            fetch('{{ route('settings.health.check') }}')
                .then(response => response.json())
                .then(data => {
                    // Update timestamp
                    const now = new Date();
                    document.getElementById('lastChecked').textContent = now.toLocaleDateString('es-ES') + ' ' + now.toLocaleTimeString('es-ES');

                    // Show success message with toastr
                    toastr.success('Verificaciones de salud completadas correctamente', 'Éxito', {
                        positionClass: 'toast-bottom-right'
                    });

                    // Reload page to show updated results
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                })
                .catch(error => {
                    toastr.error('Error al ejecutar las verificaciones de salud', 'Error', {
                        positionClass: 'toast-bottom-right'
                    });
                    console.error('Health check error:', error);
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

        function toggleAutoRefresh() {
            const checkbox = document.getElementById('autoRefresh');

            if (checkbox.checked) {
                autoRefreshInterval = setInterval(() => {
                    refreshHealthChecks();
                }, 30000); // 30 seconds
                toastr.info('Auto-actualización activada (cada 30 segundos)', 'Información', {
                    positionClass: 'toast-bottom-right'
                });
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
                toastr.info('Auto-actualización desactivada', 'Información', {
                    positionClass: 'toast-bottom-right'
                });
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
    @endpush
@endsection

<style>
    .stat-card {
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .bg-light-secondary {
        background-color: #f8f9fa;
    }

    .bg-success-subtle {
        background-color: rgba(19, 198, 114, 0.1);
    }

    .bg-danger-subtle {
        background-color: rgba(250, 137, 107, 0.1);
    }

    .bg-warning-subtle {
        background-color: rgba(254, 201, 15, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(33, 150, 243, 0.1);
    }
</style>
