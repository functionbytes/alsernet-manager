@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Gestión de almacenamiento'])

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

    <div class="row">
        <!-- Panel izquierdo: Lista de discos -->
        <div class="col-lg-7">

            <!-- Discos del sistema -->
            <div class="card mb-4">
                <div class="card-header bg-light-info">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-server me-2"></i>Discos del sistema
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Configurados en <code>config/filesystems.php</code> y <code>.env</code>
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="25%">Nombre</th>
                                    <th width="15%">Driver</th>
                                    <th width="45%">Configuración</th>
                                    <th width="15%" class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($storageData['system_disks'] as $disk)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-database text-primary me-2"></i>
                                                <strong>{{ $disk['name'] }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ strtoupper($disk['driver']) }}
                                            </span>
                                        </td>
                                        <td class="small">
                                            <div class="text-truncate" style="max-width: 300px;"
                                                 title="{{ $disk['root'] }}">
                                                {{ $disk['root'] }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="fas fa-check-circle me-1"></i>Activo
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay discos del sistema configurados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Discos personalizados -->
            <div class="card">
                <div class="card-header bg-light-primary">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-hard-drive me-2"></i>Discos personalizados
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Discos configurados dinámicamente para almacenamiento en red
                    </p>

                    <div id="customDisksContainer">
                        @forelse($storageData['custom_disks'] as $index => $disk)
                            @php
                                $isFromConfig = $disk['from_config'] ?? false;
                                $borderClass = $isFromConfig ? 'border-warning' : 'border-primary';
                            @endphp
                            <div class="card mb-3 border {{ $borderClass }} shadow-sm" data-disk-index="{{ $index }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            @php
                                                $iconClass = match($disk['driver']) {
                                                    'local' => 'fa-folder text-success',
                                                    'ftp' => 'fa-network-wired text-primary',
                                                    'sftp' => 'fa-lock text-info',
                                                    's3' => 'fa-cloud text-warning',
                                                    default => 'fa-hdd text-secondary'
                                                };
                                            @endphp
                                            <i class="fas {{ $iconClass }} fa-2x me-3"></i>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="mb-0">{{ $disk['name'] }}</h6>
                                                    @if($isFromConfig)
                                                        <span class="badge bg-warning-subtle text-warning">
                                                            <i class="fas fa-cog me-1"></i>Config
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">
                                                            <i class="fas fa-database me-1"></i>BD
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    {{ $storageData['driver_options'][$disk['driver']] ?? $disk['driver'] }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="btn-group">
                                            @if(!$isFromConfig)
                                                <button type="button" class="btn btn-sm btn-light-primary edit-disk-btn"
                                                        data-disk-index="{{ $index }}"
                                                        data-disk-data='@json($disk)'>
                                                    <i class="fas fa-edit me-1"></i>Editar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light-danger delete-disk-btn"
                                                        data-disk-name="{{ $disk['name'] }}">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-light-secondary" disabled>
                                                    <i class="fas fa-info-circle me-1"></i>Solo lectura
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        @if($disk['driver'] === 'local')
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Ruta:</small>
                                                <code class="text-break">{{ $disk['root'] ?? 'N/A' }}</code>
                                            </div>
                                            @if(isset($disk['url']))
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">URL:</small>
                                                    <code class="text-break">{{ $disk['url'] }}</code>
                                                </div>
                                            @endif
                                        @elseif(in_array($disk['driver'], ['ftp', 'sftp']))
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Host:</small>
                                                <code>{{ $disk['host'] ?? 'N/A' }}</code>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Puerto:</small>
                                                <code>{{ $disk['port'] ?? 'default' }}</code>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block">Usuario:</small>
                                                <code>{{ $disk['username'] ?? 'N/A' }}</code>
                                            </div>
                                        @elseif($disk['driver'] === 's3')
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Bucket:</small>
                                                <code>{{ $disk['bucket'] ?? 'N/A' }}</code>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Región:</small>
                                                <code>{{ $disk['region'] ?? 'N/A' }}</code>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info text-center" role="alert">
                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                <strong>No hay discos personalizados</strong>
                                <p class="mb-0 mt-2 small">Agrega uno usando el formulario de la derecha</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho: Formulario -->
        <div class="col-lg-5">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-light-success">
                    <h5 class="mb-0 text-dark" id="formTitle">
                        <i class="fas fa-plus-circle me-2"></i>Agregar disco nuevo
                    </h5>
                </div>
                <div class="card-body">
                    <form id="diskForm">
                        <input type="hidden" id="editing_index" name="editing_index" value="">

                        <!-- Información básica -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Información básica
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre del disco</label>
                                <input type="text" class="form-control" id="disk_name" name="disk_name" required
                                       placeholder="ej: network_shared">
                                <small class="text-muted">Sin espacios, solo letras, números y guiones bajos</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipo de almacenamiento</label>
                                <select class="form-select" id="disk_driver" name="disk_driver" required>
                                    <option value="">Selecciona un tipo</option>
                                    @foreach($storageData['driver_options'] as $driverKey => $driverLabel)
                                        <option value="{{ $driverKey }}">
                                            {{ $driverLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Campos específicos por driver -->
                        <div id="driverFields" style="display: none;">
                            <!-- Local -->
                            <div class="driver-fields mb-4" data-driver="local" style="display: none;">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-folder me-2 text-success"></i>Configuración local
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ruta raíz</label>
                                    <input type="text" class="form-control" name="local_root"
                                           placeholder="/mnt/red_compartida">
                                    <small class="text-muted">Ruta absoluta en el servidor</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">URL (opcional)</label>
                                    <input type="text" class="form-control" name="local_url"
                                           placeholder="http://localhost/storage">
                                    <small class="text-muted">URL pública para acceder a los archivos</small>
                                </div>
                            </div>

                            <!-- FTP -->
                            <div class="driver-fields mb-4" data-driver="ftp" style="display: none;">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-network-wired me-2 text-primary"></i>Configuración FTP
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Host</label>
                                        <input type="text" class="form-control" name="ftp_host"
                                               placeholder="ftp.ejemplo.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Puerto</label>
                                        <input type="number" class="form-control" name="ftp_port"
                                               placeholder="21">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Usuario</label>
                                        <input type="text" class="form-control" name="ftp_username">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Contraseña</label>
                                        <input type="password" class="form-control" name="ftp_password">
                                    </div>
                                </div>
                            </div>

                            <!-- SFTP -->
                            <div class="driver-fields mb-4" data-driver="sftp" style="display: none;">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-lock me-2 text-info"></i>Configuración SFTP
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Host</label>
                                        <input type="text" class="form-control" name="sftp_host"
                                               placeholder="sftp.ejemplo.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Puerto</label>
                                        <input type="number" class="form-control" name="sftp_port"
                                               placeholder="22">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Usuario</label>
                                        <input type="text" class="form-control" name="sftp_username">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Contraseña</label>
                                        <input type="password" class="form-control" name="sftp_password">
                                    </div>
                                </div>
                            </div>

                            <!-- S3 -->
                            <div class="driver-fields mb-4" data-driver="s3" style="display: none;">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-cloud me-2 text-warning"></i>Configuración Amazon S3
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Bucket</label>
                                    <input type="text" class="form-control" name="s3_bucket"
                                           placeholder="mi-bucket">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Región</label>
                                    <input type="text" class="form-control" name="s3_region"
                                           placeholder="us-east-1">
                                    <small class="text-muted">Región de AWS donde está el bucket</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Access Key ID</label>
                                    <input type="text" class="form-control" name="s3_key">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Secret Access Key</label>
                                    <input type="password" class="form-control" name="s3_secret">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Guardar disco
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="cancelBtn" style="display: none;">
                                <i class="fas fa-times me-2"></i>Cancelar edición
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Form oculto para eliminar -->
    <form id="deleteDiskForm" method="POST" action="{{ route('manager.settings.storage.destroy') }}" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="disk_name" id="delete_disk_name">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const driverSelect = document.getElementById('disk_driver');
            const driverFields = document.getElementById('driverFields');
            const diskForm = document.getElementById('diskForm');
            const formTitle = document.getElementById('formTitle');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const editingIndex = document.getElementById('editing_index');

            // Mostrar campos según el driver seleccionado
            driverSelect.addEventListener('change', function() {
                const selectedDriver = this.value;

                // Ocultar todos los campos
                document.querySelectorAll('.driver-fields').forEach(field => {
                    field.style.display = 'none';
                });

                if (selectedDriver) {
                    driverFields.style.display = 'block';
                    const driverField = document.querySelector(`.driver-fields[data-driver="${selectedDriver}"]`);
                    if (driverField) {
                        driverField.style.display = 'block';
                    }
                } else {
                    driverFields.style.display = 'none';
                }
            });

            // Manejar edición de discos
            document.querySelectorAll('.edit-disk-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = this.dataset.diskIndex;
                    const diskData = JSON.parse(this.dataset.diskData);

                    // Cambiar título del formulario
                    formTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Editar disco: ' + diskData.name;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Actualizar disco';
                    cancelBtn.style.display = 'block';
                    editingIndex.value = index;

                    // Cargar datos en el formulario
                    document.getElementById('disk_name').value = diskData.name;
                    document.getElementById('disk_driver').value = diskData.driver;

                    // Disparar evento change para mostrar campos correctos
                    driverSelect.dispatchEvent(new Event('change'));

                    // Cargar campos específicos según el driver
                    if (diskData.driver === 'local') {
                        document.querySelector('[name="local_root"]').value = diskData.root || '';
                        document.querySelector('[name="local_url"]').value = diskData.url || '';
                    } else if (diskData.driver === 'ftp' || diskData.driver === 'sftp') {
                        document.querySelector(`[name="${diskData.driver}_host"]`).value = diskData.host || '';
                        document.querySelector(`[name="${diskData.driver}_port"]`).value = diskData.port || '';
                        document.querySelector(`[name="${diskData.driver}_username"]`).value = diskData.username || '';
                        document.querySelector(`[name="${diskData.driver}_password"]`).value = diskData.password || '';
                    } else if (diskData.driver === 's3') {
                        document.querySelector('[name="s3_bucket"]').value = diskData.bucket || '';
                        document.querySelector('[name="s3_region"]').value = diskData.region || '';
                        document.querySelector('[name="s3_key"]').value = diskData.key || '';
                        document.querySelector('[name="s3_secret"]').value = diskData.secret || '';
                    }

                    // Scroll al formulario
                    document.querySelector('.sticky-top').scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            // Cancelar edición
            cancelBtn.addEventListener('click', function() {
                resetForm();
            });

            function resetForm() {
                diskForm.reset();
                editingIndex.value = '';
                formTitle.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Agregar disco nuevo';
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar disco';
                cancelBtn.style.display = 'none';
                driverFields.style.display = 'none';
            }

            // Manejar envío del formulario
            diskForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const driver = formData.get('disk_driver');
                const diskName = formData.get('disk_name');
                const editIndex = formData.get('editing_index');

                // Construir objeto de disco
                const diskData = {
                    name: diskName,
                    driver: driver
                };

                // Agregar campos específicos según el driver
                if (driver === 'local') {
                    diskData.root = formData.get('local_root');
                    diskData.url = formData.get('local_url') || null;
                } else if (driver === 'ftp' || driver === 'sftp') {
                    diskData.host = formData.get(`${driver}_host`);
                    diskData.username = formData.get(`${driver}_username`);
                    diskData.password = formData.get(`${driver}_password`) || null;
                    diskData.port = formData.get(`${driver}_port`) || null;
                } else if (driver === 's3') {
                    diskData.bucket = formData.get('s3_bucket');
                    diskData.region = formData.get('s3_region');
                    diskData.key = formData.get('s3_key');
                    diskData.secret = formData.get('s3_secret');
                }

                // Obtener discos existentes
                const existingDisks = @json($storageData['custom_disks']);

                // Si estamos editando, reemplazar el disco en el índice correcto
                if (editIndex !== '') {
                    existingDisks[parseInt(editIndex)] = diskData;
                } else {
                    // Si es nuevo, agregarlo
                    existingDisks.push(diskData);
                }

                // Enviar al servidor
                fetch('{{ route("manager.settings.storage.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ disks: existingDisks })
                })
                .then(response => response.json())
                .then(data => {
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al guardar el disco');
                });
            });

            // Manejar eliminación de discos
            document.querySelectorAll('.delete-disk-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const diskName = this.dataset.diskName;

                    if (confirm(`¿Estás seguro de que deseas eliminar el disco "${diskName}"?\n\nEsta acción no se puede deshacer.`)) {
                        document.getElementById('delete_disk_name').value = diskName;
                        document.getElementById('deleteDiskForm').submit();
                    }
                });
            });
        });
    </script>

@endsection
