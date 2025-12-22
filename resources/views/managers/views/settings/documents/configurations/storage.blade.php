@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Configuración de Almacenamiento'])

    <!-- Mensajes de estado -->
    @if ($message = session('success'))
        <div class="alert bg-light-secondary text-black alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($message = session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-circle-exclamation me-2"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-circle-exclamation me-2"></i> Por favor, corrige los siguientes errores:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('manager.settings.documents.configurations.storage.update') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        <!-- Campo oculto para razón de cambio -->
        <input type="hidden" id="change_reason" name="reason" value=""
>

        <div class="card">
            <div class="card-body">

                <!-- Selección de disco de almacenamiento -->
                <div class="mb-4">
                    <h6 class="mb-1 fw-bold text-dark">
                        Disco de almacenamiento para documentos
                    </h6>
                    <p class="text-muted small mb-3">
                        Selecciona el disco donde se guardarán los documentos subidos por los clientes. Los discos se configuran en el archivo .env del sistema.
                    </p>

                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label fw-bold">Disco predeterminado</label>
                            <select class="form-select" id="default_storage_disk" name="default_storage_disk" required>
                                <option value="">Selecciona un disco</option>
                                @foreach($storageSettings['available_disks'] as $diskName => $disk)
                                    <option value="{{ $diskName }}"
                                            {{ $storageSettings['current_disk'] === $diskName ? 'selected' : '' }}
                                            data-driver="{{ $disk['driver'] }}"
                                            data-root="{{ $disk['root'] }}"
                                            data-url="{{ $disk['url'] ?? 'N/A' }}">
                                        {{ ucfirst($diskName) }} ({{ $disk['driver'] }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">
                                Los archivos se organizarán en: /documentos/{numero_orden}/{uid_documento}/
                            </small>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-outline-primary w-100" id="test-connection-btn">
                                <i class="fas fa-wifi me-2"></i> Probar Conexión
                            </button>
                            <div id="test-result" class="mt-2" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Información del disco seleccionado -->
                <div class="mb-4" id="diskInfo" style="display: none;">
                    <h6 class="mb-1 fw-bold text-dark">
                        Información del disco seleccionado
                    </h6>
                    <p class="text-muted small mb-3">
                        Detalles de configuración del disco actual.
                    </p>

                    <div class="border rounded p-3 bg-light">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <strong class="d-block text-muted small mb-1">Driver:</strong>
                                <span id="info-driver" class="fw-semibold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong class="d-block text-muted small mb-1">Ruta raíz:</strong>
                                <span id="info-root" class="fw-semibold text-break">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong class="d-block text-muted small mb-1">URL:</strong>
                                <span id="info-url" class="fw-semibold text-break">-</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <strong class="d-block text-muted small mb-1">Descripción:</strong>
                            <p id="info-description" class="mb-0">-</p>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Estadísticas de Almacenamiento -->
                <div class="mb-4" id="storageStatsSection" style="display: none;">
                    <h6 class="mb-1 fw-bold text-dark">
                        <i class="fas fa-chart-pie me-2"></i>Estadísticas de uso
                    </h6>
                    <p class="text-muted small mb-3">
                        Información de espacio disponible en el disco seleccionado.
                    </p>

                    <div class="border rounded p-3">
                        <div id="storage-stats-content" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Configuración en .env -->
                <div class="mb-4">
                    <h6 class="mb-1 fw-bold text-dark">
                        Configuración en .env
                    </h6>
                    <p class="text-muted small mb-3">
                        Para agregar o modificar discos de almacenamiento, edita el archivo <code>config/filesystems.php</code> y las variables de entorno en <code>.env</code>
                    </p>

                    <div class="alert alert-info mb-0" role="alert">
                        <strong><i class="fas fa-info-circle me-2"></i>Ejemplo de configuración de carpeta compartida en red:</strong>
                        <pre class="mb-0 mt-2 bg-white border rounded p-2"><code># En .env
NETWORK_SHARED_PATH=/mnt/red_compartida/documentos
NETWORK_SHARED_URL=${APP_URL}/network

# O en Windows:
NETWORK_SHARED_PATH=Z:/documentos
NETWORK_SHARED_URL=${APP_URL}/network</code></pre>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Historial de cambios -->
                <div class="mb-4" id="historySection" style="display: none;">
                    <h6 class="mb-1 fw-bold text-dark">
                        <i class="fas fa-history me-2"></i>Historial de cambios
                    </h6>
                    <p class="text-muted small mb-3">
                        Últimos cambios en la configuración de almacenamiento.
                    </p>

                    <div id="history-content" class="border rounded p-3">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de guardar -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Guardar configuración
                    </button>
                </div>

            </div>
        </div>

    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const diskSelect = document.getElementById('default_storage_disk');
            const diskInfo = document.getElementById('diskInfo');
            const storageStatsSection = document.getElementById('storageStatsSection');
            const historySection = document.getElementById('historySection');
            const infoDriver = document.getElementById('info-driver');
            const infoRoot = document.getElementById('info-root');
            const infoUrl = document.getElementById('info-url');
            const infoDescription = document.getElementById('info-description');
            const testBtn = document.getElementById('test-connection-btn');
            const testResult = document.getElementById('test-result');

            // Actualizar información, estadísticas e historial al cambiar disco
            diskSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const diskName = this.value;

                if (diskName) {
                    diskInfo.style.display = 'block';
                    storageStatsSection.style.display = 'block';
                    historySection.style.display = 'block';

                    infoDriver.textContent = selectedOption.dataset.driver || 'N/A';
                    infoRoot.textContent = selectedOption.dataset.root || 'N/A';
                    infoUrl.textContent = selectedOption.dataset.url || 'N/A';

                    // Buscar la descripción en el array original
                    @foreach($storageSettings['available_disks'] as $diskName => $disk)
                        if (diskName === '{{ $diskName }}') {
                            infoDescription.textContent = '{{ $disk['description'] }}';
                        }
                    @endforeach

                    // Cargar estadísticas
                    loadStorageStats(diskName);
                    // Cargar historial
                    loadStorageHistory();
                } else {
                    diskInfo.style.display = 'none';
                    storageStatsSection.style.display = 'none';
                    historySection.style.display = 'none';
                }
            });

            // Evento para probar conexión
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const diskName = diskSelect.value;

                if (!diskName) {
                    alert('Por favor selecciona un disco primero');
                    return;
                }

                testBtn.disabled = true;
                testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Probando...';

                fetch('{{ route("manager.settings.documents.configurations.storage.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ disk_name: diskName })
                })
                .then(response => response.json())
                .then(data => {
                    testResult.style.display = 'block';

                    if (data.success) {
                        testResult.innerHTML = `
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Conexión exitosa</strong><br>
                                ${data.message}
                                <small class="d-block mt-1">Tiempo de respuesta: ${data.response_time}ms</small>
                            </div>
                        `;
                    } else {
                        testResult.innerHTML = `
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error en la conexión</strong><br>
                                ${data.message}
                                <small class="d-block mt-1">Tiempo de respuesta: ${data.response_time}ms</small>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    testResult.style.display = 'block';
                    testResult.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error: ${error.message}
                        </div>
                    `;
                })
                .finally(() => {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '<i class="fas fa-wifi me-2"></i> Probar Conexión';
                });
            });

            // Función para cargar estadísticas
            function loadStorageStats(diskName) {
                const statsContent = document.getElementById('storage-stats-content');

                fetch(`{{ route("manager.settings.documents.configurations.storage.stats", ":disk") }}`.replace(':disk', diskName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const stats = data.stats;
                            let progressClass = 'bg-success';
                            if (stats.percent > 80) progressClass = 'bg-danger';
                            else if (stats.percent > 60) progressClass = 'bg-warning';

                            const html = `
                                <div class="row g-3">
                                    <div class="col-md-3 text-center">
                                        <strong class="d-block text-muted small mb-1">Espacio total</strong>
                                        <span class="fw-bold h6">${stats.total}</span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong class="d-block text-muted small mb-1">Espacio usado</strong>
                                        <span class="fw-bold h6">${stats.used}</span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong class="d-block text-muted small mb-1">Espacio libre</strong>
                                        <span class="fw-bold h6">${stats.free}</span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong class="d-block text-muted small mb-1">% Usado</strong>
                                        <span class="fw-bold h6">${stats.percent || 'N/A'}%</span>
                                    </div>
                                </div>
                                ${stats.percent !== null ? `
                                    <div class="mt-3">
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar ${progressClass}" role="progressbar"
                                                 style="width: ${stats.percent}%" aria-valuenow="${stats.percent}" aria-valuemin="0" aria-valuemax="100">
                                                ${stats.percent}%
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                            `;
                            statsContent.innerHTML = html;
                        }
                    })
                    .catch(error => {
                        statsContent.innerHTML = `<div class="alert alert-warning mb-0">No se pudo cargar las estadísticas</div>`;
                    });
            }

            // Función para cargar historial
            function loadStorageHistory() {
                const historyContent = document.getElementById('history-content');

                fetch('{{ route("manager.settings.documents.configurations.storage.history") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.history.length > 0) {
                            let html = '<div class="timeline">';
                            data.history.forEach((record, index) => {
                                html += `
                                    <div class="timeline-item ${index === 0 ? 'active' : ''}">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <p class="mb-1">
                                                <strong>${record.new_disk}</strong>
                                                <small class="text-muted">${record.action_label}</small>
                                            </p>
                                            ${record.old_disk ? `<small class="text-muted">Anterior: ${record.old_disk}</small><br>` : ''}
                                            <small class="text-muted">Por: ${record.user || 'Sistema'}</small><br>
                                            <small class="text-muted">${record.created_at_relative}</small>
                                            ${record.reason ? `<br><small class="text-muted">Razón: ${record.reason}</small>` : ''}
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            historyContent.innerHTML = html;
                        } else {
                            historyContent.innerHTML = '<p class="text-muted text-center mb-0">No hay cambios registrados</p>';
                        }
                    })
                    .catch(error => {
                        historyContent.innerHTML = '<p class="text-muted text-center mb-0">No se pudo cargar el historial</p>';
                    });
            }

            // Cargar información del disco actual al cargar
            if (diskSelect.value) {
                diskSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            padding: 20px 0 20px 50px;
            position: relative;
            border-left: 2px solid #ddd;
        }

        .timeline-item.active {
            border-left-color: #0d6efd;
        }

        .timeline-marker {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            position: absolute;
            left: -9px;
            top: 25px;
            border: 3px solid white;
        }

        .timeline-content {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
    </style>

@endsection
