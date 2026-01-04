{{-- Modal: Solicitar documentos faltantes --}}
<div class="modal fade" id="missingDocsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Solicitar documentos faltantes</h5>
                    <p class="text-muted small mb-0">Selecciona los documentos a solicitar</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(count($missingDocs) > 0)
                    <p class="text-muted small mb-3">Los siguientes documentos están pendientes de carga:</p>
                    <form id="missingDocsForm">
                        <div class="border rounded p-3 bg-light-secondary mb-3">
                            @foreach($missingDocs as $key => $label)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="missing_docs[]" value="{{ $key }}" id="missing_{{ $key }}" checked>
                                    <label class="form-check-label fw-semibold" for="missing_{{ $key }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-0">
                            <label for="additional_notes" class="form-label fw-semibold">Notas adicionales (opcional)</label>
                            <textarea class="form-control" id="additional_notes" name="notes" rows="3" placeholder="Ej: La foto del DNI está borrosa..."></textarea>
                        </div>
                    </form>
                @else
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-1 fw-bold">Todos los documentos están cargados</h6>
                                <p class="mb-0 small">No hay documentos faltantes para este tipo de solicitud.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-top">
                @if(count($missingDocs) > 0)
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSendMissingDocs">
                        Enviar solicitud
                    </button>
                @endif
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // ===== Handler: Enviar Documentos Faltantes =====
            $('#btnSendMissingDocs').on('click', function() {
                const $btn = $(this);
                const $form = $('#missingDocsForm');
                const selectedDocs = [];

                $form.find('input[name="missing_docs[]"]:checked').each(function() {
                    selectedDocs.push($(this).val());
                });

                if (selectedDocs.length === 0) {
                    toastr.warning('Selecciona al menos un documento faltante', 'Atención', {
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
                $.ajax({
                    url: "{{ route('api.documents.send-missing', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: {
                        missing_docs: selectedDocs,
                        notes: $('#additional_notes').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#missingDocsModal').modal('hide');

                            // Limpiar checkboxes
                            $form.find('input[name="missing_docs[]"]:checked').prop('checked', false);

                            // Limpiar textarea de notas
                            $('#additional_notes').val('');

                            toastr.success('Email enviado correctamente', 'Éxito', {
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
                        $btn.html('Documentos faltantes');
                    }
                });
            });
        });
    </script>
@endpush
