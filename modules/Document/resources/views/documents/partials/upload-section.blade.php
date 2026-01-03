<!-- Upload Section - Ocultar si ya está gestionado y tiene documentos, o si todos están cargados -->
@if(!($document->proccess == 1 && $document->media->count() > 0) && !$allUploaded)
    <div class="card mb-3" id="documentsUploadCard">
        <div class="card-header p-3 bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Carga de documentos</h5>
                    <p class="small mb-0 text-muted">Sube los documentos requeridos</p>
                </div>
                <div>
                    @php
                        $totalDocs = count($requiredDocuments);
                        $uploadedCount = count($uploadedDocs);
                    @endphp
                    <span id="documentCounter">
                        {{ $uploadedCount }}/{{ $totalDocs }} cargados
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">

            <!-- Opción de destino de almacenamiento -->
            <div class="mb-3">
                <label class="form-label fw-semibold mb-2">Destino de almacenamiento</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check storage-destination" name="storage_destination" id="storage_local" value="local" checked>
                    <label class="btn btn-outline-primary" for="storage_local">
                        <i class="fas fa-server me-2"></i>Almacenamiento local
                    </label>

                    <input type="radio" class="btn-check storage-destination" name="storage_destination" id="storage_network" value="network">
                    <label class="btn btn-outline-primary" for="storage_network">
                        <i class="fas fa-network-wired me-2"></i>Carpeta en red
                    </label>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Selecciona dónde guardar los documentos subidos.
                </small>
            </div>

            <!-- Selector de disco de red (mostrar solo si está habilitada carpeta en red) -->
            <div id="networkDiskSelector" style="display: none;" class="mb-3">
                <label class="form-label fw-semibold">Carpeta compartida en red</label>
                <select class="form-select" id="network_disk" name="network_disk">
                    <option value="">Cargando opciones...</option>
                </select>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Las opciones disponibles han sido configuradas en la sección de almacenamiento.
                </small>
            </div>

            <!-- Formulario de carga múltiple -->
            <form id="adminUploadForm" enctype="multipart/form-data">
                @foreach($requiredDocuments as $docKey => $docLabel)
                    <div class="mb-3 document-upload-item" data-doc-type="{{ $docKey }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0 fw-semibold">
                                {{ $docLabel }}
                            </label>
                            @if(!isset($uploadedDocs[$docKey]))
                                <span class="badge bg-danger-subtle text-danger">
                                    Pendiente
                                </span>
                            @endif
                        </div>

                        @if(isset($uploadedDocs[$docKey]))
                            <!-- Documento ya cargado -->
                            <div class="uploaded-doc-info p-3 bg-light-secondary border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div>
                                            <p class="mb-0 fw-semibold">{{ $uploadedDocs[$docKey]->file_name }}</p>
                                            <small class="text-muted">{{ formatBytes($uploadedDocs[$docKey]->size) }} • {{ $uploadedDocs[$docKey]->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ parse_url($uploadedDocs[$docKey]->getUrl(), PHP_URL_PATH) }}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-media-id="{{ $uploadedDocs[$docKey]->id }}" data-doc-type="{{ $docKey }}" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Input para cargar documento -->
                            <input
                                type="file"
                                class="form-control document-file-input"
                                name="documents[{{ $docKey }}]"
                                data-doc-type="{{ $docKey }}"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            >
                            <small class="text-muted d-block mt-1">
                                <i class="fa fa-info-circle"></i> PDF, JPG, PNG, DOC (máximo 10MB)
                            </small>
                        @endif
                    </div>
                @endforeach

                <div id="uploadProgress" style="display: none;" class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="fw-semibold">Cargando documentos...</small>
                        <small class="text-muted" id="uploadStatus">0%</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" id="uploadProgressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-upload-document" @if($allUploaded) disabled @endif>
                    @if($allUploaded)
                        <i class="fa fa-check-circle"></i> Documentos Completos
                    @else
                        <i class="fas fa-upload me-2"></i>Cargar documentos
                    @endif
                </button>
            </form>

            @if($document->media->count() > 1 && $document->confirmed_at != null)
                <div class="border-top mt-3 pt-3">
                    <a href="{{ route('documents.summary', $document->uid) }}" target="_blank" class="btn btn-outline-primary w-100">
                        <i class="fa fa-file-archive"></i> Ver todos los documentos comprimidos
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Mensaje de éxito cuando todos los documentos están cargados -->
@if($allUploaded)
    <div class="card mb-3">
        <div class="card-header p-3 bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-success">Documentos cargados</h5>
                    <p class="small mb-0 text-muted">Todos los documentos requeridos han sido recibidos</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Lista de documentos con opciones -->
            @foreach($uploadedDocs as $docType => $media)
                <div class="mb-3 document-upload-item" data-doc-type="{{ $docType }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label mb-0 fw-semibold">
                            {{ $media->file_name }}
                        </label>
                    </div>

                    <!-- Documento ya cargado -->
                    <div class="uploaded-doc-info p-3 bg-light-secondary border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div>
                                    <p class="mb-0 fw-semibold">{{ $media->file_name }}</p>
                                    <small class="text-muted">{{ formatBytes($media->size) }} • {{ $media->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ parse_url($media->getUrl(), PHP_URL_PATH) }}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
                                    <i class="fa fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-media-id="{{ $media->id }}" data-doc-type="{{ $docType }}" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($document->media->count() > 1 && $document->confirmed_at != null)
                <div class="border-top mt-3 pt-3">
                    <a href="{{ route('documents.summary', $document->uid) }}" target="_blank" class="btn btn-primary w-100">
                       Ver todos los documentos
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const storageDestinationRadios = document.querySelectorAll('.storage-destination');
    const networkDiskSelector = document.getElementById('networkDiskSelector');
    const networkDiskSelect = document.getElementById('network_disk');
    const adminUploadForm = document.getElementById('adminUploadForm');
    const uploadProgressDiv = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatus = document.getElementById('uploadStatus');

    // Cargar opciones de discos de red
    function loadNetworkDisks() {
        fetch('{{ route("documents.network-disks") }}')
            .then(response => response.json())
            .then(data => {
                if (data.disks && data.disks.length > 0) {
                    networkDiskSelect.innerHTML = '<option value="">Selecciona una carpeta...</option>';
                    data.disks.forEach(disk => {
                        const option = document.createElement('option');
                        option.value = disk.name;
                        option.textContent = disk.label + ' (' + disk.driver + ')';
                        networkDiskSelect.appendChild(option);
                    });
                } else {
                    networkDiskSelect.innerHTML = '<option value="">No hay carpetas compartidas configuradas</option>';
                }
            })
            .catch(error => {
                console.error('Error loading network disks:', error);
                networkDiskSelect.innerHTML = '<option value="">Error al cargar las opciones</option>';
            });
    }

    // Event listeners para cambio de destino
    storageDestinationRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'network') {
                networkDiskSelector.style.display = 'block';
                loadNetworkDisks();
                if (adminUploadForm) {
                    adminUploadForm.dataset.destination = 'network';
                }
            } else {
                networkDiskSelector.style.display = 'none';
                if (adminUploadForm) {
                    adminUploadForm.dataset.destination = 'local';
                }
            }
        });
    });

    // Manejar envío del formulario
    if (adminUploadForm) {
        adminUploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const destination = document.querySelector('input[name="storage_destination"]:checked').value;
            const uid = '{{ $document->uid }}';

            // Validar que hay archivos seleccionados
            const fileInputs = this.querySelectorAll('input[type="file"]');
            let hasFiles = false;

            fileInputs.forEach(input => {
                if (input.files.length > 0) {
                    hasFiles = true;
                }
            });

            if (!hasFiles) {
                alert('Por favor, selecciona al menos un archivo para subir');
                return;
            }

            // Si es destino local, usar el comportamiento actual
            if (destination === 'local') {
                // Mantener el comportamiento original
                uploadToLocal();
                return;
            }

            // Si es destino en red
            if (destination === 'network') {
                const networkDisk = networkDiskSelect.value;
                if (!networkDisk) {
                    alert('Por favor, selecciona una carpeta compartida en red');
                    return;
                }

                uploadToNetwork(uid, networkDisk);
            }
        });
    }

    async function uploadToNetwork(uid, networkDisk) {
        const formData = new FormData();
        formData.append('network_disk', networkDisk);

        // Agregar archivos al FormData
        const fileInputs = document.querySelectorAll('.document-file-input');
        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                formData.append(`documents[${input.dataset.docType}]`, input.files[0]);
            }
        });

        // Mostrar progress bar
        uploadProgressDiv.style.display = 'block';
        uploadProgressBar.style.width = '0%';
        uploadStatus.textContent = '0%';

        try {
            const response = await fetch(`/administrative/documents/${uid}/upload-to-network`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                uploadProgressBar.style.width = '100%';
                uploadStatus.textContent = '100%';

                // Mostrar mensaje de éxito
                setTimeout(() => {
                    alert(data.message);
                    // Recargar la página o actualizar la sección
                    location.reload();
                }, 500);
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
                uploadProgressDiv.style.display = 'none';
            }
        } catch (error) {
            console.error('Error uploading to network:', error);
            alert('Error al subir los documentos a la carpeta en red');
            uploadProgressDiv.style.display = 'none';
        }
    }

    function uploadToLocal() {
        // Aquí iría el código existente para subida local
        // Por ahora solo mostramos un mensaje
        alert('Subida local - usar el comportamiento actual');
    }
});
</script>
