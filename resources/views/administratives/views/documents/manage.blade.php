@extends('layouts.administratives')

@section('title', 'Gestionar Documento')

@section('content')

    @include('managers.includes.card', ['title' => 'Gestionar Documento'])

    @php
        use App\Models\Document\DocumentType;

        // Obtener tipo de documento desde la base de datos
        $documentType = DocumentType::where('slug', $document->type)->with('requirements')->first();

        // Obtener documentos requeridos con labels (retorna array asociativo)
        $requiredDocuments = $documentType?->getRequiredDocuments() ?? [];

        // Obtener label del tipo de documento
        $documentTypeLabel = $documentType?->getLabel() ?? ucfirst($document->type);

        // Obtener documentos ya cargados organizados por tipo
        $uploadedDocs = [];
        foreach($document->media as $media) {
            $docType = $media->getCustomProperty('document_type', 'documento');
            $uploadedDocs[$docType] = $media;
        }

        // Calcular documentos faltantes
        $missingDocs = array_diff_key($requiredDocuments, $uploadedDocs);
        $allUploaded = empty($missingDocs);
    @endphp

    @include('managers.components.alerts')

    <div class="row">
        <div class="col-lg-4">
            <!-- Email Actions - Sidebar -->
            <div class="card mb-3">
                <div class="card-header p-3 bg-white border-bottom">
                    <h5 class="mb-1 fw-bold">Acciones de email</h5>
                    <p class="small mb-0 text-muted">Comunicación con el cliente</p>
                </div>
                <div class="card-body">
                    @if($documentConfig['enable_initial_request'] ?? true)
                        <!-- Solicitud Inicial -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">
                                Solicitud inicial
                            </label>
                            <p class="text-muted small mb-2">
                                {{ $documentConfig['initial_request_description'] ?? 'Envía un email al cliente solicitándole que cargue los documentos requeridos.' }}
                            </p>
                            <button type="button" class="btn btn-outline-primary w-100 send-notification-btn" data-uid="{{ $document->uid }}">
                                Solicitar carga
                            </button>
                        </div>
                    @endif

                    @if($documentConfig['enable_missing_docs'] ?? true)
                        <!-- Documentos Faltantes -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">
                                Documentos específicos
                            </label>
                            <p class="text-muted small mb-2">
                                {{ $documentConfig['missing_docs_description'] ?? 'Solicita al cliente que reenvíe documentos concretos que falten o necesiten corrección.' }}
                            </p>
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#missingDocsModal">
                                Documentos faltantes
                            </button>
                        </div>
                    @endif

                    @if($documentConfig['enable_reminder'] ?? true)
                        <!-- Recordatorio -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">
                                Recordatorio
                            </label>
                            <p class="text-muted small mb-2">
                                {{ $documentConfig['reminder_description'] ?? 'Envía un recordatorio al cliente si aún no ha completado la carga de documentos.' }}
                            </p>
                            <button type="button" class="btn btn-outline-primary w-100 send-reminder-btn" data-uid="{{ $document->uid }}">
                                Enviar recordatorio
                            </button>
                        </div>
                    @endif


                        @if($documentConfig['enable_upload_confirmation'] ?? true)
                            <hr class="my-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">
                                    Confirmación de subida
                                </label>
                                <p class="text-muted small mb-2">
                                    Confirma al cliente que sus documentos han sido recibidos.
                                </p>
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#uploadConfirmationModal">
                                    Enviar
                                </button>
                            </div>
                        @endif

                        @if($documentConfig['enable_approval'] ?? true)
                            <hr class="my-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">
                                    Notificación de aprobación
                                </label>
                                <p class="text-muted small mb-2">
                                    Notifica al cliente que sus documentos fueron aprobados.
                                </p>
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    Enviar
                                </button>
                            </div>
                        @endif

                        @if($documentConfig['enable_rejection'] ?? true)
                            <hr class="my-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">
                                    Notificación de rechazo
                                </label>
                                <p class="text-muted small mb-2">
                                    Notifica al cliente que sus documentos fueron rechazados.
                                </p>
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#rejectionModal">
                                    Enviar rechazo
                                </button>
                            </div>
                        @endif

                     @if($documentConfig['enable_custom_email'] ?? true)

                        <!-- Correo Personalizado -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">
                                Correo personalizado
                            </label>
                            <p class="text-muted small mb-2">
                                {{ $documentConfig['custom_email_description'] ?? 'Envía un correo con contenido personalizado al cliente.' }}
                            </p>
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#customEmailModal">
                                Redactar correo
                            </button>
                        </div>

                        @endif

                </div>
            </div>

            <!-- Action History -->
            <div id="actionHistoryContainer">
                @include('administratives.views.documents.includes.action-history')
            </div>

            <!-- Document Notes -->
            @include('administratives.views.documents.includes.document-notes-sidebar')

            <!-- Status Timeline -->
            @include('administratives.views.documents.includes.status-timeline')

        </div>

        <div class="col-lg-8">
            @if($products->count())
                <!-- Products List -->
                <div class="card mb-3">
                    <div class="card-header ">
                        <h5 class="mb-1 fw-bold">Listado de productos</h5>
                        <p class="small mb-0 text-muted">Productos relacionados con la orden</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover bg-light mb-0">
                                <tbody>
                                @foreach($products as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white">{{ $item->quantity}} ud</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Details -->
            <div class="card mb-3">
                <div class="card-header p-3 bg-white border-bottom">
                    <h5 class="mb-1 fw-bold">Detalle de la orden</h5>
                    <p class="small mb-0 text-muted">Información de la orden y fechas</p>
                </div>
                <div class="card-body">
                    <form id="formDocuments" enctype="multipart/form-data" role="form" onSubmit="return false">
                        {{ csrf_field() }}
                        <input type="hidden" id="uid" name="uid" value="{{ $document->uid }}">

                        <div class="row g-3">
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Orden</label>
                                <input type="text" class="form-control" value="{{$document->order_id}}" disabled>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Referencia</label>
                                <input type="text" class="form-control" value="{{$document->order_reference}}" disabled>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Tipo</label>
                                <input type="text" class="form-control" value="{{$documentTypeLabel}}" disabled>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Fecha de orden</label>
                                <input type="text" class="form-control" value="{{ $document->order_date ? \Carbon\Carbon::parse($document->order_date)->format('d/m/Y H:i') : '' }}" disabled>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Fecha de confirmación</label>
                                <input type="text" class="form-control" value="{{ $document->confirmed_at ? \Carbon\Carbon::parse($document->confirmed_at)->format('d/m/Y H:i') : '' }}" disabled>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="form-label fw-semibold">Fecha de creación</label>
                                <input type="text" class="form-control" value="{{ $document->created_at ? \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') : '' }}" disabled>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-3">
                <div class="card-header p-3 bg-white border-bottom">
                    <h5 class="mb-1 fw-bold">Información del cliente</h5>
                    <p class="small mb-0 text-muted">Datos de contacto del cliente</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-12 col-md-6">
                            <label class="form-label fw-semibold">Nombres</label>
                            <input type="text" class="form-control" value="{{$document->customer_firstname}}" disabled>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="form-label fw-semibold">Apellidos</label>
                            <input type="text" class="form-control" value="{{$document->customer_lastname}}" disabled>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="form-label fw-semibold">DNI/NIE/CIF</label>
                            <input type="text" class="form-control" value="{{$document->customer_dni}}" disabled>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="form-label fw-semibold">Correo electrónico</label>
                            <input type="text" class="form-control" value="{{$document->customer_email}}" disabled>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" value="{{$document->customer_cellphone}}" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Configuration -->
            <div class="card mb-3">
                <div class="card-header p-3 bg-white border-bottom">
                    <h5 class="mb-1 fw-bold">Gestión del documento</h5>
                    <p class="small mb-0 text-muted">Indica si el documento fue procesado y cómo fue recibido</p>
                </div>
                <div class="card-body">
                    <form id="formDocumentConfig" enctype="multipart/form-data" role="form" onSubmit="return false">
                        {{ csrf_field() }}
                        <input type="hidden" id="uid_config" name="uid" value="{{ $document->uid }}">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Estado del documento</label>
                                <select class="form-select select2" id="status_id" name="status_id">
                                    <option value="">Selecciona un estado</option>
                                    @forelse($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $document->status_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->label }}
                                        </option>
                                    @empty
                                        <option disabled>No hay estados disponibles</option>
                                    @endforelse
                                </select>
                                <label id="status_id-error" class="error" for="status_id" style="display: none"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Origen (Canal)</label>
                                <select class="form-select select2" id="document_source_id" name="document_source_id">
                                    <option value="">Sin especificar</option>
                                    @forelse($documentSources as $source)
                                        <option value="{{ $source->id }}" {{ $document->document_source_id == $source->id ? 'selected' : '' }}>
                                            {{ $source->label }} - {{ $source->description }}
                                        </option>
                                    @empty
                                        <option disabled>No hay orígenes disponibles</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tipo de Carga</label>
                                <select class="form-select" id="upload_type" name="upload_type">
                                    <option value="automatic" {{ $document->upload_type == 'automatic' ? 'selected' : '' }}>
                                       Automático (Cliente/Sistema)
                                    </option>
                                    <option value="manual" {{ $document->upload_type == 'manual' ? 'selected' : '' }}>
                                       Manual (Administrador)
                                    </option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Guardar configuración
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Section - Ocultar si ya está gestionado y tiene documentos -->
            <div id="uploadSectionContainer">
                @include('administratives.views.documents.partials.upload-section', [
                    'document' => $document,
                    'requiredDocuments' => $requiredDocuments,
                    'uploadedDocs' => $uploadedDocs,
                    'missingDocs' => $missingDocs,
                    'allUploaded' => $allUploaded,
                ])
            </div>


        </div>

    </div>

    <!-- Modal para confirmar upload con documentos faltantes -->
    <div class="modal fade" id="confirmMissingDocumentsModal" tabindex="-1" role="dialog" aria-labelledby="confirmMissingDocumentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmMissingDocumentsModalLabel">
                        Documentos faltantes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <strong>Aún faltarán por cargar los siguientes documentos:</strong>
                    </p>
                    <ul id="missingDocsList" class="list-unstyled ms-3">
                        <!-- Se rellena dinámicamente con JavaScript -->
                    </ul>
                    <p class="mt-3 mb-0 text-muted">
                        <small>¿Deseas continuar con la carga de todas formas?</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="confirmUploadBtn">
                        Sí, continuar
                    </button>
                    <button type="button" class="btn btn-secondary w-100 " data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de documento -->
    <div class="modal fade" id="confirmDeleteDocumentModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title " id="confirmDeleteDocumentModalLabel">
                        Confirmar eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        <strong>¿Estás seguro de que deseas eliminar este documento?</strong>
                    </p>
                    <p class="text-muted small mt-2 mb-0">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="confirmDeleteBtn">
                        Eliminar
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Documentos Faltantes -->
    <div class="modal fade" id="missingDocsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">Solicitar documentos faltantes</h5>
                        <p class="text-muted small mb-0">Selecciona los documentos a solicitar</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(count($missingDocs) > 0)
                        <p class="text-muted small mb-3">Los siguientes documentos están pendientes de carga:</p>
                        <form id="missingDocsForm">
                            <div class="border rounded p-3 bg-light-secondary mb-3">
                                @foreach($missingDocs as $key => $label)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="missing_docs[]" value="{{ $key }}" id="missing_{{ $key }}" checked>
                                        <label class="form-check-label fw-semibold" for="missing_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-0">
                                <label for="additional_notes" class="form-label fw-semibold">Notas adicionales (opcional)</label>
                                <textarea class="form-control" id="additional_notes" name="notes" rows="3" placeholder="Ej: La foto del DNI está borrosa..."></textarea>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fs-4 me-2"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">Todos los documentos están cargados</h6>
                                    <p class="mb-0 small">No hay documentos faltantes para este tipo de solicitud.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top">

                    @if(count($missingDocs) > 0)
                        <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendMissingDocs">
                            Enviar solicitud
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Correo Personalizado -->
    <div class="modal fade" id="customEmailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            Redactar correo personalizado
                        </h5>
                        <p class="text-muted small mb-0">Envía un mensaje personalizado al cliente</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($customEmailTemplate)
                        <!-- Información de la plantilla configurada -->
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-2 mt-1"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Plantilla configurada</h6>
                                    <p class="mb-0 small">
                                        Se usará la plantilla: <span class="badge bg-primary">{{ $customEmailTemplate->name }}</span>
                                    </p>
                                    @if($customEmailTemplate->description)
                                        <p class="mb-0 small mt-1">{{ $customEmailTemplate->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Sin plantilla configurada -->
                        <div class="alert alert-warning mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Sin plantilla configurada</h6>
                                    <p class="mb-0 small">
                                        No hay plantilla de correo personalizado configurada. <a href="{{ route('managers.settings.documents.global') }}" class="text-decoration-none">Configura una plantilla aquí</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form id="customEmailForm">
                        <!-- Asunto -->
                        <div class="mb-3">
                            <label for="email_subject" class="form-label fw-semibold">Asunto</label>
                            <input type="text" class="form-control" id="email_subject" name="subject" placeholder="Ej: Información sobre tu orden {ORDER_REFERENCE}" required>
                        </div>

                        <!-- Variables disponibles Panel (similar a template editor) -->
                        <div class="border-top p-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
                                <div>
                                    <h6 class="mb-1 fw-semibold text-dark">
                                        Variables disponibles
                                    </h6>
                                    <small class="text-muted d-block">
                                        Haz clic en cualquier variable para insertarla en el editor
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="btnLoadCustomEmailVariables" data-bs-toggle="tooltip" title="Recargar variables">
                                    <i class="fas fa-sync-alt me-1"></i>
                                </button>
                            </div>
                            <div id="customEmailVariablesPanel" style="max-height: 350px; overflow-y: auto;">
                                <!-- Variables se cargarán aquí -->
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="mb-3">
                            <label for="email_content" class="form-label fw-semibold">Contenido del correo</label>
                            <textarea class="form-control" id="email_content" name="content" rows="6" placeholder="Escribe el contenido personalizado&#10;Ej: Hola {CUSTOMER_NAME}, queremos informarte que tu orden {ORDER_ID} ha sido procesada..." required></textarea>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> Las variables entre llaves se reemplazarán con los datos reales del cliente
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendCustomEmail">
                        Enviar correo
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Solicitud Inicial de Documentos -->
    <div class="modal fade" id="requestUploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            Solicitud documentación
                        </h5>
                        <p class="text-muted small mb-0">Solicita al cliente que cargue los documentos requeridos</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-circle-info me-2"></i>
                            <div>
                                <p class="mb-0 small">Se enviará un email solicitando que se carguen todos los documentos requeridos.</p>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendRequestUpload">
                        Enviar solicitud
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Recordatorio -->
    <div class="modal fade" id="reminderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            Enviar recordatorio
                        </h5>
                        <p class="text-muted small mb-0">Recordar al cliente que cargue los documentos faltantes</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert bg-light" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <p class="mb-0 small">Se enviará un email de recordatorio al cliente para que complete la carga de documentos.</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendReminder">
                        Enviar recordatorio
                    </button>
                    <button type="button" class="btn btn-secondary w-100"  data-bs-dismiss="modal">Cancelar</button>

                </div>
            </div>
        </div>
    </div>

    <!-- Upload Confirmation Modal -->
    <div class="modal fade" id="uploadConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Confirmación de subida
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>Se enviará un email confirmando que los documentos han sido recibidos correctamente.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendUploadConfirmation">
                        Enviar
                    </button>
                    <button type="button" class="btn btn-secondary  w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title">
                            Notificación de aprobación
                        </h5>
                        <p class="text-muted small mb-0">Notifica al cliente que sus documentos fueron aprobados.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>Se enviará un email notificando que los documentos han sido aprobados.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100" id="btnSendApproval">
                        Enviar
                    </button>
                    <button type="button" class="btn btn-secondary  w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title">
                            Notificación de rechazo
                        </h5>
                        <p class="text-muted small mb-0">Notifica al cliente que sus documentos fueron rechazados.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>Se enviará un email notificando que los documentos han sido rechazados.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Documentos rechazados</label>
                        <p class="text-muted small mb-2">Selecciona los documentos que necesitan ser reenviados:</p>
                        <div class="border rounded p-3 bg-light-secondary">
                            @foreach($requiredDocuments as $docKey => $docLabel)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="rejected_docs[]" value="{{ $docKey }}" id="rejected_{{ $docKey }}" checked>
                                    <label class="form-check-label fw-semibold" for="rejected_{{ $docKey }}">
                                        {{ $docLabel }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Razón del rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="4" required
                                  placeholder="Explica por qué los documentos fueron rechazados..."></textarea>
                        <small class="text-muted">Este campo es obligatorio.</small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100" id="btnSendRejection">
                        Enviar rechazo
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Document Configuration Modal -->
    <div class="modal fade" id="confirmConfigurationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        <i class="fas fa-save text-primary me-2"></i>
                        Guardar configuración
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>¿Estás seguro de que deseas guardar la configuración del documento?</div>
                    </div>
                    <p class="text-muted mb-0">
                        Se actualizarán el estado, origen y tipo de carga del documento.
                    </p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="confirmConfigBtn">
                        Guardar
                    </button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    </div>

@endsection

@push('styles')
<style>
    /* Variable Cards Styling */
    .variable-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        margin-bottom: 8px;
    }

    .variable-card:hover {
        background: linear-gradient(135deg, #90bb13 0%, #7a9f10 100%);
        border-color: #90bb13;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(144, 187, 19, 0.15);
    }

    .variable-card:hover .variable-code {
        color: #ffffff;
    }

    .variable-code {
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        text-align: center;
        word-break: break-word;
        transition: color 0.2s ease;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const documentUid = '{{ $document->uid }}';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ===== Enviar Solicitud de Documentos Faltantes (Directo) =====
            $('#btnSendMissingDocs').on('click', function() {
                const $btn = $(this);
                const $form = $('#missingDocsForm');
                const selectedDocs = [];

                $form.find('input[name="missing_docs[]"]:checked').each(function() {
                    selectedDocs.push($(this).val());
                });

                if (selectedDocs.length === 0) {
                    toastr.warning('Selecciona al menos un documento faltante', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    return;
                }

                // Deshabilitar botón y mostrar estado de carga
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');

                // Enviar directamente
                $.ajax({
                    url: "{{ route('administrative.documents.send-missing', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: {
                        missing_docs: selectedDocs,
                        notes: $('#additional_notes').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#missingDocsModal').modal('hide');

                            // Limpiar checkboxes
                            $form.find('input[name="missing_docs[]"]:checked').prop('checked', false);

                            // Limpiar textarea de notas
                            $('#additional_notes').val('');

                            toastr.success('Email enviado correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });

                            // Recargar historial de acciones
                            reloadActionHistory();
                        } else {
                            toastr.error(response.message || 'No se pudo enviar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-send"></i> Documentos faltantes');
                    }
                });
            });

            // ===== Enviar Email de Notificación (Modal) =====
            $(document).on('click', '.send-notification-btn', function(e) {
                e.preventDefault();
                $('#requestUploadModal').modal('show');
            });

            // ===== Enviar Solicitud Inicial =====
            $(document).on('click', '#btnSendRequestUpload', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('Enviando...');

                $.ajax({
                    url: "{{ route('administrative.documents.send-notification', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            $('#requestUploadModal').modal('hide');
                            toastr.success('Email enviado a: ' + (response.recipient || 'cliente'), 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Recargar historial de acciones
                            reloadActionHistory();
                        } else {
                            toastr.error(response.message || 'No se pudo enviar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('Enviar solicitud');
                    }
                });
            });

            // ===== Enviar Email de Recordatorio (Modal) =====
            $(document).on('click', '.send-reminder-btn', function(e) {
                e.preventDefault();
                $('#reminderModal').modal('show');
            });

            // ===== Enviar Recordatorio desde Modal =====
            $(document).on('click', '#btnSendReminder', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');

                $.ajax({
                    url: "{{ route('administrative.documents.send-reminder', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            $('#reminderModal').modal('hide');
                            toastr.success('Email enviado a: ' + (response.recipient || 'cliente'), 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Recargar historial de acciones
                            reloadActionHistory();
                        } else {
                            toastr.error(response.message || 'No se pudo enviar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-bell"></i> Enviar Recordatorio');
                    }
                });
            });

            // ===== Cargar Documentos Múltiples =====
            $(document).on('submit', '#adminUploadForm', function(e) {
                e.preventDefault();

                const $form = $(this);
                const formData = new FormData(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const $progressBar = $('#uploadProgress');
                const $uploadStatus = $('#uploadStatus');

                // Verificar que al menos un archivo esté seleccionado
                let hasFiles = false;
                const uploadedByType = {};
                const filesBeingUploaded = [];

                $('.document-file-input').each(function() {
                    const $input = $(this);
                    const docType = $input.data('doc-type');

                    if ($input[0].files && $input[0].files.length > 0) {
                        hasFiles = true;
                        uploadedByType[docType] = true;

                        const $item = $input.closest('.document-upload-item');
                        let docLabel = $item.find('.form-label').text().trim();

                        // Validar que se encontró el label
                        if (!docLabel) {
                            docLabel = `Documento (${docType})`;
                        }

                        filesBeingUploaded.push({
                            type: docType,
                            label: docLabel
                        });
                    }
                });

                if (!hasFiles) {
                    toastr.warning('Por favor selecciona al menos un documento', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    return;
                }

                // Validar que lo que se está cargando existe en los requeridos
                let allFilesValid = true;
                filesBeingUploaded.forEach(file => {
                    const $item = $(`.document-upload-item[data-doc-type="${file.type}"]`);
                    if ($item.length === 0) {
                        toastr.error(`Documento tipo "${file.label}" no es válido`, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        allFilesValid = false;
                    }
                });

                if (!allFilesValid) {
                    return;
                }

                // Advertir si aún hay documentos faltantes después de esta carga
                const missingAfterUpload = [];
                $('.document-upload-item').each(function() {
                    const docType = $(this).data('doc-type');
                    const isAlreadyUploaded = $(this).find('.uploaded-doc-info').length > 0;

                    // Verificar si hay archivo seleccionado en el input (búsqueda más directa)
                    const $fileInput = $(this).find('input.document-file-input');
                    const hasFileSelected = $fileInput.length > 0 && $fileInput[0].files && $fileInput[0].files.length > 0;

                    // También verificar en uploadedByType por compatibilidad
                    const isBeingUploaded = uploadedByType[docType] || hasFileSelected;

                    if (!isAlreadyUploaded && !isBeingUploaded) {
                        const docLabel = $(this).find('.form-label').text().trim();
                        missingAfterUpload.push({
                            type: docType,
                            label: docLabel
                        });
                    }
                });

                if (missingAfterUpload.length > 0) {
                    // Mostrar modal con advertencia clara
                    const missingHtml = missingAfterUpload.map(doc => `<li><strong>${doc.label}</strong></li>`).join('');
                    const uploadingHtml = filesBeingUploaded.map(file => `<li class="text-success"><strong>${file.label}</strong></li>`).join('');

                    let modalBody = `
                        <div class="mb-3">
                            <h6 class="text-success mb-2">Estás cargando:</h6>
                            <ul class="list-unstyled ms-3">
                                ${uploadingHtml}
                            </ul>
                        </div>
                        <div>
                            <h6 class="text-success mb-2">Aún faltarán después de esta carga:</h6>
                            <ul class="list-unstyled ms-3">
                                ${missingHtml}
                            </ul>
                        </div>
                    `;

                    $('#missingDocsList').html(modalBody);
                    $('#confirmMissingDocumentsModal').modal('show');

                    // Guardar formData en variable global para usarla cuando confirme
                    window.pendingFormData = formData;
                    window.pendingUpload = true;
                    return;
                }

                // Si no hay documentos faltantes, proceder directamente
                performUpload($submitBtn, formData, $progressBar, $uploadStatus);
            });

            // ===== Eliminar Documento Individual =====
            $(document).on('click', '.btn-delete-single-doc', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const mediaId = $btn.data('media-id');
                const docType = $btn.data('doc-type');

                // Guardar datos en window global para usar en el modal
                window.pendingDelete = {
                    btn: $btn,
                    mediaId: mediaId,
                    docType: docType
                };

                // Mostrar modal
                $('#confirmDeleteDocumentModal').modal('show');
            });

            // ===== Handler para confirmar eliminación desde el modal =====
            $(document).on('click', '#confirmDeleteBtn', function() {
                if (!window.pendingDelete) {
                    return;
                }

                const { btn: $btn, mediaId, docType } = window.pendingDelete;

                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i>');

                $('#confirmDeleteDocumentModal').modal('hide');

                $.ajax({
                    url: "{{ route('administrative.documents.delete-single', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: {
                        media_id: mediaId,
                        doc_type: docType
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Documento eliminado correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Actualizar estado sin recargar la página
                            updateDocumentState(documentUid);
                        } else {
                            toastr.error(response.message || 'No se pudo eliminar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-trash-alt"></i>');
                        window.pendingDelete = null;
                    }
                });
            });

            // ===== Confirmar Carga =====
            $(document).on('click', '.confirm-upload-btn', function(e) {
                e.preventDefault();

                if (!confirm('¿Confirmar carga del documento?')) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('<i class="ti ti-loader-2 spin"></i> Confirmando...');

                $.ajax({
                    url: "{{ route('administrative.documents.confirm-upload', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Carga confirmada correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(response.message || 'No se pudo confirmar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="ti ti-check"></i> Confirmar carga');
                    }
                });
            });

            // ===== Guardar Configuración (Estado y Origen) =====
            let configFormData = null;
            let $configSubmitBtn = null;

            $(document).on('submit', '#formDocumentConfig', function(e) {
                e.preventDefault();

                const $form = $(this);
                configFormData = {
                    proccess: $('#proccess').val(),
                    source: $('#source').val(),
                    status_id: $('#status_id').val(),
                    document_source_id: $('#document_source_id').val(),
                    upload_type: $('#upload_type').val()
                };
                $configSubmitBtn = $form.find('button[type="submit"]');

                // Open confirmation modal
                const modal = new bootstrap.Modal(document.getElementById('confirmConfigurationModal'));
                modal.show();
            });

            // Handle confirmation button click
            $('#confirmConfigBtn').on('click', function() {
                if (!configFormData || !$configSubmitBtn) return;

                $configSubmitBtn.prop('disabled', true);
                $configSubmitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmConfigurationModal'));
                modal.hide();

                $.ajax({
                    url: "{{ route('administrative.documents.update', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: configFormData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Configuración guardada correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(response.message || 'No se pudo guardar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al procesar la solicitud';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        toastr.error(errorMsg, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $configSubmitBtn.prop('disabled', false);
                        $configSubmitBtn.html('<i class="fas fa-save me-1"></i> Guardar configuración');
                    }
                });
            });

            // ===== Abrir Modal de Redacción de Correo Personalizado =====
            // ===== Enviar Correo Personalizado (Directo) =====
            $(document).on('click', '#btnSendCustomEmail', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const subject = $('#email_subject').val().trim();
                const content = $('#email_content').val().trim();

                if (!subject) {
                    toastr.warning('Por favor ingresa un asunto', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    return;
                }

                if (!content) {
                    toastr.warning('Por favor ingresa el contenido del correo', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    return;
                }

                // Deshabilitar botón y mostrar estado de carga
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');

                // Enviar directamente
                console.log('Enviando correo personalizado:', { subject, content });
                $.ajax({
                    url: "{{ route('administrative.documents.send-custom-email', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        subject: subject,
                        content: content
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#customEmailModal').modal('hide');
                            $('#customEmailForm')[0].reset();
                            toastr.success('Correo enviado correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Recargar historial de acciones
                            reloadActionHistory();
                        } else {
                            toastr.error(response.message || 'No se pudo enviar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al procesar la solicitud';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        toastr.error(errorMsg, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('Enviar correo');
                    }
                });
            });

            // ===== Actualizar Estado del Documento Dinámicamente =====
            /**
             * Recarga completamente la sección de carga de documentos vía AJAX
             * Reemplaza todo el HTML de la sección para evitar errores de sincronización
             */
            function reloadDocumentsSection(uid = documentUid) {
                console.log('[reloadDocumentsSection] Iniciando recarga para uid:', uid);

                $.ajax({
                    url: "{{ route('administrative.documents.refresh-section', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', uid),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('[reloadDocumentsSection] Response:', response);

                        if (response.success && response.html) {
                            console.log('[reloadDocumentsSection] Reemplazando HTML en #uploadSectionContainer');

                            const $container = $('#uploadSectionContainer');
                            console.log('[DEBUG] Contenedor encontrado:', $container.length > 0);
                            console.log('[DEBUG] Contenedor display:', $container.css('display'));
                            console.log('[DEBUG] Contenedor visibility:', $container.css('visibility'));
                            console.log('[DEBUG] Contenedor html antes:', $container.html().substring(0, 50) + '...');

                            // Reemplazar completamente el contenedor con el nuevo HTML
                            $container.html(response.html);

                            console.log('[DEBUG] Contenedor html después:', $container.html().substring(0, 50) + '...');
                            console.log('[DEBUG] Nueva altura del contenedor:', $container.height());
                            console.log('[reloadDocumentsSection] HTML reemplazado, estado actual:', $container.html().substring(0, 100));

                            // Asegurar que los event handlers estén activados (usan event delegation, así que no es necesario)
                            console.log('[reloadDocumentsSection] Recarga completada');
                        } else {
                            console.error('[reloadDocumentsSection] Respuesta sin success o html:', response);
                            toastr.error('No se pudo actualizar la sección', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('[reloadDocumentsSection] Error AJAX:', xhr);
                        let errorMsg = 'Error al refrescar la sección de documentos';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMsg = 'La ruta de actualización no existe (404)';
                        }

                        toastr.error(errorMsg, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                });
            }

            /**
             * DEPRECATED: Mantener por compatibilidad, usar reloadDocumentsSection()
             */
            function updateDocumentState(uid = documentUid) {
                reloadDocumentsSection(uid);
            }

            /**
             * Recarga el historial de acciones
             */
            function reloadActionHistory(uid = documentUid) {
                console.log('[reloadActionHistory] Iniciando recarga para uid:', uid);

                $.ajax({
                    url: "{{ route('administrative.documents.refresh-action-history', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', uid),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('[reloadActionHistory] Response:', response);

                        if (response.success && response.html) {
                            console.log('[reloadActionHistory] Reemplazando HTML en #actionHistoryContainer');

                            const $container = $('#actionHistoryContainer');
                            $container.html(response.html);

                            console.log('[reloadActionHistory] Recarga completada');
                        } else {
                            console.error('[reloadActionHistory] Respuesta sin success o html:', response);
                        }
                    },
                    error: function(xhr) {
                        console.error('[reloadActionHistory] Error AJAX:', xhr);
                        let errorMsg = 'Error al refrescar el historial de acciones';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        console.error(errorMsg);
                    }
                });
            }

            /**
             * Re-inicializar handlers de upload (event delegation ya está en lugar)
             * Esta función es un stub para compatibilidad futura
             */
            function initializeUploadHandlers() {
                // Los handlers de upload usan $(document).on() así que ya funcionan con elementos dinámicos
                // Esta función es un placeholder para compatibilidad futura si es necesario
            }

            /**
             * Re-inicializar handlers de delete (event delegation ya está en lugar)
             * Esta función es un stub para compatibilidad futura
             */
            function initializeDeleteHandlers() {
                // Los handlers de delete usan $(document).on() así que ya funcionan con elementos dinámicos
                // Esta función es un placeholder para compatibilidad futura si es necesario
            }

            /**
             * Actualiza los elementos visuales de documentos ya cargados
             */
            function updateUploadedDocumentsUI(response) {
                // Nueva estructura simplificada: uploaded_documents es un array de keys ["doc_1", "doc_2"]
                // Los detalles completos están en uploaded_documents_details
                const uploadedKeys = response.uploaded_documents || [];
                const uploadedDetails = response.uploaded_documents_details || {};
                const uploadedDocs = uploadedKeys.reduce((acc, key) => {
                    acc[key] = uploadedDetails[key];
                    return acc;
                }, {});

                // Actualizar contador principal
                const totalDocs = Object.keys(response.stats?.total_required || {}).length || $('.document-upload-item').length;
                const uploadedCount = uploadedKeys.length;
                $('#documentCounter').text(uploadedCount + '/' + totalDocs + ' cargados');

                // Iterar sobre cada item de documento
                $('.document-upload-item').each(function() {
                    const docType = $(this).data('doc-type');
                    const $badge = $(this).find('.badge');
                    const $input = $(this).find('input[type="file"]');
                    const $infoDiv = $(this).find('.uploaded-doc-info');

                    if (uploadedDocs[docType]) {
                        const docInfo = uploadedDocs[docType];

                        // Cambiar badge a "Cargado"
                        $badge.removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success');
                        $badge.html('<i class="fa fa-check-circle"></i> Cargado');

                        // Si no existe la div de info, crearla
                        if ($infoDiv.length === 0) {
                            const infoDivHtml = `
                                <div class="uploaded-doc-info mt-2 p-3 bg-light border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark small">
                                                <i class="fa fa-file-pdf text-danger"></i> ${escapeHtml(docInfo.file_name)}
                                            </p>
                                            <small class="text-muted d-block mt-1">
                                                ${formatBytes(docInfo.size)} • ${docInfo.created_at}
                                            </small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="${docInfo.url}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-media-id="${docInfo.id}" data-doc-type="${docType}" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $(this).append(infoDivHtml);
                        } else {
                            // Actualizar contenido existente
                            $infoDiv.find('.fw-semibold').html(`<i class="fa fa-file-pdf text-danger"></i> ${escapeHtml(docInfo.file_name)}`);
                            $infoDiv.find('small.text-muted').html(`${formatBytes(docInfo.size)} • ${docInfo.created_at}`);
                        }

                        // Ocultar input si existe
                        if ($input.length) {
                            $input.hide();
                        }
                    } else {
                        // Documento NO cargado
                        $badge.removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger');
                        $badge.html('<i class="fa fa-clock"></i> Pendiente');

                        // Eliminar div de info si existe
                        if ($infoDiv.length) {
                            $infoDiv.remove();
                        }

                        // Si no existe input, recrearlo
                        if ($input.length === 0) {
                            const inputHtml = `
                                <input
                                    type="file"
                                    class="form-control document-file-input"
                                    name="documents[${docType}]"
                                    data-doc-type="${docType}"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                >
                                <small class="text-muted d-block mt-1">
                                    <i class="fa fa-info-circle"></i> PDF, JPG, PNG, DOC (máximo 10MB)
                                </small>
                            `;
                            $(this).append(inputHtml);
                        } else {
                            // Si existe, asegurarse de que esté visible
                            $input.show();
                        }
                    }
                });
            }

            /**
             * Actualiza la lista de documentos faltantes
             */
            function updateMissingDocumentsUI(response) {
                const missingDocs = response.missing_documents || {};
                const totalRequired = response.stats?.total_required || 0;
                const totalMissing = response.stats?.total_missing || 0;

                // Actualizar contador de documentos faltantes en el modal
                const $missingBadge = $('.missing-count-badge');
                if ($missingBadge.length) {
                    if (totalMissing > 0) {
                        $missingBadge.removeClass('d-none').text(totalMissing);
                    } else {
                        $missingBadge.addClass('d-none');
                    }
                }

                // Actualizar lista de documentos faltantes si existe
                const $missingList = $('#missingDocsList');
                if ($missingList.length) {
                    $missingList.empty();
                    if (Object.keys(missingDocs).length > 0) {
                        Object.entries(missingDocs).forEach(([docType, docLabel]) => {
                            $missingList.append(`
                                <li class="list-group-item">
                                    <i class="fa fa-warning text-warning"></i> ${escapeHtml(docLabel)}
                                </li>
                            `);
                        });
                    } else {
                        $missingList.html('<li class="list-group-item text-success">Todos los documentos están cargados</li>');
                    }
                }

                // Si está completo, mostrar mensaje de éxito
                if (response.all_uploaded) {
                    toastr.success('¡Todos los documentos han sido cargados correctamente!', 'Completado', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                }
            }

            /**
             * Función auxiliar para formatear bytes a tamaño legible
             */
            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            // Llamar a updateDocumentState después de cargar documentos exitosamente
            // Actualiza la función existente en el success handler del upload
            $(document).on('ajax-upload-success', function() {
                updateDocumentState();
            });

            // También actualizar cuando se elimina un documento
            $(document).on('document-deleted', function() {
                updateDocumentState();
            });

            /**
             * Realiza el upload de documentos via AJAX
             */
            function performUpload($submitBtn, formData, $progressBar, $uploadStatus) {
                $submitBtn.prop('disabled', true);
                $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
                $progressBar.show();
                $uploadStatus.text('Cargando documentos...');

                $.ajax({
                    url: "{{ route('administrative.documents.admin-upload', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                $('#uploadProgressBar').css('width', percentComplete + '%');
                                $uploadStatus.text('Cargando... ' + Math.round(percentComplete) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            const uploadedCount = response.uploaded_count || 1;
                            toastr.success('Se cargaron ' + uploadedCount + ' documento(s) correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Actualizar estado del documento sin recargar la página
                            updateDocumentState(documentUid);
                        } else {
                            toastr.error(response.message || 'No se pudo cargar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al procesar la solicitud';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        toastr.error(errorMsg, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html('Cargar documentos');
                        $progressBar.hide();
                    }
                });
            }

            /**
             * Handler para confirmar upload desde el modal
             */
            $(document).on('click', '#confirmUploadBtn', function() {
                $('#confirmMissingDocumentsModal').modal('hide');
                performUpload(
                    $('#adminUploadForm').find('button[type="submit"]'),
                    window.pendingFormData,
                    $('#uploadProgress'),
                    $('#uploadStatus')
                );
            });

            // ===== Renderizar Variables Panel en Modal de Correo Personalizado =====
            function renderCustomEmailVariables() {
                const variables = [
                    { name: 'CUSTOMER_NAME', desc: 'Nombre del cliente' },
                    { name: 'CUSTOMER_EMAIL', desc: 'Email del cliente' },
                    { name: 'ORDER_ID', desc: 'ID de la orden' },
                    { name: 'ORDER_REFERENCE', desc: 'Referencia de la orden' },
                    { name: 'DOCUMENT_TYPE', desc: 'Tipo de documento' },
                    { name: 'DOCUMENT_TYPE_LABEL', desc: 'Etiqueta del documento' },
                    { name: 'UPLOAD_LINK', desc: 'Enlace de carga' },
                    { name: 'EXPIRATION_DATE', desc: 'Fecha de expiración' },
                    { name: 'COMPANY_NAME', desc: 'Nombre de la empresa' },
                    { name: 'SUPPORT_EMAIL', desc: 'Email de soporte' },
                    { name: 'SUPPORT_PHONE', desc: 'Teléfono de soporte' }
                ];

                let html = '<div class="row g-1 px-2">';
                $.each(variables, function(idx, variable) {
                    html += '<div class="col-6 col-md-4">';
                    html += '<div class="variable-card variable-insert" data-variable-name="' + variable.name + '" data-bs-toggle="tooltip" title="' + variable.desc + '">';
                    html += '<code class="variable-code">{' + variable.name + '}</code>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';

                $('#customEmailVariablesPanel').html(html);

                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this);
                });

                // Track last cursor position for custom email fields
                let lastFocusedField = null;
                let lastCursorPosition = 0;

                $('#email_content, #email_subject').on('focus click keyup', function() {
                    lastFocusedField = this;
                    lastCursorPosition = this.selectionStart;
                });

                // Add click handlers
                $(document).off('click', '.variable-insert').on('click', '.variable-insert', function(e) {
                    e.preventDefault();
                    const variableName = $(this).data('variable-name');
                    const variable = '{' + variableName + '}';

                    // Determine which field to insert into
                    let $targetField = null;

                    // First check if a field is currently focused
                    if ($('#email_content').is(':focus')) {
                        $targetField = $('#email_content');
                    } else if ($('#email_subject').is(':focus')) {
                        $targetField = $('#email_subject');
                    }
                    // Then check the last focused field
                    else if (lastFocusedField) {
                        $targetField = $(lastFocusedField);
                    }
                    // Default to email_content
                    else {
                        $targetField = $('#email_content');
                    }

                    if ($targetField && $targetField.length > 0) {
                        const field = $targetField[0];
                        const curPos = field.selectionStart || lastCursorPosition || field.value.length;
                        const textBefore = $targetField.val().substring(0, curPos);
                        const textAfter = $targetField.val().substring(curPos);

                        $targetField.val(textBefore + variable + textAfter);

                        // Set cursor position after the inserted variable
                        const newPos = curPos + variable.length;
                        field.selectionStart = field.selectionEnd = newPos;
                        lastCursorPosition = newPos;

                        // Focus the field
                        $targetField.focus();

                        toastr.success('Variable insertada: ' + variable, 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 1500
                        });
                    }
                });
            }

            // Cargar variables cuando se abre el modal
            $('#customEmailModal').on('show.bs.modal', function() {
                renderCustomEmailVariables();
            });

            // Handler para botón de recargar variables
            $(document).on('click', '#btnLoadCustomEmailVariables', function() {
                const $btn = $(this);
                $btn.prop('disabled', true).find('i').addClass('fa-spin');

                // Simular carga y renderizar
                setTimeout(function() {
                    renderCustomEmailVariables();
                    $btn.prop('disabled', false).find('i').removeClass('fa-spin');
                }, 300);
            });

            // ===== Upload Confirmation Handler =====
            $('#btnSendUploadConfirmation').on('click', function() {
                const $btn = $(this);
                const notes = $('#uploadConfirmationNotes').val();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');

                $.ajax({
                    url: "{{ route('administrative.documents.send-upload-confirmation', $document->uid) }}",
                    method: 'POST',
                    data: { notes: notes },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Email enviado correctamente a: ' + response.recipient, 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            $('#uploadConfirmationModal').modal('hide');
                            $('#uploadConfirmationNotes').val('');
                            // Recargar historial de acciones
                            reloadActionHistory();
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Error al enviar el email';
                        toastr.error(message, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Enviar');
                    }
                });
            });

            // ===== Approval Handler =====
            $('#btnSendApproval').on('click', function() {
                const $btn = $(this);

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');

                $.ajax({
                    url: "{{ route('administrative.documents.send-approval', $document->uid) }}",
                    method: 'POST',
                    data: {},
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Email de aprobación enviado a: ' + response.recipient, 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            $('#approvalModal').modal('hide');
                            // Recargar historial de acciones
                            reloadActionHistory();
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Error al enviar el email';
                        toastr.error(message, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Enviar');
                    }
                });
            });

            // ===== Rejection Handler =====
            $('#btnSendRejection').on('click', function() {
                const $btn = $(this);
                const reason = $('#rejectionReason').val().trim();

                if (!reason) {
                    toastr.warning('Debes especificar la razón del rechazo', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    $('#rejectionReason').focus();
                    return;
                }

                // Recoger documentos rechazados seleccionados
                const rejectedDocs = [];
                $('input[name="rejected_docs[]"]:checked').each(function() {
                    rejectedDocs.push($(this).val());
                });

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');

                $.ajax({
                    url: "{{ route('administrative.documents.send-rejection', $document->uid) }}",
                    method: 'POST',
                    data: {
                        reason: reason,
                        rejected_docs: rejectedDocs
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Email de rechazo enviado a: ' + response.recipient, 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            $('#rejectionModal').modal('hide');

                            // Limpiar textarea de razón
                            $('#rejectionReason').val('');

                            // Limpiar checkboxes (desmarcar todos)
                            $('input[name="rejected_docs[]"]').prop('checked', false);

                            // Recargar historial de acciones
                            reloadActionHistory();
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Error al enviar el email';
                        toastr.error(message, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Enviar');
                    }
                });
            });

        });
    </script>

    <style>
        /* Spinner animation for loading states */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        /* Stat card hover effect */
        .stat-card {
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

    </style>
@endpush
