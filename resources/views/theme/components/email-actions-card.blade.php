{{-- Componente: Tarjeta de Acciones de Email --}}
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Acciones de email</h5>
        <p class="small mb-0 text-muted">Comunicación con el cliente</p>
    </div>
    <div class="card-body">
        @if(($documentConfig['enable_initial_request'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-initial-request'))
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

        @if(($documentConfig['enable_missing_docs'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-missing-docs'))
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

        @if(($documentConfig['enable_reminder'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-reminder'))
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

        @if(($documentConfig['enable_upload_confirmation'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-upload-confirmation'))
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

        @if(($documentConfig['enable_approval'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-approval'))
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

        @if(($documentConfig['enable_rejection'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-rejection'))
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

        @if(($documentConfig['enable_custom_email'] ?? true) && auth()->user()->canActionDocumentComponent('email-actions', 'send-custom'))
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

{{-- MODALES DE EMAIL (Condicionales) --}}
@if($documentConfig['enable_missing_docs'] ?? true)
    @include('documents::documents.documents.components.email.modals.missing-docs')
@endif

@if($documentConfig['enable_initial_request'] ?? true)
    @include('documents::documents.documents.components.email.modals.initial-request')
@endif

@if($documentConfig['enable_reminder'] ?? true)
    @include('documents::documents.documents.components.email.modals.reminder')
@endif

@if($documentConfig['enable_upload_confirmation'] ?? true)
    @include('documents::documents.documents.components.email.modals.upload-confirmation')
@endif

@if($documentConfig['enable_approval'] ?? true)
    @include('documents::documents.documents.components.email.modals.approval')
@endif

@if($documentConfig['enable_rejection'] ?? true)
    @include('documents::documents.documents.components.email.modals.rejection')
@endif

@if($documentConfig['enable_custom_email'] ?? true)
    @include('documents::documents.documents.components.email.modals.custom-email')
@endif
