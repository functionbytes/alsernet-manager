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

                // Usar fetch en lugar de jQuery AJAX para mayor control
                fetch("{{ route('api.documents.send-reminder', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', window.documentUid), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    // Parsear JSON sin importar el status code
                    return response.json().then(data => ({
                        status: response.status,
                        data: data
                    }));
                })
                .then(({ status, data }) => {
                    // Manejar 200 (éxito)
                    if (status === 200) {
                        if (data.success) {
                            $('#reminderModal').modal('hide');
                            toastr.success('Email enviado a: ' + (data.recipient || 'cliente'), 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Recargar historial de acciones
                            if (typeof window.reloadActionHistory === 'function') {
                                window.reloadActionHistory();
                            }
                        } else if (data.error_type === 'rate_limit') {
                            showRateLimitModal(data);
                        } else {
                            toastr.error(data.message || 'No se pudo enviar', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    }
                    // Manejar 429 (rate limit)
                    else if (status === 429) {
                        if (data.error_type === 'rate_limit') {
                            showRateLimitModal(data);
                        } else {
                            toastr.error(data.message || 'Error al procesar la solicitud', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    }
                    // Manejar 422 (validación)
                    else if (status === 422) {
                        toastr.error(data.message || 'Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                    // Otros errores
                    else {
                        toastr.error('Error al procesar la solicitud', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en solicitud:', error);
                    toastr.error('Error al procesar la solicitud', 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                })
                .finally(() => {
                    $btn.prop('disabled', false);
                    $btn.html('Enviar Recordatorio');
                });
            });

            // Función para mostrar modal de rate limit
            function showRateLimitModal(response) {
                const retryTime = new Date(response.retry_after);
                const secondsRemaining = Math.ceil(response.seconds_remaining);

                // Cerrar modal actual
                $('#reminderModal').modal('hide');

                // Crear modal de rate limit
                const modalHtml = `
                    <div class="modal fade" id="rateLimitModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-sm">
                                <div class="modal-header bg-light border-bottom">
                                    <h5 class="modal-title fw-bold">
                                        Límite de envío
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="text-muted mb-3">Solo puedes enviar un email del mismo tipo cada 60 segundos</p>
                                    <div class="mb-3">
                                        <div class="h2 text-warning fw-bold">${secondsRemaining}s</div>
                                        <small class="text-muted">Reintentar en</small>
                                    </div>
                                    <small class="text-muted d-block">A las ${retryTime.toLocaleTimeString('es-ES')}</small>
                                </div>
                                <div class="modal-footer border-top bg-light">
                                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Remover modal anterior si existe
                $('#rateLimitModal').remove();
                $('body').append(modalHtml);

                const rateLimitModal = new bootstrap.Modal(document.getElementById('rateLimitModal'));
                rateLimitModal.show();

                // Timer que actualiza cada segundo
                let remaining = secondsRemaining;
                const timerInterval = setInterval(function() {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(timerInterval);
                        rateLimitModal.hide();
                    } else {
                        $('#rateLimitModal .h2').html(remaining + 's');
                    }
                }, 1000);
            }
        });
    </script>
@endpush
