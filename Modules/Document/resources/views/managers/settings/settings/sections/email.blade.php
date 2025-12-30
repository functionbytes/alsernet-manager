{{-- Email Notification Settings Section --}}
<div class="email-settings">

    <!-- Section Header -->
    <div class="mb-4">
        <h5 class="fw-bold mb-2">
            <i class="ti ti-mail text-primary me-2"></i>
            Notificaciones por Email
        </h5>
        <p class="text-muted mb-0">Configura las plantillas y comportamiento de los correos electronicos enviados durante el proceso de gestion de documentos.</p>
    </div>

    <!-- Template Variables Reference -->
    <div class="alert alert-light border mb-4">
        <div class="d-flex align-items-start">
            <i class="ti ti-code text-primary me-3 fs-5"></i>
            <div class="flex-grow-1">
                <h6 class="fw-semibold mb-2">Variables Disponibles para Plantillas</h6>
                <p class="text-muted small mb-2">Usa estas variables en los mensajes para personalizar el contenido:</p>
                <div class="row g-2">
                    <div class="col-md-4">
                        <code class="small">@{{customer_name}}</code> - Nombre del cliente<br>
                        <code class="small">@{{customer_email}}</code> - Email del cliente<br>
                        <code class="small">@{{order_id}}</code> - ID de la orden
                    </div>
                    <div class="col-md-4">
                        <code class="small">@{{document_name}}</code> - Nombre del documento<br>
                        <code class="small">@{{document_type}}</code> - Tipo de documento<br>
                        <code class="small">@{{upload_link}}</code> - Enlace de carga
                    </div>
                    <div class="col-md-4">
                        <code class="small">@{{due_date}}</code> - Fecha limite<br>
                        <code class="small">@{{rejection_reason}}</code> - Motivo rechazo<br>
                        <code class="small">@{{company_name}}</code> - Nombre empresa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initial Request Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-primary d-flex align-items-center justify-content-center">
                            <i class="ti ti-send text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Solicitud Inicial de Documentos</h6>
                            <small class="text-muted">Se envia cuando se crea una nueva solicitud de documentos</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_initial_request_enabled"
                               id="emailInitialRequestEnabled"
                               value="1"
                               {{ ($settings['email']['initial_request']['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailInitialRequestEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['initial_request']['enabled'] ?? true) ? '' : 'opacity-50' }}" id="initialRequestContent">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_initial_request_subject"
                               value="{{ $settings['email']['initial_request']['subject'] ?? 'Solicitud de Documentos - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo..."
                               required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_initial_request_message"
                                  rows="5"
                                  data-max-chars="2000"
                                  id="emailInitialRequestMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['initial_request']['message'] ?? 'Estimado/a {{customer_name}},

Necesitamos que nos proporcione los siguientes documentos para completar su solicitud:

Por favor, acceda al siguiente enlace para cargar sus documentos: {{upload_link}}

Fecha limite: {{due_date}}

Gracias por su colaboracion.

{{company_name}}' }}</textarea>
                        <small class="text-muted">Maximo 2000 caracteres. Usa las variables indicadas arriba.</small>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-preview-email" data-email-type="initial_request">
                                <i class="ti ti-eye me-1"></i> Vista Previa
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-test-email" data-email-type="initial_request">
                                <i class="ti ti-send me-1"></i> Enviar Prueba
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Documents Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-warning d-flex align-items-center justify-content-center">
                            <i class="ti ti-file-alert text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Documentos Faltantes o Rechazados</h6>
                            <small class="text-muted">Se envia cuando se solicita reenvio de documentos especificos</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_missing_docs_enabled"
                               id="emailMissingDocsEnabled"
                               value="1"
                               {{ ($settings['email']['missing_docs']['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailMissingDocsEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['missing_docs']['enabled'] ?? true) ? '' : 'opacity-50' }}" id="missingDocsContent">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_missing_docs_subject"
                               value="{{ $settings['email']['missing_docs']['subject'] ?? 'Documentos Requeridos - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_missing_docs_message"
                                  rows="5"
                                  data-max-chars="2000"
                                  id="emailMissingDocsMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['missing_docs']['message'] ?? 'Estimado/a {{customer_name}},

Necesitamos que vuelva a enviar el siguiente documento: {{document_name}}

Motivo: {{rejection_reason}}

Por favor, acceda al siguiente enlace: {{upload_link}}

{{company_name}}' }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-preview-email" data-email-type="missing_docs">
                                <i class="ti ti-eye me-1"></i> Vista Previa
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-test-email" data-email-type="missing_docs">
                                <i class="ti ti-send me-1"></i> Enviar Prueba
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reminder Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-info d-flex align-items-center justify-content-center">
                            <i class="ti ti-bell text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Recordatorio Automatico</h6>
                            <small class="text-muted">Se envia automaticamente cuando no se han cargado los documentos</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_reminder_enabled"
                               id="emailReminderEnabled"
                               value="1"
                               {{ ($settings['email']['reminder']['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailReminderEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['reminder']['enabled'] ?? true) ? '' : 'opacity-50' }}" id="reminderContent">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Enviar Despues de <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control"
                                   name="email_reminder_days"
                                   value="{{ $settings['email']['reminder']['days'] ?? 7 }}"
                                   min="1"
                                   max="30">
                            <span class="input-group-text">dias</span>
                        </div>
                        <small class="text-muted">Dias sin actividad para enviar recordatorio</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Maximo de Recordatorios
                        </label>
                        <input type="number"
                               class="form-control"
                               name="email_reminder_max"
                               value="{{ $settings['email']['reminder']['max_reminders'] ?? 3 }}"
                               min="1"
                               max="10">
                        <small class="text-muted">0 = Sin limite</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Intervalo entre Recordatorios
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control"
                                   name="email_reminder_interval"
                                   value="{{ $settings['email']['reminder']['interval'] ?? 3 }}"
                                   min="1"
                                   max="14">
                            <span class="input-group-text">dias</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_reminder_subject"
                               value="{{ $settings['email']['reminder']['subject'] ?? 'Recordatorio: Documentos Pendientes - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_reminder_message"
                                  rows="5"
                                  data-max-chars="2000"
                                  id="emailReminderMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['reminder']['message'] ?? 'Estimado/a {{customer_name}},

Le recordamos que aun tiene documentos pendientes de cargar para su orden #{{order_id}}.

Por favor, acceda al siguiente enlace para completar la carga: {{upload_link}}

Fecha limite: {{due_date}}

Gracias.

{{company_name}}' }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-preview-email" data-email-type="reminder">
                                <i class="ti ti-eye me-1"></i> Vista Previa
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-test-email" data-email-type="reminder">
                                <i class="ti ti-send me-1"></i> Enviar Prueba
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-success d-flex align-items-center justify-content-center">
                            <i class="ti ti-check text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Documento Aprobado</h6>
                            <small class="text-muted">Se envia cuando un documento individual es aprobado</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_approval_enabled"
                               id="emailApprovalEnabled"
                               value="1"
                               {{ ($settings['email']['approval']['enabled'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailApprovalEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['approval']['enabled'] ?? false) ? '' : 'opacity-50' }}" id="approvalContent">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_approval_subject"
                               value="{{ $settings['email']['approval']['subject'] ?? 'Documento Aprobado: {{document_name}} - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_approval_message"
                                  rows="4"
                                  data-max-chars="2000"
                                  id="emailApprovalMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['approval']['message'] ?? 'Estimado/a {{customer_name}},

Su documento "{{document_name}}" ha sido aprobado.

{{company_name}}' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-danger d-flex align-items-center justify-content-center">
                            <i class="ti ti-x text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Documento Rechazado</h6>
                            <small class="text-muted">Se envia cuando un documento es rechazado y requiere correccion</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_rejection_enabled"
                               id="emailRejectionEnabled"
                               value="1"
                               {{ ($settings['email']['rejection']['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailRejectionEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['rejection']['enabled'] ?? true) ? '' : 'opacity-50' }}" id="rejectionContent">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_rejection_subject"
                               value="{{ $settings['email']['rejection']['subject'] ?? 'Documento Rechazado: {{document_name}} - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_rejection_message"
                                  rows="5"
                                  data-max-chars="2000"
                                  id="emailRejectionMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['rejection']['message'] ?? 'Estimado/a {{customer_name}},

Su documento "{{document_name}}" ha sido rechazado por el siguiente motivo:

{{rejection_reason}}

Por favor, cargue una version corregida en: {{upload_link}}

{{company_name}}' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Email -->
    <div class="setting-section mb-4">
        <div class="card border">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-40 rounded-circle bg-primary d-flex align-items-center justify-content-center">
                            <i class="ti ti-confetti text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0">Proceso Completado</h6>
                            <small class="text-muted">Se envia cuando todos los documentos han sido aprobados</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="email_completion_enabled"
                               id="emailCompletionEnabled"
                               value="1"
                               {{ ($settings['email']['completion']['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="emailCompletionEnabled">Habilitado</label>
                    </div>
                </div>
            </div>
            <div class="card-body email-section-content {{ ($settings['email']['completion']['enabled'] ?? true) ? '' : 'opacity-50' }}" id="completionContent">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Asunto del Email <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               name="email_completion_subject"
                               value="{{ $settings['email']['completion']['subject'] ?? 'Documentacion Completa - Orden #{{order_id}}' }}"
                               placeholder="Asunto del correo...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje del Email
                        </label>
                        <textarea class="form-control"
                                  name="email_completion_message"
                                  rows="5"
                                  data-max-chars="2000"
                                  id="emailCompletionMessage"
                                  placeholder="Escribe el mensaje...">{{ $settings['email']['completion']['message'] ?? 'Estimado/a {{customer_name}},

Nos complace informarle que todos los documentos requeridos para su orden #{{order_id}} han sido aprobados satisfactoriamente.

Su solicitud continuara con el siguiente paso del proceso.

Gracias por su colaboracion.

{{company_name}}' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Notifications Section -->
    <div class="setting-section">
        <h6 class="fw-semibold mb-3 text-uppercase text-muted small">
            <i class="ti ti-users me-1"></i> Notificaciones a Administradores
        </h6>

        <div class="row g-3">
            <!-- Notify on New Upload -->
            <div class="col-md-6">
                <div class="p-3 bg-light rounded h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <label class="form-label fw-semibold mb-1">
                                <i class="ti ti-upload me-1 text-muted"></i>
                                Notificar Nueva Carga
                            </label>
                            <p class="text-muted small mb-0">Envia email al admin cuando se carga un nuevo documento.</p>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="notify_admin_new_upload"
                                   id="notifyAdminNewUpload"
                                   value="1"
                                   {{ ($settings['email']['admin']['notify_new_upload'] ?? true) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notify on SLA Breach -->
            <div class="col-md-6">
                <div class="p-3 bg-light rounded h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <label class="form-label fw-semibold mb-1">
                                <i class="ti ti-alert-triangle me-1 text-muted"></i>
                                Notificar Incumplimiento SLA
                            </label>
                            <p class="text-muted small mb-0">Envia alerta cuando se incumple una politica SLA.</p>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="notify_admin_sla_breach"
                                   id="notifyAdminSlaBreach"
                                   value="1"
                                   {{ ($settings['email']['admin']['notify_sla_breach'] ?? true) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Email Recipients -->
            <div class="col-12">
                <div class="p-3 bg-light rounded">
                    <label class="form-label fw-semibold mb-1">
                        Destinatarios de Notificaciones Admin
                    </label>
                    <p class="text-muted small mb-2">Direcciones de email que recibiran las notificaciones administrativas (separadas por coma).</p>
                    <input type="text"
                           class="form-control"
                           name="admin_notification_emails"
                           value="{{ $settings['email']['admin']['recipients'] ?? '' }}"
                           placeholder="admin@empresa.com, soporte@empresa.com">
                    <small class="text-muted">Deja vacio para usar el email del administrador principal.</small>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle email section content when switch is toggled
    $('input[type="checkbox"][id^="email"]').on('change', function() {
        const contentId = $(this).attr('id').replace('Enabled', 'Content');
        const content = $('#' + contentId.replace('email', '').charAt(0).toLowerCase() + contentId.replace('email', '').slice(1));

        if (content.length === 0) {
            // Try alternative ID format
            const altId = $(this).closest('.card').find('.card-body').attr('id');
            if (altId) {
                $('#' + altId).toggleClass('opacity-50', !this.checked);
            }
        } else {
            content.toggleClass('opacity-50', !this.checked);
        }
    });

    // Email preview button handler
    $('.btn-preview-email').on('click', function() {
        const emailType = $(this).data('email-type');
        const card = $(this).closest('.card');
        const subject = card.find('input[name$="_subject"]').val();
        const message = card.find('textarea').val();

        // Replace template variables with sample data
        const sampleData = {
            'customer_name': 'Juan Perez',
            'customer_email': 'juan.perez@email.com',
            'order_id': '12345',
            'document_name': 'DNI - Anverso',
            'document_type': 'DNI',
            'upload_link': 'https://ejemplo.com/cargar/abc123',
            'due_date': '{{ now()->addDays(7)->format("d/m/Y") }}',
            'rejection_reason': 'El documento esta borroso o ilegible.',
            'company_name': '{{ config("app.name") }}'
        };

        let previewSubject = subject;
        let previewMessage = message;

        for (const [key, value] of Object.entries(sampleData)) {
            const regex = new RegExp('\\{\\{' + key + '\\}\\}', 'g');
            previewSubject = previewSubject.replace(regex, value);
            previewMessage = previewMessage.replace(regex, value);
        }

        // Build preview HTML
        const previewHtml = `
            <div class="mb-3">
                <strong class="d-block text-muted small mb-1">Asunto:</strong>
                <div class="p-2 bg-light rounded">${previewSubject}</div>
            </div>
            <div>
                <strong class="d-block text-muted small mb-1">Mensaje:</strong>
                <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">${previewMessage}</div>
            </div>
        `;

        $('#emailPreviewContent').html(previewHtml);
        $('#emailPreviewModal').modal('show');
    });

    // Test email button handler
    $('.btn-test-email').on('click', function() {
        const btn = $(this);
        const emailType = btn.data('email-type');

        btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> Enviando...');

        $.ajax({
            url: '{{ route("manager.settings.documents.settings.test-email") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email_type: emailType
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Email de prueba enviado correctamente', 'Exito');
                } else {
                    toastr.error(response.message || 'Error al enviar el email', 'Error');
                }
                btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Enviar Prueba');
            },
            error: function(xhr) {
                toastr.error('Error al enviar el email de prueba', 'Error');
                btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Enviar Prueba');
            }
        });
    });

    // Send test email from modal
    $('#btnSendTestEmail').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> Enviando...');

        $.ajax({
            url: '{{ route("manager.settings.documents.settings.test-email") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email_type: 'preview'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Email de prueba enviado al administrador', 'Exito');
                    $('#emailPreviewModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error al enviar el email', 'Error');
                }
                btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Enviar Email de Prueba');
            },
            error: function(xhr) {
                toastr.error('Error al enviar el email de prueba', 'Error');
                btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Enviar Email de Prueba');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .round-40 {
        width: 40px;
        height: 40px;
    }
    .email-section-content {
        transition: opacity 0.3s ease;
    }
    .email-section-content.opacity-50 input,
    .email-section-content.opacity-50 textarea,
    .email-section-content.opacity-50 button {
        pointer-events: none;
    }
</style>
@endpush
