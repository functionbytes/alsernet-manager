/**
 * Formulario de documentos - Control de validación y envío
 * Maneja la lógica de habilitación/deshabilitación del botón basada en:
 * 1. Aceptación de términos (checkbox condition)
 * 2. Completación de reCAPTCHA
 */

$(document).ready(function () {
    // Solo ejecutar si existe el contenedor del formulario
    if (!$('#alsernet-documents').length) {
        return;
    }

    // Cargar estado de documentos al inicio
    loadDocumentStatus();

    // ============================================
    // CONTROL DEL BOTÓN SUBMIT
    // ============================================

    /**
     * Verificar y actualizar estado del botón de submit
     * El botón solo se habilita si AMBAS condiciones se cumplen:
     * 1. Checkbox de términos marcado
     * 2. reCAPTCHA completado
     */
    function updateSubmitButtonState() {
        const $submitBtn = $('button[type="submit"]');
        const $validationHelp = $('#submitValidationHelp');
        const isConditionChecked = $('#condition').is(':checked');
        const isRecaptchaValid = grecaptcha && grecaptcha.getResponse() ? grecaptcha.getResponse().length > 0 : false;

        console.log('🔍 Estado de validación:', {
            conditionChecked: isConditionChecked,
            recaptchaValid: isRecaptchaValid
        });

        // Habilitar solo si AMBAS condiciones se cumplen
        if (isConditionChecked && isRecaptchaValid) {
            $submitBtn
                .prop('disabled', false)
                .attr('aria-disabled', 'false')
                .css({
                    'opacity': '1',
                    'cursor': 'pointer'
                });
            $validationHelp.addClass('d-none');
            console.log('✅ Botón HABILITADO');
        } else {
            $submitBtn
                .prop('disabled', true)
                .attr('aria-disabled', 'true')
                .css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed'
                });
            $validationHelp.removeClass('d-none');
            console.log('❌ Botón DESHABILITADO');
        }
    }

    // Escuchar cambios en el checkbox de condiciones
    $('#condition').on('change', function() {
        console.log('✏️ Checkbox de términos:', $(this).is(':checked'));
        updateSubmitButtonState();
    });

    // Callback para reCAPTCHA completado/verificado
    window.onRecaptchaChange = function() {
        console.log('✅ reCAPTCHA completado/verificado');
        updateSubmitButtonState();
    };

    // Callback para cuando reCAPTCHA expira
    window.onRecaptchaExpire = function() {
        console.log('⚠️ reCAPTCHA expirado - requiere verificación nuevamente');
        updateSubmitButtonState();
    };

    // Verificación periódica del reCAPTCHA (primeros 5 segundos)
    // Por si el reCAPTCHA carga más lentamente
    let checkCount = 0;
    const recaptchaCheckInterval = setInterval(function() {
        checkCount++;
        try {
            if (grecaptcha && grecaptcha.getResponse && grecaptcha.getResponse().length > 0) {
                console.log('✅ reCAPTCHA detectado como completado');
                updateSubmitButtonState();
                clearInterval(recaptchaCheckInterval);
            }
        } catch (e) {
            console.debug('⏳ reCAPTCHA no está listo aún');
        }

        if (checkCount > 10) {
            clearInterval(recaptchaCheckInterval);
        }
    }, 500);

    // ============================================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ============================================

    if ($.fn.validate) {
        $("#alsernet-documents").validate({
            ignore: ".ignore",
            rules: {
                condition: {
                    required: true
                }
            },
            messages: {
                condition: {
                    required: "{l s='You must accept the terms and conditions' mod='alsernetforms'}"
                }
            },
            submitHandler: function (form) {
                // Recopilar todos los archivos a subir
                const filesToUpload = [];

                // Extensiones y MIME types permitidos
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                const allowedMimeTypes = [
                    'application/pdf',
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];

                // Iterar por cada input de archivo en el formulario
                $(form).find('input[type="file"]').each(function() {
                    const input = this;
                    const files = input.files;
                    const docType = $(input).data('doc-type');

                    if (files && files.length > 0) {
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];

                            // 1. Validar que el archivo existe
                            if (!file) {
                                showError("{l s='Please select a valid file' mod='alsernetforms'}");
                                return false;
                            }

                            // 2. Validar tamaño máximo (10MB)
                            const maxSize = 10 * 1024 * 1024;
                            if (file.size > maxSize) {
                                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                showError(`"{l s='File' mod='alsernetforms'}" "${file.name}" "{l s='is too large' mod='alsernetforms'}" (${fileSizeMB}MB). "{l s='Maximum size is' mod='alsernetforms'}" 10MB.`);
                                return false;
                            }

                            // 3. Validar que el archivo no esté vacío
                            if (file.size === 0) {
                                showError(`"${file.name}" "{l s='is empty. Please select a valid file' mod='alsernetforms'}"`);
                                return false;
                            }

                            // 4. Validar extensión del archivo
                            const fileName = file.name.toLowerCase();
                            const fileExtension = fileName.split('.').pop();
                            if (!allowedExtensions.includes(fileExtension)) {
                                showError(`"${file.name}" "{l s='has an invalid format. Allowed formats: PDF, JPG, PNG, DOC, DOCX' mod='alsernetforms'}"`);
                                return false;
                            }

                            // 5. Validar MIME type
                            if (!allowedMimeTypes.includes(file.type)) {
                                showError(`"${file.name}" "{l s='has an invalid type' mod='alsernetforms'}" (${file.type}). "{l s='Allowed formats: PDF, JPG, PNG, DOC, DOCX' mod='alsernetforms'}"`);
                                return false;
                            }

                            filesToUpload.push({
                                file: file,
                                docType: docType
                            });
                        }
                    }
                });

                // Si no hay archivos, mostrar advertencia
                if (filesToUpload.length === 0) {
                    if (window.toastr) {
                        toastr.warning("{l s='Please select at least one document to upload' mod='alsernetforms'}", "{l s='Attention' mod='alsernetforms'}", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                    } else {
                        alert("{l s='Please select at least one document to upload' mod='alsernetforms'}");
                    }
                    return false;
                }

                var $submitButton = $('button[type="submit"]');
                $submitButton.prop('disabled', true);

                // Mostrar progress bar
                $('#uploadProgress').show();
                $('#uploadStatus').text('0%');
                $('#uploadProgressBar').css('width', '0%');

                // Subir archivos secuencialmente
                uploadFilesSequentially(filesToUpload, 0, form, $submitButton);

                return false;
            }
        });
    }

    // ============================================
    // MANEJO DE ELIMINACIÓN DE DOCUMENTOS
    // ============================================

    $(document).on('click', '.btn-delete-single-doc', function(e) {
        e.preventDefault();
        const docType = $(this).data('doc-type');
        const $btn = $(this);

        if (confirm("{l s='Are you sure you want to delete this document?' mod='alsernetforms'}")) {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '/modules/alsernetforms/controllers/routes.php',
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
                            toastr.success("{l s='Document deleted successfully' mod='alsernetforms'}", "{l s='Success' mod='alsernetforms'}", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                        }
                    } else {
                        if (window.toastr) {
                            toastr.error("{l s='Error deleting document' mod='alsernetforms'}", "{l s='Error' mod='alsernetforms'}", {
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
                        toastr.error("{l s='Error deleting document' mod='alsernetforms'}", "{l s='Error' mod='alsernetforms'}", {
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
    // Si ya se completó la subida de todos los archivos
    if (index >= files.length) {
        loadDocumentStatus();
        $(form)[0].reset();
        $('#uploadProgress').hide();
        $submitButton.prop('disabled', false);

        if (window.toastr) {
            toastr.success("{l s='All documents uploaded successfully!' mod='alsernetforms'}", "{l s='Success' mod='alsernetforms'}", {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
        } else {
            alert("{l s='All documents uploaded successfully!' mod='alsernetforms'}");
        }
        return;
    }

    const fileData = files[index];
    const formData = new FormData();

    // Preparar datos del archivo
    formData.append('file[]', fileData.file);
    formData.append('document_types[]', fileData.docType);
    formData.append('action', 'upload');
    formData.append('uid', $('#uid').val());
    formData.append('type', $('#type').val());

    // Calcular y mostrar progreso
    const progress = Math.round(((index + 1) / files.length) * 100);
    $('#uploadStatus').text(progress + '%');
    $('#uploadProgressBar').css('width', progress + '%');
    $submitButton.html('<i class="fa fa-spinner fa-spin"></i> {l s="Uploading" mod="alsernetforms"} ' + (index + 1) + '/' + files.length + '...');

    $.ajax({
        url: '/modules/alsernetforms/controllers/routes.php',
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
                $submitButton.prop('disabled', false).html("{l s='Upload Document' mod='alsernetforms'}");

                if (window.toastr) {
                    toastr.error("{l s='Error uploading' mod='alsernetforms'} \"" + fileData.file.name + "\": " + (response.message || "{l s='Unknown error' mod='alsernetforms'}"), "{l s='Error' mod='alsernetforms'}", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                } else {
                    alert("{l s='Error uploading' mod='alsernetforms'} \"" + fileData.file.name + "\": " + (response.message || "{l s='Unknown error' mod='alsernetforms'}"));
                }
            }
        },
        error: function (xhr, status, error) {
            $('#uploadProgress').hide();
            $submitButton.prop('disabled', false).html("{l s='Upload Document' mod='alsernetforms'}");

            let errorMsg = "{l s='Error uploading document. Please try again.' mod='alsernetforms'}";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            if (window.toastr) {
                toastr.error(errorMsg, "{l s='Error' mod='alsernetforms'}", {
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
        console.error('UID no encontrado');
        return;
    }

    $.ajax({
        url: '/modules/alsernetforms/controllers/routes.php',
        type: 'POST',
        data: {
            action: 'validate',
            uid: uid
        },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                updateDocumentUI(response.data);
                updateDocumentCounter(response.data);

                // Si todos los documentos están completos, mostrar confirmación
                if (response.data.is_complete) {
                    $('#documentsConfirmation').removeClass('d-none');
                    $('#documentsContainer').addClass('d-none');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando estado de documentos:', error);
        }
    });
}

/**
 * Actualizar contador de documentos
 */
function updateDocumentCounter(data) {
    const uploadedCount = Object.keys(data.uploaded_documents || {}).length;
    const totalCount = Object.keys(data.required_documents || {}).length;

    $('#documentCounter').text(uploadedCount + '/' + totalCount + ' {l s="cargados" mod="alsernetforms"}');
}

/**
 * Actualizar UI con estado de documentos
 * Solo muestra los documentos faltantes (missing_documents)
 */
function updateDocumentUI(data) {
    console.log('=== Actualizando UI de documentos ===');
    console.log('Documentos requeridos:', data.required_documents);
    console.log('Documentos cargados:', data.uploaded_documents);
    console.log('Documentos faltantes:', data.missing_documents);

    // Ocultar todos los items de carga primero
    $('.document-upload-item').hide();

    // Iterar solo por documentos FALTANTES
    $.each(data.missing_documents || {}, function(docKey, docLabel) {
        const $item = $('[data-doc-type="' + docKey + '"]');

        if (!$item.length) {
            console.log('⚠️ Elemento no encontrado para docKey:', docKey);
            return;
        }

        console.log('✏️ Procesando documento faltante:', docKey, 'Etiqueta:', docLabel);

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
                    <i class="fa fa-info-circle"></i> {l s='PDF, JPG, PNG, DOC (max 10MB)' mod='alsernetforms'}
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
        // Remover sección anterior si existe
        $('.uploaded-documents-section').remove();

        let uploadedHTML = '<div class="mt-4 pt-3 border-top uploaded-documents-section"><h6 class="fw-semibold mb-3"><i class="fa fa-check-circle text-success me-2"></i>{l s="Completed documents" mod="alsernetforms"}</h6>';

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
                                <a href="${docInfo.url}" class="btn btn-sm btn-primary" target="_blank" title="{l s='Download' mod='alsernetforms'}">
                                    <i class="fa fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-single-doc" data-doc-type="${docKey}" title="{l s='Delete' mod='alsernetforms'}">
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
        toastr.error(message, "{l s='Error' mod='alsernetforms'}", {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-bottom-right",
            timeOut: 5000
        });
    } else {
        alert(message);
    }
}
