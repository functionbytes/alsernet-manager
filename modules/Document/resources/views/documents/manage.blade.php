 @extends('layouts.theme')

@section('title', 'Gestionar Documento')

@section('content')

    @include('theme.components.card', ['title' => 'Gestionar Documento'])

    @include('theme.components.alerts')

    <div class="row">
        <div class="col-lg-4">
            <!-- Email Actions - Sidebar (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('email-actions'))
                @include('theme.components.email-actions-card', [
                    'document' => $document,
                    'documentConfig' => $documentConfig
                ])
            @endif

            <!-- Workflow Multi-Etapa (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('validation-workflow'))
                @include('documents::documents.components.validation.validation-workflow-sidebar')
            @endif

            <!-- Document Notes (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('document-notes'))
                @include('documents::documents.components.notes.document-notes-sidebar')
            @endif

            <!-- Action History (Always Visible) -->
            <div id="actionHistoryContainer">
                @include('documents::documents.components.management.action-history')
            </div>

            <!-- Email History (Always Visible) -->
            @include('documents::documents.components.email.email-history')

            <!-- Status Timeline (Always Visible) -->
            @include('documents::documents.components.management.status-timeline')

        </div>

        <div class="col-lg-8">

            @if($products->count())
                <!-- Products List -->
                <div class="card mb-3">
                    <div class="card-header ">
                        <h5 class="mb-1 fw-bold">Listado de productos</h5>
                        <p class="small mb-0 text-muted">Productos relacionados con la orden</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover bg-light mb-0">
                                <tbody>
                                @foreach($products as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white">{{ $item->quantity}} ud</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Details -->
            @include('documents::documents.components.management.order-details')

            <!-- Customer Information -->
            @include('documents::documents.components.management.customer-information')

            <!-- Document Configuration (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('document-management'))
                @include('theme.components.document-management-card', [
                    'document' => $document,
                    'statuses' => $statuses,
                    'documentSources' => $documentSources,
                    'documentLoads' => $documentLoads,
                    'documentSyncs' => $documentSyncs,
                    'uploadTypes' => $uploadTypes
                ])
            @endif

            <!-- Upload Section (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('document-upload'))
                <div id="uploadSectionContainer">
                    @include('documents::partials.upload-section', [
                        'document' => $document,
                        'requiredDocuments' => $requiredDocuments,
                        'uploadedDocs' => $uploadedDocs,
                        'missingDocs' => $missingDocs,
                        'allUploaded' => $allUploaded,
                    ])
                </div>
            @endif

            <!-- Additional Attachments Section (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('additional-attachments'))
                @include('documents::documents.components.files.additional-attachments')
            @endif

        </div>

    </div>

    {{-- ========================================================================
         MODALES DE UTILIDAD (Confirmaciones)
         ======================================================================== --}}

    @include('documents::documents.components.management.modals.confirm-missing-docs')
    @include('documents::documents.components.files.modals.confirm-delete')

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const documentUid = '{{ $document->uid }}';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });



            // ===== Cargar Documentos Múltiples =====
            $(document).on('submit', '#adminUploadForm', function(e) {
                e.preventDefault();

                const $form = $(this);
                const formData = new FormData(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const $progressBar = $('#uploadProgress');
                const $uploadStatus = $('#uploadStatus');

                // Verificar que al menos un archivo esté seleccionado
                let hasFiles = false;
                const uploadedByType = {};
                const filesBeingUploaded = [];

                $('.document-file-input').each(function() {
                    const $input = $(this);
                    const docType = $input.data('doc-type');

                    if ($input[0].files && $input[0].files.length > 0) {
                        hasFiles = true;
                        uploadedByType[docType] = true;

                        const $item = $input.closest('.document-upload-item');
                        let docLabel = $item.find('.form-label').text().trim();

                        // Validar que se encontró el label
                        if (!docLabel) {
                            docLabel = `Documento (${docType})`;
                        }

                        filesBeingUploaded.push({
                            type: docType,
                            label: docLabel
                        });
                    }
                });

                if (!hasFiles) {
                    toastr.warning('Por favor selecciona al menos un documento', 'Atención', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    return;
                }

                // Validar que lo que se está cargando existe en los requeridos
                let allFilesValid = true;
                filesBeingUploaded.forEach(file => {
                    const $item = $(`.document-upload-item[data-doc-type="${file.type}"]`);
                    if ($item.length === 0) {
                        toastr.error(`Documento tipo "${file.label}" no es válido`, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        allFilesValid = false;
                    }
                });

                if (!allFilesValid) {
                    return;
                }

                // Advertir si aún hay documentos faltantes después de esta carga
                const missingAfterUpload = [];
                $('.document-upload-item').each(function() {
                    const docType = $(this).data('doc-type');
                    const isAlreadyUploaded = $(this).find('.uploaded-doc-info').length > 0;

                    // Verificar si hay archivo seleccionado en el input (búsqueda más directa)
                    const $fileInput = $(this).find('input.document-file-input');
                    const hasFileSelected = $fileInput.length > 0 && $fileInput[0].files && $fileInput[0].files.length > 0;

                    // También verificar en uploadedByType por compatibilidad
                    const isBeingUploaded = uploadedByType[docType] || hasFileSelected;

                    if (!isAlreadyUploaded && !isBeingUploaded) {
                        const docLabel = $(this).find('.form-label').text().trim();
                        missingAfterUpload.push({
                            type: docType,
                            label: docLabel
                        });
                    }
                });

                if (missingAfterUpload.length > 0) {
                    // Mostrar modal con advertencia clara
                    const missingHtml = missingAfterUpload.map(doc => `<li><strong>${doc.label}</strong></li>`).join('');
                    const uploadingHtml = filesBeingUploaded.map(file => `<li class="text-success"><strong>${file.label}</strong></li>`).join('');

                    let modalBody = `
                        <div class="mb-3">
                            <h6 class="text-success mb-2">Estás cargando:</h6>
                            <ul class="list-unstyled ms-3">
                                ${uploadingHtml}
                            </ul>
                        </div>
                        <div>
                            <h6 class="text-success mb-2">Aún faltarán después de esta carga:</h6>
                            <ul class="list-unstyled ms-3">
                                ${missingHtml}
                            </ul>
                        </div>
                    `;

                    $('#missingDocsList').html(modalBody);
                    $('#confirmMissingDocumentsModal').modal('show');

                    // Guardar formData en variable global para usarla cuando confirme
                    window.pendingFormData = formData;
                    window.pendingUpload = true;
                    return;
                }

                // Si no hay documentos faltantes, proceder directamente
                performUpload($submitBtn, formData, $progressBar, $uploadStatus);
            });

            // ===== Eliminar Documento Individual =====
            $(document).on('click', '.btn-delete-single-doc', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const mediaId = $btn.data('media-id');
                const docType = $btn.data('doc-type');

                // Guardar datos en window global para usar en el modal
                window.pendingDelete = {
                    btn: $btn,
                    mediaId: mediaId,
                    docType: docType
                };

                // Mostrar modal
                $('#confirmDeleteDocumentModal').modal('show');
            });

            // ===== Handler para confirmar eliminación desde el modal =====

            // ===== Confirmar Carga =====
            $(document).on('click', '.confirm-upload-btn', function(e) {
                e.preventDefault();

                if (!confirm('¿Confirmar carga del documento?')) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('<i class="ti ti-loader-2 spin"></i> Confirmando...');

                $.ajax({
                    url: "{{ route('api.documents.confirm-upload') }}",
                    type: 'POST',
                    data: {
                        uid: documentUid
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Carga confirmada correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(response.message || 'No se pudo confirmar', 'Error', {
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
                        $btn.html('<i class="ti ti-check"></i> Confirmar carga');
                    }
                });
            });

            // ===== Guardar Configuración (Estado y Origen) =====
            let configFormData = null;
            let $configSubmitBtn = null;
            let previousStatusId = $('#status_id').val();

            // Mapeo de estados a descripciones de email (según nombres reales del sistema)
            const statusEmailMap = {
                'pending': {
                    show: true,
                    label: 'Solicitud inicial',
                    description: 'Se enviará un email de "Solicitud inicial" solicitando al cliente que cargue los documentos requeridos.'
                },
                'awaiting_documents': {
                    show: true,
                    label: 'Recordatorio',
                    description: 'Se enviará un email de "Recordatorio" solicitando los documentos pendientes.'
                },
                'received': {
                    show: true,
                    label: 'Confirmación de subida',
                    description: 'Se enviará un email de "Confirmación de subida" confirmando que los documentos han sido recibidos y están en revisión.'
                },
                'incomplete': {
                    show: true,
                    label: 'Documentos específicos',
                    description: 'Se enviará un email de "Documentos específicos" indicando los documentos que faltan y deben ser enviados.'
                },
                'approved': {
                    show: true,
                    label: 'Notificación de aprobación',
                    description: 'Se enviará un email de "Notificación de aprobación" confirmando que los documentos han sido aprobados.'
                },
                'rejected': {
                    show: true,
                    label: 'Notificación de rechazo',
                    description: 'Se enviará un email de "Notificación de rechazo" indicando que los documentos han sido rechazados y deben ser reenviados.'
                },
                'cancelled': {
                    show: false
                }
            };

            // Detectar cambio de estado
            $('#status_id').on('change', function() {
                const newStatusId = $(this).val();
                const statusChanged = newStatusId !== previousStatusId;

                if (statusChanged) {
                    previousStatusId = newStatusId;
                }
            });

            $(document).on('submit', '#formDocumentConfig', function(e) {
                e.preventDefault();

                const $form = $(this);
                const selectedStatusId = $('#status_id').val();
                const selectedStatusKey = $('#status_id option:selected').data('key') || '';

                configFormData = {
                    status_id: selectedStatusId,
                    source_id: $('#source_id').val(),
                    load_id: $('#load_id').val(),
                    sync_id: $('#sync_id').val(),
                    upload_id: $('#upload_id').val()
                };
                $configSubmitBtn = $form.find('button[type="submit"]');

                // Verificar si el estado cambió y si debe mostrar opción de email
                const statusChanged = selectedStatusId && (selectedStatusId !== previousStatusId);
                const emailConfig = statusEmailMap[selectedStatusKey];

                if (statusChanged && emailConfig && emailConfig.show) {
                    // Mostrar sección de email
                    $('#emailNotificationSection').show();
                    $('#emailTypeDescription').html(
                        `<i class="fas fa-info-circle me-1"></i><strong>${emailConfig.label}:</strong> ${emailConfig.description}`
                    );
                    $('#sendEmailOnStatusChange').prop('checked', true);
                } else {
                    // Ocultar sección de email
                    $('#emailNotificationSection').hide();
                }

                // Open confirmation modal
                const modal = new bootstrap.Modal(document.getElementById('confirmConfigurationModal'));
                modal.show();
            });

            // Handle confirmation button click

            // ===== Abrir Modal de Redacción de Correo Personalizado =====
            // ===== Enviar Correo Personalizado (Directo) =====

            // ===== Actualizar Estado del Documento Dinámicamente =====
            /**
             * Recarga completamente la sección de carga de documentos vía AJAX
             * Reemplaza todo el HTML de la sección para evitar errores de sincronización
             */
            function reloadDocumentsSection(uid = documentUid) {
                console.log('[reloadDocumentsSection] Iniciando recarga para uid:', uid);

                $.ajax({
                    url: "{{ route('documents.refresh-section', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', uid),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('[reloadDocumentsSection] Response:', response);

                        if (response.success && response.html) {
                            console.log('[reloadDocumentsSection] Reemplazando HTML en #uploadSectionContainer');

                            const $container = $('#uploadSectionContainer');
                            console.log('[DEBUG] Contenedor encontrado:', $container.length > 0);
                            console.log('[DEBUG] Contenedor display:', $container.css('display'));
                            console.log('[DEBUG] Contenedor visibility:', $container.css('visibility'));
                            console.log('[DEBUG] Contenedor html antes:', $container.html().substring(0, 50) + '...');

                            // Reemplazar completamente el contenedor con el nuevo HTML
                            $container.html(response.html);

                            console.log('[DEBUG] Contenedor html después:', $container.html().substring(0, 50) + '...');
                            console.log('[DEBUG] Nueva altura del contenedor:', $container.height());
                            console.log('[reloadDocumentsSection] HTML reemplazado, estado actual:', $container.html().substring(0, 100));

                            // Asegurar que los event handlers estén activados (usan event delegation, así que no es necesario)
                            console.log('[reloadDocumentsSection] Recarga completada');
                        } else {
                            console.error('[reloadDocumentsSection] Respuesta sin success o html:', response);
                            toastr.error('No se pudo actualizar la sección', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('[reloadDocumentsSection] Error AJAX:', xhr);
                        let errorMsg = 'Error al refrescar la sección de documentos';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMsg = 'La ruta de actualización no existe (404)';
                        }

                        toastr.error(errorMsg, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                });
            }

            /**
             * DEPRECATED: Mantener por compatibilidad, usar reloadDocumentsSection()
             */
            function updateDocumentState(uid = documentUid) {
                reloadDocumentsSection(uid);
            }

            /**
             * Recarga el historial de acciones
             */
            function reloadActionHistory(uid = documentUid) {
                console.log('[reloadActionHistory] Iniciando recarga para uid:', uid);

                $.ajax({
                    url: "{{ route('documents.refresh-action-history', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', uid),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('[reloadActionHistory] Response:', response);

                        if (response.success && response.html) {
                            console.log('[reloadActionHistory] Reemplazando HTML en #actionHistoryContainer');

                            const $container = $('#actionHistoryContainer');
                            $container.html(response.html);

                            console.log('[reloadActionHistory] Recarga completada');
                        } else {
                            console.error('[reloadActionHistory] Respuesta sin success o html:', response);
                        }
                    },
                    error: function(xhr) {
                        console.error('[reloadActionHistory] Error AJAX:', xhr);
                        let errorMsg = 'Error al refrescar el historial de acciones';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        console.error(errorMsg);
                    }
                });
            }

            /**
             * Re-inicializar handlers de upload (event delegation ya está en lugar)
             * Esta función es un stub para compatibilidad futura
             */
            function initializeUploadHandlers() {
                // Los handlers de upload usan $(document).on() así que ya funcionan con elementos dinámicos
                // Esta función es un placeholder para compatibilidad futura si es necesario
            }

            /**
             * Re-inicializar handlers de delete (event delegation ya está en lugar)
             * Esta función es un stub para compatibilidad futura
             */
            function initializeDeleteHandlers() {
                // Los handlers de delete usan $(document).on() así que ya funcionan con elementos dinámicos
                // Esta función es un placeholder para compatibilidad futura si es necesario
            }

            /**
             * Actualiza los elementos visuales de documentos ya cargados
             */
            function updateUploadedDocumentsUI(response) {
                // Nueva estructura simplificada: uploaded_documents es un array de keys ["doc_1", "doc_2"]
                // Los detalles completos están en uploaded_documents_details
                const uploadedKeys = response.uploaded_documents || [];
                const uploadedDetails = response.uploaded_documents_details || {};
                const uploadedDocs = uploadedKeys.reduce((acc, key) => {
                    acc[key] = uploadedDetails[key];
                    return acc;
                }, {});

                // Actualizar contador principal
                const totalDocs = Object.keys(response.stats?.total_required || {}).length || $('.document-upload-item').length;
                const uploadedCount = uploadedKeys.length;
                $('#documentCounter').text(uploadedCount + '/' + totalDocs + ' cargados');

                // Iterar sobre cada item de documento
                $('.document-upload-item').each(function() {
                    const docType = $(this).data('doc-type');
                    const $badge = $(this).find('.badge');
                    const $input = $(this).find('input[type="file"]');
                    const $infoDiv = $(this).find('.uploaded-doc-info');

                    if (uploadedDocs[docType]) {
                        const docInfo = uploadedDocs[docType];

                        // Cambiar badge a "Cargado"
                        $badge.removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success');
                        $badge.html('<i class="fa fa-check-circle"></i> Cargado');

                        // Si no existe la div de info, crearla
                        if ($infoDiv.length === 0) {
                            const infoDivHtml = `
                                <div class="uploaded-doc-info mt-2 p-3 bg-light border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark small">
                                                <i class="fa fa-file-pdf text-danger"></i> ${escapeHtml(docInfo.file_name)}
                                            </p>
                                            <small class="text-muted d-block mt-1">
                                                ${formatBytes(docInfo.size)} • ${docInfo.created_at}
                                            </small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="${docInfo.url}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-media-id="${docInfo.id}" data-doc-type="${docType}" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $(this).append(infoDivHtml);
                        } else {
                            // Actualizar contenido existente
                            $infoDiv.find('.fw-semibold').html(`<i class="fa fa-file-pdf text-danger"></i> ${escapeHtml(docInfo.file_name)}`);
                            $infoDiv.find('small.text-muted').html(`${formatBytes(docInfo.size)} • ${docInfo.created_at}`);
                        }

                        // Ocultar input si existe
                        if ($input.length) {
                            $input.hide();
                        }
                    } else {
                        // Documento NO cargado
                        $badge.removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger');
                        $badge.html('<i class="fa fa-clock"></i> Pendiente');

                        // Eliminar div de info si existe
                        if ($infoDiv.length) {
                            $infoDiv.remove();
                        }

                        // Si no existe input, recrearlo
                        if ($input.length === 0) {
                            const inputHtml = `
                                <input
                                    type="file"
                                    class="form-control document-file-input"
                                    name="documents[${docType}]"
                                    data-doc-type="${docType}"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                >
                                <small class="text-muted d-block mt-1">
                                    <i class="fa fa-info-circle"></i> PDF, JPG, PNG, DOC (máximo 10MB)
                                </small>
                            `;
                            $(this).append(inputHtml);
                        } else {
                            // Si existe, asegurarse de que esté visible
                            $input.show();
                        }
                    }
                });
            }

            /**
             * Actualiza la lista de documentos faltantes
             */
            function updateMissingDocumentsUI(response) {
                const missingDocs = response.missing_documents || {};
                const totalRequired = response.stats?.total_required || 0;
                const totalMissing = response.stats?.total_missing || 0;

                // Actualizar contador de documentos faltantes en el modal
                const $missingBadge = $('.missing-count-badge');
                if ($missingBadge.length) {
                    if (totalMissing > 0) {
                        $missingBadge.removeClass('d-none').text(totalMissing);
                    } else {
                        $missingBadge.addClass('d-none');
                    }
                }

                // Actualizar lista de documentos faltantes si existe
                const $missingList = $('#missingDocsList');
                if ($missingList.length) {
                    $missingList.empty();
                    if (Object.keys(missingDocs).length > 0) {
                        Object.entries(missingDocs).forEach(([docType, docLabel]) => {
                            $missingList.append(`
                                <li class="list-group-item">
                                    <i class="fa fa-warning text-warning"></i> ${escapeHtml(docLabel)}
                                </li>
                            `);
                        });
                    } else {
                        $missingList.html('<li class="list-group-item text-success">Todos los documentos están cargados</li>');
                    }
                }

                // Si está completo, mostrar mensaje de éxito
                if (response.all_uploaded) {
                    toastr.success('¡Todos los documentos han sido cargados correctamente!', 'Completado', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                }
            }

            /**
             * Función auxiliar para formatear bytes a tamaño legible
             */
            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            // Llamar a updateDocumentState después de cargar documentos exitosamente
            // Actualiza la función existente en el success handler del upload
            $(document).on('ajax-upload-success', function() {
                updateDocumentState();
            });

            // También actualizar cuando se elimina un documento
            $(document).on('document-deleted', function() {
                updateDocumentState();
            });

            /**
             * Realiza el upload de documentos via AJAX
             */
            function performUpload($submitBtn, formData, $progressBar, $uploadStatus) {
                $submitBtn.prop('disabled', true);
                $submitBtn.html('Cargando...');
                $progressBar.show();
                $uploadStatus.text('Cargando documentos...');

                $.ajax({
                    url: "{{ route('documents.admin-upload', ['uid' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', documentUid),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                $('#uploadProgressBar').css('width', percentComplete + '%');
                                $uploadStatus.text('Cargando... ' + Math.round(percentComplete) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            const uploadedCount = response.uploaded_count || 1;
                            toastr.success('Se cargaron ' + uploadedCount + ' documento(s) correctamente', 'Éxito', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            // Actualizar estado del documento sin recargar la página
                            updateDocumentState(documentUid);
                        } else {
                            toastr.error(response.message || 'No se pudo cargar', 'Error', {
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
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html('Cargar documentos');
                        $progressBar.hide();
                    }
                });
            }

            /**
             * Handler para confirmar upload desde el modal
             */

            // ===== Renderizar Variables Panel en Modal de Correo Personalizado =====

            // ===== Upload Confirmation Handler =====
            });

            // ===== Approval Handler =====


            // =========================================
            // MULTI-STAGE WORKFLOW: APPROVE & REJECT
            // =========================================


        });
    </script>

    <style>
        /* Spinner animation for loading states */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        /* Stat card hover effect */
        .stat-card {
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

    </style>
@endpush
