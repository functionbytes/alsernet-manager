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

        @if(auth()->user()->canDocument('view-document-upload'))
            @include('documents::documents.documents.components.management.modals.confirm-missing-docs')
        @endif

        @if(auth()->user()->canDocument('view-document-upload'))
            @include('documents::documents.documents.components.files.modals.confirm-delete')
        @endif

    </div>

@endsection

{{-- Global Functions Used Across Components --}}
@push('scripts')
    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


    </script>
@endpush
