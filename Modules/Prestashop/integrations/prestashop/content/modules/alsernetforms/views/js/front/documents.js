$(document).ready(function () {

    // Solo ejecutar si existe el contenedor
    if (!$('#alsernet-documents').length) {
        return; // Salir para evitar errores
    }

    // Cargar estado de documentos al inicio
    loadDocumentStatus();

    // ============================================
    // Controlar estado del botón de submit
    // El botón debe estar DISABLED hasta que:
    // 1. Se acepten los términos (checkbox condition)
    // 2. Se complete el reCAPTCHA
    // ============================================

    // Función para verificar si el formulario puede ser enviado
    function updateSubmitButtonState() {
        const $submitBtn = $('button[type="submit"]');
        const isConditionChecked = $('#condition').is(':checked');
        const isRecaptchaValid = grecaptcha && grecaptcha.getResponse() ? grecaptcha.getResponse().length > 0 : false;

        console.log('Checking button state:', {
            conditionChecked: isConditionChecked,
            recaptchaValid: isRecaptchaValid
        });

        // Habilitar solo si AMBAS condiciones se cumplen
        if (isConditionChecked && isRecaptchaValid) {
            $submitBtn.prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
            console.log('✅ Submit button ENABLED');
        } else {
            $submitBtn.prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
            console.log('❌ Submit button DISABLED');
        }
    }

    // Inicialmente, el botón debe estar DISABLED
    $('button[type="submit"]').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');

    // Escuchar cambios en el checkbox de condiciones
    $('#condition').on('change', function() {
        console.log('Condition checkbox changed:', $(this).is(':checked'));
        updateSubmitButtonState();
    });

    // Escuchar cambios en el reCAPTCHA
    // Se dispara después de que el usuario completa el captcha
    window.onRecaptchaChange = function() {
        console.log('✅ reCAPTCHA completed/verified');
        updateSubmitButtonState();
    };

    // Escuchar cuando el reCAPTCHA expira
    window.onRecaptchaExpire = function() {
        console.log('⚠️ reCAPTCHA expired - need to verify again');
        updateSubmitButtonState();
    };

    // También verificar cuando el reCAPTCHA carga (para casos donde ya está completo)
    // Hacer una verificación cada 500ms durante los primeros 5 segundos
    let checkCount = 0;
    const recaptchaCheckInterval = setInterval(function() {
        checkCount++;
        try {
            if (grecaptcha && grecaptcha.getResponse && grecaptcha.getResponse().length > 0) {
                console.log('reCAPTCHA detected as complete (periodic check)');
                updateSubmitButtonState();
                clearInterval(recaptchaCheckInterval);
            }
        } catch (e) {
            console.log('reCAPTCHA not ready yet');
        }

        if (checkCount > 10) {
            // Dejar de verificar después de 5 segundos
            clearInterval(recaptchaCheckInterval);
        }
    }, 500);

    // Manejar envío del formulario
    if ($.fn.validate) {
        $("#alsernet-documents").validate({
            rules: {
                condition: { required: true }
            },
            messages: {
                condition: { required: "You must accept the conditions." }
            },
            submitHandler: function (form) {
                // Obtener todos los archivos a subir
                const filesToUpload = [];

                // Formatos permitidos
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                const allowedMimeTypes = [
                    'application/pdf',
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];

                $(form).find('input[type="file"]').each(function() {
                    const input = this;
                    const files = input.files;
                    const docType = $(input).data('doc-type');

                    if (files && files.length > 0) {
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];

                            // 1. Validar que el archivo existe
                            if (!file) {
                                showError('Please select a valid file.');
                                return false;
                            }

                            // 2. Validar tamaño (máximo 10MB)
                            const maxSize = 10 * 1024 * 1024; // 10MB en bytes
                            if (file.size > maxSize) {
                                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                showError(`File "${file.name}" is too large (${fileSizeMB}MB). Maximum size is 10MB.`);
                                return false;
                            }

                            // 3. Validar tamaño mínimo (no vacío)
                            if (file.size === 0) {
                                showError(`File "${file.name}" is empty. Please select a valid file.`);
                                return false;
                            }

                            // 4. Validar extensión del archivo
                            const fileName = file.name.toLowerCase();
                            const fileExtension = fileName.split('.').pop();
                            if (!allowedExtensions.includes(fileExtension)) {
                                showError(`File "${file.name}" has an invalid format. Allowed formats: PDF, JPG, PNG, DOC, DOCX.`);
                                return false;
                            }

                            // 5. Validar tipo MIME
                            if (!allowedMimeTypes.includes(file.type)) {
                                showError(`File "${file.name}" has an invalid type (${file.type}). Allowed formats: PDF, JPG, PNG, DOC, DOCX.`);
                                return false;
                            }

                            filesToUpload.push({
                                file: file,
                                docType: docType
                            });
                        }
                    }
                });

                // Si no hay archivos seleccionados, mostrar error
                if (filesToUpload.length === 0) {
                    if (window.toastr) {
                        toastr.warning('Please select at least one document to upload', 'Attention', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    } else {
                        alert('Please select at least one document to upload');
                    }
                    return false;
                }

                var $submitButton = $('button[type="submit"]');
                $submitButton.prop('disabled', true);

                // Mostrar progress bar
                $('#uploadProgress').show();
                $('#uploadStatus').text('0%');
                $('#uploadProgressBar').css('width', '0%');

                // Subir archivos uno por uno
                uploadFilesSequentially(filesToUpload, 0, form, $submitButton);

                return false;
            }
        });
    }

    // Manejar botón de eliminar documento
    $(document).on('click', '.btn-delete-single-doc', function(e) {
        e.preventDefault();
        const docType = $(this).data('doc-type');
        const $btn = $(this);

        if (confirm('Are you sure you want to delete this document?')) {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'https://webadminpruebas.a-alvarez.com/api/documents',
                type: 'POST',
                data: {
                    action: 'delete',
                    uid: $('#uid').val(),
                    doc_type: docType
                },
                success: function(response) {
                    if (response.status === 'success') {
                        loadDocumentStatus();
                        if (window.toastr) {
                            toastr.success('Document deleted successfully', 'Success', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    } else {
                        if (window.toastr) {
                            toastr.error('Error deleting document', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                        $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                    }
                },
                error: function() {
                    if (window.toastr) {
                        toastr.error('Error deleting document', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    }
                    $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                }
            });
        }
    });
});

/**
 * Subir archivos secuencialmente (uno por uno)
 */
function uploadFilesSequentially(files, index, form, $submitButton) {
    // Si ya subimos todos los archivos
    if (index >= files.length) {
        // Recargar estado y resetear formulario
        loadDocumentStatus();
        $(form)[0].reset();

        // Ocultar progress bar
        $('#uploadProgress').hide();

        // Habilitar botón
        $submitButton.prop('disabled', false);

        if (window.toastr) {
            toastr.success("All documents uploaded successfully!", "Success", {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
        } else {
            alert("All documents uploaded successfully!");
        }

        return;
    }

    const fileData = files[index];
    const formData = new FormData();

    // Agregar archivo y tipo
    formData.append('file[]', fileData.file);
    formData.append('document_types[]', fileData.docType);

    // Agregar datos del formulario
    formData.append('action', 'upload');
    formData.append('uid', $('#uid').val());
    formData.append('type', $('#type').val());

    // Calcular progreso
    const progress = Math.round(((index + 1) / files.length) * 100);
    $('#uploadStatus').text(progress + '%');
    $('#uploadProgressBar').css('width', progress + '%');
    $submitButton.html('<i class="fa fa-spinner fa-spin"></i> Uploading ' + (index + 1) + '/' + files.length + '...');

    $.ajax({
        url: 'https://webadminpruebas.a-alvarez.com/api/documents',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.status === 'success') {
                // Continuar con el siguiente archivo
                uploadFilesSequentially(files, index + 1, form, $submitButton);
            } else {
                // Error en este archivo
                $('#uploadProgress').hide();
                $submitButton.prop('disabled', false).html('Upload Documents');

                if (window.toastr) {
                    toastr.error('Error uploading "' + fileData.file.name + '": ' + (response.message || 'Unknown error'), 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                } else {
                    alert('Error uploading "' + fileData.file.name + '": ' + (response.message || 'Unknown error'));
                }
            }
        },
        error: function (xhr, status, error) {
            // Error en la petición
            $('#uploadProgress').hide();
            $submitButton.prop('disabled', false).html('Upload Documents');

            let errorMsg = "Error uploading document. Please try again.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            if (window.toastr) {
                toastr.error(errorMsg, "Error", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
            } else {
                alert(errorMsg);
            }
        }
    });
}

/**
 * Cargar estado de documentos desde el backend
 */
function loadDocumentStatus() {
    const uid = $('#uid').val();

    if (!uid) {
        console.error('UID not found');
        return;
    }

    $.ajax({
        url: 'https://webadminpruebas.a-alvarez.com/api/documents',
        type: 'POST',
        data: {
            action: 'validate',
            uid: uid
        },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                updateDocumentUI(response.data);

                // Actualizar contador
                updateDocumentCounter(response.data);

                // Si todos los documentos están completos, mostrar confirmación
                if (response.data.is_complete) {
                    $('#documentsConfirmation').removeClass('d-none');
                    $('#documentsContainer').addClass('d-none');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading document status:', error);
        }
    });
}

/**
 * Actualizar contador de documentos
 */
function updateDocumentCounter(data) {
    const uploadedCount = Object.keys(data.uploaded_documents || {}).length;
    const requiredCount = Object.keys(data.required_documents || {}).length;
    const missingCount = Object.keys(data.missing_documents || {}).length;

    console.log('Document counter:', { uploaded: uploadedCount, required: requiredCount, missing: missingCount });
    $('#documentCounter').text(uploadedCount + '/' + requiredCount + ' cargados');
}

/**
 * Actualizar UI con estado de documentos
 * Solo muestra los documentos faltantes (missing_documents)
 */
function updateDocumentUI(data) {
    console.log('=== updateDocumentUI ===');
    console.log('Required documents:', data.required_documents);
    console.log('Uploaded documents:', data.uploaded_documents);
    console.log('Missing documents:', data.missing_documents);

    // Ocultar todos los items primero
    $('.document-upload-item').hide();

    // Iterar solo por cada documento FALTANTE
    $.each(data.missing_documents || {}, function(docKey, docLabel) {
        const $item = $('[data-doc-type="' + docKey + '"]');

        if (!$item.length) {
            console.log('⚠️ Item no encontrado para docKey:', docKey);
            return;
        }

        console.log('✏️ Procesando documento faltante:', docKey, '→', docLabel);

        // Mostrar el item (estaba oculto)
        $item.show();

        // Remover badge anterior si existe
        $item.find('.badge').remove();

        // Asegurar que existe el input de archivo
        if ($item.find('input[type="file"]').length === 0) {
            const $input = $(`
                <input
                    type="file"
                    class="form-control document-file-input"
                    name="documents[${docKey}]"
                    data-doc-type="${docKey}"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                >
                <small class="text-muted d-block mt-1">
                    <i class="fa fa-info-circle"></i> PDF, JPG, PNG, DOC (max 10MB)
                </small>
            `);
            $item.append($input);
        }

        // Remover info de documento cargado si existe
        $item.find('.uploaded-doc-info').remove();
    });

    // Mostrar sección de documentos completados (si existen)
    const uploadedDocs = data.uploaded_documents || {};
    if (Object.keys(uploadedDocs).length > 0) {
        let uploadedHTML = '<div class="mt-4 pt-3 border-top"><h6 class="fw-semibold mb-3"><i class="fa fa-check-circle text-success me-2"></i>Documentos completados</h6>';

        $.each(uploadedDocs, function(docKey, docInfo) {
            const docLabel = data.required_documents[docKey] || docKey;
            uploadedHTML += `
                <div class="mb-2 document-completed-item" data-doc-type="${docKey}">
                    <div class="uploaded-doc-info p-3 bg-light-secondary border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div>
                                    <p class="mb-0 fw-semibold">${docLabel}</p>
                                    <small class="text-muted">${docInfo.file_name}</small>
                                    <small class="text-muted d-block">${docInfo.created_at}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="${docInfo.url}" class="btn btn-sm btn-primary" target="_blank" title="Descargar">
                                    <i class="fa fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-doc-type="${docKey}" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        uploadedHTML += '</div>';

        // Insertar antes del progress bar o al final del form
        if ($('#uploadProgress').length) {
            $(uploadedHTML).insertBefore('#uploadProgress');
        } else {
            $('#alsernet-documents').append(uploadedHTML);
        }
    }
}

/**
 * Mostrar mensaje de error
 */
function showError(message) {
    if (window.toastr) {
        toastr.error(message, "Error", {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-bottom-right",
            timeOut: 5000
        });
    } else {
        alert(message);
    }
}
