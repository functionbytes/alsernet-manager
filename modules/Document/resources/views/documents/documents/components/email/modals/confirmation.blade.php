{{-- Modal: Confirmar carga de documentos --}}
<div class="modal fade" id="uploadConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Confirmar carga de documentos</h5>
                    <p class="text-muted small mb-0">Verifica los archivos antes de cargar</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-info mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-info me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-2 fw-semibold">Archivos seleccionados</h6>
                            <p class="small mb-0">Revisa la lista de archivos que se cargarán al documento</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-file-upload me-2"></i>Archivos a cargar
                    </label>
                    <div id="filesList" class="border rounded p-3 bg-light">
                        <!-- Lista de archivos se llenará dinámicamente -->
                        <p class="text-muted text-center mb-0 small">No hay archivos seleccionados</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoría de documentos</label>
                    <select class="form-select select2" id="documentCategory">
                        <option value="">Seleccionar categoría...</option>
                        <option value="identity">Documentos de identidad</option>
                        <option value="financial">Documentos financieros</option>
                        <option value="legal">Documentos legales</option>
                        <option value="commercial">Documentos comerciales</option>
                        <option value="technical">Documentos técnicos</option>
                        <option value="other">Otros documentos</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Notas sobre los archivos</label>
                    <textarea
                        class="form-control"
                        id="uploadNotes"
                        rows="3"
                        placeholder="Agrega notas o comentarios sobre estos documentos (opcional)..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <div class="w-100">
                    <div class="progress mb-2" id="uploadProgress" style="display: none; height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             style="width: 0%"></div>
                    </div>
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnConfirmUpload">
                        Cargar archivos
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedFiles = [];

        // Función para mostrar archivos seleccionados
        window.showUploadConfirmation = function(files) {
            selectedFiles = files;

            if (!files || files.length === 0) {
                $('#filesList').html('<p class="text-muted text-center mb-0 small">No hay archivos seleccionados</p>');
                return;
            }

            let html = '<ul class="list-unstyled mb-0">';
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const sizeKB = (file.size / 1024).toFixed(2);
                const icon = getFileIcon(file.name);

                html += `
                    <li class="d-flex align-items-center mb-2 pb-2 ${i < files.length - 1 ? 'border-bottom' : ''}">
                        <i class="${icon} me-3 text-primary"></i>
                        <div class="flex-grow-1">
                            <div class="fw-medium small">${file.name}</div>
                            <small class="text-muted">${sizeKB} KB</small>
                        </div>
                    </li>
                `;
            }
            html += '</ul>';

            $('#filesList').html(html);
            $('#uploadConfirmationModal').modal('show');
        };

        // Función auxiliar para obtener icono según tipo de archivo
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'fas fa-file-pdf text-danger',
                'doc': 'fas fa-file-word text-primary',
                'docx': 'fas fa-file-word text-primary',
                'xls': 'fas fa-file-excel text-success',
                'xlsx': 'fas fa-file-excel text-success',
                'jpg': 'fas fa-file-image text-warning',
                'jpeg': 'fas fa-file-image text-warning',
                'png': 'fas fa-file-image text-warning',
                'zip': 'fas fa-file-archive text-secondary',
                'rar': 'fas fa-file-archive text-secondary'
            };
            return icons[ext] || 'fas fa-file text-muted';
        }

        $('#btnConfirmUpload').on('click', function() {
            if (selectedFiles.length === 0) {
                toastr.warning('No hay archivos seleccionados', 'Atención');
                return;
            }

            const $btn = $(this);
            const category = $('#documentCategory').val();
            const notes = $('#uploadNotes').val().trim();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Cargando...');
            $('#uploadProgress').show();

            const formData = new FormData();
            for (let i = 0; i < selectedFiles.length; i++) {
                formData.append('files[]', selectedFiles[i]);
            }
            formData.append('category', category || 'other');
            formData.append('notes', notes || '');
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ route('api.documents.files.store', $document->uid) }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    // NOTA: xhr.upload.addEventListener es necesario para progress tracking
                    // No hay equivalente nativo en jQuery para esto
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            // Actualizamos progress bar con jQuery
                            $('#uploadProgress .progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(`${response.uploaded_count} archivo(s) cargado(s) exitosamente`, 'Éxito');
                        $('#uploadConfirmationModal').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al cargar los archivos';
                    toastr.error(message, 'Error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt me-2"></i>Cargar archivos');
                    $('#uploadProgress').hide();
                    $('#uploadProgress .progress-bar').css('width', '0%');
                }
            });
        });

        // Limpiar el formulario cuando se cierra el modal
        $('#uploadConfirmationModal').on('hidden.bs.modal', function() {
            selectedFiles = [];
            $('#filesList').html('<p class="text-muted text-center mb-0 small">No hay archivos seleccionados</p>');
            $('#documentCategory').val('');
            $('#uploadNotes').val('');
            $('#uploadProgress').hide();
            $('#uploadProgress .progress-bar').css('width', '0%');
        });
    });
</script>
@endpush
