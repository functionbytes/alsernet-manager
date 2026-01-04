{{-- Modal: Correo personalizado --}}
<div class="modal fade" id="customEmailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Redactar correo personalizado</h5>
                    <p class="text-muted small mb-0">Envía un mensaje personalizado al cliente</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Plantilla configurada - Cargado via AJAX --}}
                <div class="alert alert-info" id="templateInfo" style="display: none;">
                    <h6 class="mb-1 fw-semibold">Plantilla configurada</h6>
                    <p class="mb-0 small">Se usará: <span class="badge bg-primary" id="templateName"></span></p>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Asunto</label>
                    <input type="text" class="form-control" id="emailSubject" placeholder="Asunto del correo">
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Mensaje</label>
                    <textarea class="form-control" id="emailMessage" rows="5" placeholder="Escribe tu mensaje..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendCustomEmail">
                    Enviar email
                </button>
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let customEmailTemplateId = null;

        // Cargar plantilla de email cuando se abre el modal
        $('#customEmailModal').on('show.bs.modal', function() {
            $.ajax({
                url: "{{ route('api.documents.custom-email-template') }}",
                method: 'GET',
                success: function(response) {
                    if (response.success && response.template) {
                        customEmailTemplateId = response.template.id;
                        $('#templateName').text(response.template.name);
                        $('#templateInfo').show();
                    }
                }
            });
        });

        $('#btnSendCustomEmail').on('click', function() {
            const $btn = $(this);
            const subject = $('#emailSubject').val().trim();
            const message = $('#emailMessage').val().trim();

            if (!subject || !message) {
                toastr.warning('Completa todos los campos', 'Atención');
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

            $.ajax({
                url: "{{ route('api.documents.send-custom-email', $document->uid) }}",
                method: 'POST',
                data: {
                    subject: subject,
                    message: message,
                    template_id: customEmailTemplateId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Email enviado a: ' + response.recipient, 'Éxito');
                        $('#customEmailModal').modal('hide');
                        $('#emailSubject, #emailMessage').val('');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error al enviar', 'Error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('Enviar email');
                }
            });
        });
    });
</script>
@endpush
