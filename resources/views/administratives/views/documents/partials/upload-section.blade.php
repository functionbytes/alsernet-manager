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
                                        <a href="{{ $uploadedDocs[$docKey]->getUrl() }}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
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
                        Cargar documentos
                    @endif
                </button>
            </form>

            @if($document->media->count() > 1 && $document->confirmed_at != null)
                <div class="border-top mt-3 pt-3">
                    <a href="{{ route('administrative.documents.summary', $document->uid) }}" target="_blank" class="btn btn-outline-primary w-100">
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
                                <a href="{{ $media->getUrl() }}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
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
                    <a href="{{ route('administrative.documents.summary', $document->uid) }}" target="_blank" class="btn btn-primary w-100">
                       Ver todos los documentos
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
