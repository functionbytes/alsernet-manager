{{-- Modal: Confirmación de recepción de documentos --}}
<div class="modal fade" id="uploadConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Confirmación de recepción</h5>
                    <p class="text-muted small mb-0">Notifica al cliente que sus documentos han sido recibidos</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert bg-light border-0">
                    Se enviará un email confirmando que los documentos han sido recibidos correctamente.
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary w-100 mb-1" id="btnConfirmUpload">
                    <i class="fas fa-paper-plane me-1"></i> Enviar confirmación
                </button>
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Enviar confirmación de recepción =====
        $('#btnConfirmUpload').on('click', function() {
            const $btn = $(this);

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enviando...');

            $.ajax({
                url: "{{ route('api.documents.send-upload-confirmation', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', window.documentUid),
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Email de confirmación enviado a: ' + response.recipient, 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        $('#uploadConfirmationModal').modal('hide');

                        // Recargar historial de acciones
                        if (typeof window.reloadActionHistory === 'function') {
                            window.reloadActionHistory();
                        }
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
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar confirmación');
                }
            });
        });
    });
</script>
@endpush
