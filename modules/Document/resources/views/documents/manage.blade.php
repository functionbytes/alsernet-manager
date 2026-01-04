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

            <!-- Action History (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('action-history'))
                <div id="actionHistoryContainer">
                    @include('documents::documents.components.management.action-history')
                </div>
            @endif

            <!-- Email History (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('email-history'))
                @include('documents::documents.components.email.email-history')
            @endif

            <!-- Status Timeline (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('status-timeline'))
                @include('documents::documents.components.management.status-timeline')
            @endif

        </div>

        <div class="col-lg-8">

            <!-- Products List (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('products-list'))
                @include('documents::documents.components.management.products-list')
            @endif

            <!-- Order Details (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('order-details'))
                @include('documents::documents.components.management.order-details')
            @endif

            <!-- Customer Information (Permission-Controlled) -->
            @if(auth()->user()->canViewDocumentComponent('customer-information'))
                @include('documents::documents.components.management.customer-information')
            @endif

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
                    @include('documents::documents.components.files.upload-section', [
                        'document' => $document,
                        'requiredDocuments' => $requiredDocuments,
                        'uploadedDocs' => $uploadedDocs,
                        'missingDocs' => $missingDocs,
                        'allUploaded' => $allUploaded,
                    ])
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

    <!-- Confirm Missing Documents Modal (Permission-Controlled) -->
    @if(auth()->user()->canViewDocumentComponent('document-upload'))
        @include('documents::documents.components.management.modals.confirm-missing-docs')
    @endif

    <!-- Confirm Delete Document Modal (Permission-Controlled) -->
    @if(auth()->user()->canViewDocumentComponent('document-upload'))
        @include('documents::documents.components.files.modals.confirm-delete')
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

    </script>
@endpush
