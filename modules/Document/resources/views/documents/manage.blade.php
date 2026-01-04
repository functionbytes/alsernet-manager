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

            <!-- Products List -->
            @include('documents::documents.components.management.products-list')

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

{{-- Global Functions Used Across Components --}}
@push('scripts')
    <script>
        const documentUid = '{{ $document->uid }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /**
         * Recarga completamente la sección de carga de documentos vía AJAX
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
                        const $container = $('#uploadSectionContainer');
                        $container.html(response.html);
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
         * Actualiza la lista de documentos faltantes
         */
        function updateMissingDocumentsUI(response) {
            const missingDocs = response.missing_documents || {};
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
         * Función auxiliar para escapar HTML (usada en múltiples lugares)
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // ===== Global Event Listeners =====
        // Llamar a updateDocumentState después de cargar documentos exitosamente
        $(document).on('ajax-upload-success', function() {
            updateDocumentState();
        });

        // También actualizar cuando se elimina un documento
        $(document).on('document-deleted', function() {
            updateDocumentState();
        });
    </script>
@endpush
