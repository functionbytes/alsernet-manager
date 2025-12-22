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

                    <div class="mb-3">
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
            const infoDriver = document.getElementById('info-driver');
            const infoRoot = document.getElementById('info-root');
            const infoUrl = document.getElementById('info-url');
            const infoDescription = document.getElementById('info-description');

            // Actualizar información al cambiar disco
            diskSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    diskInfo.style.display = 'block';
                    infoDriver.textContent = selectedOption.dataset.driver || 'N/A';
                    infoRoot.textContent = selectedOption.dataset.root || 'N/A';
                    infoUrl.textContent = selectedOption.dataset.url || 'N/A';

                    // Buscar la descripción en el array original
                    const diskName = this.value;
                    @foreach($storageSettings['available_disks'] as $diskName => $disk)
                        if (diskName === '{{ $diskName }}') {
                            infoDescription.textContent = '{{ $disk['description'] }}';
                        }
                    @endforeach
                } else {
                    diskInfo.style.display = 'none';
                }
            });

            // Mostrar información del disco actual al cargar
            if (diskSelect.value) {
                diskSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

@endsection
