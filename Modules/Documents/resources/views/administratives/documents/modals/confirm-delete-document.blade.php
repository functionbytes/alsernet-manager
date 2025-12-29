{{-- Modal: Confirmar eliminación de documento --}}
<div class="modal fade" id="confirmDeleteDocumentModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title " id="confirmDeleteDocumentModalLabel">
                    Confirmar eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    <strong>¿Estás seguro de que deseas eliminar este documento?</strong>
                </p>
                <p class="text-muted small mt-2 mb-0">
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 mb-1" id="confirmDeleteBtn">
                    Eliminar
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
        // ===== Handler: Confirmar Eliminación de Documento =====
        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!window.pendingDelete) {
                return;
            }

            const { btn: $btn, mediaId, docType } = window.pendingDelete;

            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin"></i>');

            $('#confirmDeleteDocumentModal').modal('hide');

            $.ajax({
                url: "{{ route('api.documents.delete', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    media_id: mediaId,
                    doc_type: docType
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Documento eliminado correctamente', 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        // Actualizar estado sin recargar la página
                        if (typeof updateDocumentState === 'function') {
                            updateDocumentState(documentUid);
                        }
                    } else {
                        toastr.error(response.message || 'No se pudo eliminar', 'Error', {
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
                    $btn.html('<i class="fas fa-trash-alt"></i>');
                    window.pendingDelete = null;
                }
            });
        });
    });
</script>
@endpush
