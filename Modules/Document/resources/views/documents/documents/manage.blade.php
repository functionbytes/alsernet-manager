 @extends('layouts.theme')

@section('title', 'Gestionar Documento')

@section('content')

    @include('theme.components.card', ['title' => 'Gestionar Documento'])

    @include('theme.components.alerts')

    <div class="row">
        <div class="col-lg-4">
            <!-- Email Actions - Sidebar (Permission-Controlled) -->
            @if(auth()->user()->canDocument('email-actions'))
                @include('documents::documents.documents.components.email.actions', [
                    'document' => $document,
                    'documentConfig' => $documentConfig
                ])
            @endif

            <!-- Workflow Multi-Etapa (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-validation-workflow'))
                @include('documents::documents.documents.components.validation.validation-workflow-sidebar')
            @endif

            <!-- Document Notes (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-document-notes'))
                @include('documents::documents.documents.components.notes.document-notes-sidebar')
            @endif

            <!-- Action History (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-action-history'))
                <div id="actionHistoryContainer">
                    @include('documents::documents.documents.components.management.action-history')
                </div>
            @endif

            <!-- Email History (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-email-history'))
                @include('documents::documents.documents.components.email.history')
            @endif

            <!-- Status Timeline (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-status-timeline'))
                @include('documents::documents.documents.components.management.status-timeline')
            @endif

        </div>

        <div class="col-lg-8">

            <!-- Products List (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-products-list'))
                @include('documents::documents.documents.components.management.products-list')
            @endif

            <!-- Order Details (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-document-details'))
                @include('documents::documents.documents.components.management.order-details')
            @endif

            <!-- Customer Information (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-customer-information'))
                @include('documents::documents.documents.components.management.customer-information')
            @endif

            <!-- Document Configuration (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-document-management'))
                @include('documents::documents.documents.components.management.document-management', [
                    'document' => $document,
                    'statuses' => $statuses,
                    'documentSources' => $documentSources,
                    'documentLoads' => $documentLoads,
                    'documentSyncs' => $documentSyncs,
                    'uploadTypes' => $uploadTypes
                ])
            @endif

            <!-- Upload Section (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-document-upload'))
                    @include('documents::documents.documents.components.files.upload-section', [
                        'document' => $document,
                        'requiredDocuments' => $requiredDocuments,
                        'uploadedDocs' => $uploadedDocs,
                        'missingDocs' => $missingDocs,
                        'allUploaded' => $allUploaded,
                    ])
            @endif

            <!-- Additional Attachments Section (Permission-Controlled) -->
            @if(auth()->user()->canDocument('view-additional-attachments'))
                @include('documents::documents.documents.components.files.additional-attachments')
            @endif

        </div>

    </div>

    {{-- ========================================================================
         MODALES DE UTILIDAD (Confirmaciones)
         ======================================================================== --}}

    <!-- Confirm Missing Documents Modal (Permission-Controlled) -->
    @if(auth()->user()->canDocument('view-document-upload'))
        @include('documents::documents.documents.components.management.modals.confirm-missing-docs')
    @endif

    <!-- Confirm Delete Document Modal (Permission-Controlled) -->
    @if(auth()->user()->canDocument('view-document-upload'))
        @include('documents::documents.documents.components.files.modals.confirm-delete')
    @endif

    </div>

@endsection

{{-- Global Functions Used Across Components --}}
@push('scripts')
    <script>
        const documentUid = '{{ $document->uid }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Document Configuration Form Handler
        let configFormData = {};
        let $configSubmitBtn = null;
        let previousStatusId = $('#status_id').val();

        // Status to Email Configuration Mapping
        const statusEmailMap = {
            'approved': {
                show: true,
                label: 'Documento aprobado',
                description: 'Se enviará notificación al cliente informando que su documento ha sido aprobado'
            },
            'rejected': {
                show: true,
                label: 'Documento rechazado',
                description: 'Se enviará notificación al cliente informando que su documento ha sido rechazado'
            },
            'pending': {
                show: false
            }
        };

        // Handle form submission
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
                upload_id: $('#upload_id').val(),
                requires_financing: $('#requires_financing').val()
            };
            $configSubmitBtn = $form.find('button[type="submit"]');

            // Check if status changed and if email notification should be shown
            const statusChanged = selectedStatusId && (selectedStatusId !== previousStatusId);
            const emailConfig = statusEmailMap[selectedStatusKey];

            if (statusChanged && emailConfig && emailConfig.show) {
                // Show email notification section
                $('#emailNotificationSection').show();
                $('#emailTypeDescription').html(
                    `<i class="fas fa-info-circle me-1"></i><strong>${emailConfig.label}:</strong> ${emailConfig.description}`
                );
                $('#sendEmailOnStatusChange').prop('checked', true);
            } else {
                // Hide email notification section
                $('#emailNotificationSection').hide();
            }

            // Open confirmation modal
            const modal = new bootstrap.Modal(document.getElementById('confirmConfigurationModal'));
            modal.show();
        });

        // Handle confirmation button
        $(document).on('click', '.confirm-configuration-btn', function() {
            const $btn = $(this);
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');

            const sendEmail = $('#sendEmailOnStatusChange').is(':checked') ? 1 : 0;

            $.ajax({
                url: '/documents/manage/' + documentUid + '/update-configuration',
                type: 'POST',
                data: {
                    ...configFormData,
                    send_email_notification: sendEmail
                },
                success: function(response) {
                    // Show success message
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> Configuración guardada correctamente
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;

                    // Insert alert at top of page
                    $('main').prepend(alertHtml);

                    // Update previous status ID
                    previousStatusId = configFormData.status_id;

                    // Close modal and reset button
                    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmConfigurationModal'));
                    if (modal) modal.hide();

                    // Reset button
                    $btn.prop('disabled', false).html(originalText);

                    // Reload the page after 1 second
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Error al guardar la configuración';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;

                    // Insert alert at top of page
                    $('main').prepend(alertHtml);

                    // Reset button
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

    </script>
@endpush
