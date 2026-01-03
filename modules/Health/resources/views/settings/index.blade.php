@extends('layouts.theme')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <!-- Overall Status Card -->
            <div class="card border shadow-none mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">
                                <i class="fas fa-heartbeat me-2"></i>System Health Status
                            </h4>
                            <p class="card-subtitle text-muted mb-0">
                                Real-time system monitoring and health checks
                            </p>
                        </div>
                        <div class="text-end">
                            @if($overallStatus === 'ok')
                                <div class="badge bg-success fs-6 px-4 py-2">
                                    <i class="fas fa-check-circle me-2"></i>All Systems Operational
                                </div>
                            @elseif($overallStatus === 'warning')
                                <div class="badge bg-warning text-dark fs-6 px-4 py-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Warning Detected
                                </div>
                            @else
                                <div class="badge bg-danger fs-6 px-4 py-2">
                                    <i class="fas fa-times-circle me-2"></i>System Issues
                                </div>
                            @endif
                            <div class="text-muted mt-2 small">
                                Last checked: <span id="lastChecked">{{ now()->format('H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Checks Grid -->
            <div class="row" id="healthChecksGrid">
                @foreach($results as $result)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-start border-4
                            @if($result->status->value === 'ok') border-success
                            @elseif($result->status->value === 'warning') border-warning
                            @else border-danger
                            @endif
                            shadow-sm mb-4">
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
                                            <i class="{{ $icon }} me-2"></i>{{ $result->check->getLabel() }}
                                        </h6>
                                        <p class="mb-2 small">
                                            {{ $result->shortSummary }}
                                        </p>

                                        @if($result->meta)
                                            <div class="mt-2">
                                                @foreach($result->meta as $key => $value)
                                                    <div class="text-muted small">
                                                        <strong>{{ str_replace('_', ' ', ucfirst($key)) }}:</strong>
                                                        @if(is_bool($value))
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        @elseif(is_numeric($value))
                                                            {{ $value }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($result->notificationMessage)
                                            <div class="alert alert-danger alert-sm mt-2 mb-0 py-1 px-2">
                                                <small>{{ $result->notificationMessage }}</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ms-2">
                                        @if($result->status->value === 'ok')
                                            <span class="badge bg-success-subtle text-success rounded-pill">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @elseif($result->status->value === 'warning')
                                            <span class="badge bg-warning-subtle text-warning rounded-pill">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill">
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

            <!-- Actions Card -->
            <div class="card border shadow-none">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">Actions</h6>
                            <p class="text-muted small mb-0">Manage health check monitoring</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="refreshHealthChecks()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Now
                            </button>
                            <div class="form-check form-switch mb-0 ms-3">
                                <input class="form-check-input" type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                                <label class="form-check-label" for="autoRefresh">
                                    Auto-refresh (30s)
                                </label>
                            </div>
                        </div>
                    </div>
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Checking...';
            btn.disabled = true;

            fetch('{{ route('settings.health.check') }}')
                .then(response => response.json())
                .then(data => {
                    // Update timestamp
                    document.getElementById('lastChecked').textContent = new Date().toLocaleTimeString();

                    // Show success message
                    showToast('Health checks completed successfully', 'success');

                    // Reload page to show updated results
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch(error => {
                    showToast('Error running health checks', 'error');
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
                showToast('Auto-refresh enabled (30s)', 'info');
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
                showToast('Auto-refresh disabled', 'info');
            }
        }

        function showToast(message, type = 'success') {
            // Simple toast notification - you can enhance this with your toast library
            const colors = {
                success: '#13C672',
                error: '#FA896B',
                info: '#539BFF'
            };

            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-body bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} text-white">
                        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle me-2"></i>${message}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
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
