{{-- Modal: Confirmar carga con documentos faltantes --}}
<div class="modal fade" id="confirmMissingDocumentsModal" tabindex="-1" role="dialog" aria-labelledby="confirmMissingDocumentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmMissingDocumentsModalLabel">
                    Documentos faltantes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong>Aún faltarán por cargar los siguientes documentos:</strong>
                </p>
                <ul id="missingDocsList" class="list-unstyled ms-3">
                    <!-- Se rellena dinámicamente con JavaScript -->
                </ul>
                <p class="mt-3 mb-0 text-muted">
                    <small>¿Deseas continuar con la carga de todas formas?</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 mb-1" id="confirmUploadBtn">
                    Sí, continuar
                </button>
                <button type="button" class="btn btn-secondary w-100 " data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Confirmar Carga con Documentos Faltantes =====
        $(document).on('click', '#confirmUploadBtn', function() {
            $('#confirmMissingDocumentsModal').modal('hide');
            performUpload(
                $('#adminUploadForm').find('button[type="submit"]'),
                window.pendingFormData,
                $('#uploadProgress'),
                $('#uploadStatus')
            );
        });
    });
</script>
@endpush
