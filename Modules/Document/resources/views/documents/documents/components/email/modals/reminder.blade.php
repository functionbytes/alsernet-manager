{{-- Modal: Recordatorio --}}
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
                        Se enviará un email de recordatorio al cliente para que complete la carga de documentos.
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

@push('scripts')
    <script>
        $(document).ready(function() {

            $(document).on('click', '.send-reminder-btn', function(e) {
                e.preventDefault();
                $('#reminderModal').modal('show');
            });

            // ===== Handler: Enviar Recordatorio =====
            $(document).on('click', '#btnSendReminder', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('Enviando...');

                $.ajax({
                    url: "{{ route('api.documents.send-reminder', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', window.documentUid),
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
                            if (typeof window.reloadActionHistory === 'function') {
                                window.reloadActionHistory();
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
                        $btn.html('Enviar Recordatorio');
                    }
                });
            });
        });
    </script>
@endpush
