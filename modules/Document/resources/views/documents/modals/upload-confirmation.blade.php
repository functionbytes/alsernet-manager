{{-- Modal: Confirmación de subida --}}
<div class="modal fade" id="uploadConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">
                    Confirmación de subida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
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

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Enviar Confirmación de Subida =====
        $('#btnSendUploadConfirmation').on('click', function() {
            const $btn = $(this);
            const notes = $('#uploadConfirmationNotes').val();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');

            $.ajax({
                url: "{{ route('documents.send-upload-confirmation', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
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
                        if (typeof reloadActionHistory === 'function') {
                            reloadActionHistory();
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
                    $btn.prop('disabled', false).html('Enviar');
                }
            });
        });
    });
</script>
@endpush
