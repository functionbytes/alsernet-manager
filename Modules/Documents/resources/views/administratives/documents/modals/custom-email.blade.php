{{-- Modal: Correo personalizado --}}
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
                @if($customEmailTemplate ?? null)
                    <!-- Información de la plantilla configurada -->
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex align-items-start">
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
                            <div>
                                <h6 class="mb-1 fw-semibold">Sin plantilla configurada</h6>
                                <p class="mb-0 small">
                                    No hay plantilla de correo personalizado configurada. <a href="{{ route('manager.settings.documents.configurations') }}" class="text-decoration-none">Configura una plantilla aquí</a>
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

                    <!-- Variables disponibles Panel -->
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
                            Las variables entre llaves se reemplazarán con los datos reales del cliente
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

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Enviar Correo Personalizado =====
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
            $btn.html('Enviando...');

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
                        if (typeof reloadActionHistory === 'function') {
                            reloadActionHistory();
                        }
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

        // ===== Función: Renderizar Variables del Email Personalizado =====
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

        // ===== Cargar variables cuando se abre el modal =====
        $('#customEmailModal').on('show.bs.modal', function() {
            renderCustomEmailVariables();
        });

        // ===== Handler: Botón de recargar variables =====
        $(document).on('click', '#btnLoadCustomEmailVariables', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).find('i').addClass('fa-spin');

            // Simular carga y renderizar
            setTimeout(function() {
                renderCustomEmailVariables();
                $btn.prop('disabled', false).find('i').removeClass('fa-spin');
            }, 300);
        });
    });
</script>
@endpush
