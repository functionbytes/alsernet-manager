{{-- Modal: Notificación de aprobación --}}
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
                    <div>Se enviará un email notificando que los documentos han sido aprobados.</div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary w-100" id="btnSendApproval">
                    Enviar aprobación
                </button>
                <button type="button" class="btn btn-secondary  w-100" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Enviar Aprobación =====
        $('#btnSendApproval').on('click', function() {
            const $btn = $(this);

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');

            $.ajax({
                url: "{{ route('api.documents.send-approval', $document->uid) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Email de aprobación enviado a: ' + response.recipient, 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        $('#approvalModal').modal('hide');
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
