{{-- Modal: Solicitud inicial de documentos --}}
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

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Enviar Solicitud Inicial de Documentos =====
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
    });
</script>
@endpush
