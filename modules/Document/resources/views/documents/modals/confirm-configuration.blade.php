{{-- Modal: Confirmar configuración del documento --}}
<div class="modal fade" id="confirmConfigurationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">
                    <i class="fas fa-save me-2 text-primary"></i>Guardar configuración
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>¿Deseas guardar la configuración del documento?</strong>
                </div>

                <p class="text-muted mb-3">
                    Se actualizarán el estado, origen y tipo de carga del documento.
                </p>

                <!-- Email notification option -->
                <div class="border rounded p-3 bg-light" id="emailNotificationSection" style="display: none;">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="sendEmailOnStatusChange" checked>
                        <label class="form-check-label fw-semibold" for="sendEmailOnStatusChange">
                            <i class="fas fa-envelope me-1"></i>Enviar email automático al cliente
                        </label>
                    </div>
                    <div class="ps-4">
                        <small class="text-muted" id="emailTypeDescription">
                            <!-- Se llenará dinámicamente según el estado seleccionado -->
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary w-100 mb-1" id="confirmConfigBtn">
                    <i class="fas fa-check me-2"></i>Guardar
                </button>
                <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Confirmar Guardado de Configuración =====
        $('#confirmConfigBtn').on('click', function() {
            if (!configFormData || !$configSubmitBtn) return;

            $configSubmitBtn.prop('disabled', true);
            $configSubmitBtn.html('Guardando...');

            // Agregar parámetro de email si está visible y checkeado
            const $emailSection = $('#emailNotificationSection');
            const $emailCheckbox = $('#sendEmailOnStatusChange');

            if ($emailSection.is(':visible') && $emailCheckbox.is(':checked')) {
                configFormData.send_email = true;
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmConfigurationModal'));
            modal.hide();

            $.ajax({
                url: "{{ route('documents.update', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                type: 'POST',
                data: configFormData,
                success: function(response) {
                    if (response.success) {
                        let successMsg = 'Configuración guardada correctamente';

                        // Si se envió un email, mostrar información adicional
                        if (response.email_sent) {
                            successMsg += `<br><small><i class="fas fa-envelope me-1"></i>Email enviado: ${response.email_sent.label} a ${response.email_sent.recipient}</small>`;
                        }

                        toastr.success(successMsg, 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right",
                            timeOut: 3000,
                            enableHtml: true
                        });

                        setTimeout(() => location.reload(), 2000);
                    } else {
                        toastr.error(response.message || 'No se pudo guardar', 'Error', {
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
                    $configSubmitBtn.prop('disabled', false);
                    $configSubmitBtn.html('Guardar configuración');
                }
            });
        });
    });
</script>
@endpush
