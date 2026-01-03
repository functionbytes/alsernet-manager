{{-- Additional Attachments Section --}}
@php
    $attachments = $document->getMedia('additional_attachments');
    $attachmentDetails = $document->getAdditionalAttachmentsWithDetails();
    $totalAttachments = $attachments->count();
@endphp

<div class="card mb-3" id="additionalAttachmentsSection">
    <div class="card-header p-3 bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold">Documentación adicional</h5>
                <p class="small mb-0 text-muted">Adjuntar documentos extra para el expediente</p>
            </div>
            <span class="badge bg-primary-subtle text-primary" id="attachmentCount">
                {{ $totalAttachments }} {{ $totalAttachments === 1 ? 'adjunto' : 'adjuntos' }}
            </span>
        </div>
    </div>
    <div class="card-body">
        {{-- Upload Form --}}
        <form id="uploadAdditionalAttachmentForm" class="mb-4">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label for="additionalFile" class="form-label fw-semibold small">
                        Seleccionar archivo
                    </label>
                    <input type="file"
                           class="form-control form-control-sm"
                           id="additionalFile"
                           name="file"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" @if(!auth()->user()->canActionDocumentComponent('additional-attachments', 'upload')) disabled @endif>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        PDF, JPG, PNG, DOC, DOCX (máx. 10MB)
                    </small>
                </div>
                <div class="col-12">
                    <label for="attachmentNotes" class="form-label fw-semibold small">
                        Notas <span class="text-muted fw-normal">(opcional)</span>
                    </label>
                    <input type="text"
                           class="form-control form-control-sm"
                           id="attachmentNotes"
                           name="notes"
                           placeholder="Ej: Factura proforma, Contrato firmado..."
                           maxlength="500" @if(!auth()->user()->canActionDocumentComponent('additional-attachments', 'upload')) disabled @endif>
                </div>
                <div class="col-12">
                    <button type="submit"
                            class="btn btn-primary w-100"
                            id="uploadAttachmentBtn" @if(!auth()->user()->canActionDocumentComponent('additional-attachments', 'upload')) disabled title="No tienes permiso para subir adjuntos" @endif>
                        <i class="fas fa-upload me-2"></i>Subir documento
                    </button>
                </div>
            </div>
        </form>

        <div class="border-top my-3"></div>

        {{-- Attachments List --}}
        <div id="attachmentsList">
            @if($totalAttachments > 0)
                <div class="attachments-scroll">
                    @foreach($attachmentDetails as $attachment)
                        <div class="attachment-item border-bottom py-2" data-attachment-id="{{ $attachment['id'] }}">
                            <div class="d-flex align-items-start gap-2">
                                {{-- File Icon --}}
                                @php
                                    $extension = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
                                    $iconConfig = [
                                        'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-danger'],
                                        'doc' => ['icon' => 'fa-file-word', 'color' => 'text-primary'],
                                        'docx' => ['icon' => 'fa-file-word', 'color' => 'text-primary'],
                                        'jpg' => ['icon' => 'fa-file-image', 'color' => 'text-success'],
                                        'jpeg' => ['icon' => 'fa-file-image', 'color' => 'text-success'],
                                        'png' => ['icon' => 'fa-file-image', 'color' => 'text-success'],
                                    ];
                                    $icon = $iconConfig[$extension] ?? ['icon' => 'fa-file', 'color' => 'text-secondary'];
                                @endphp
                                <i class="fas {{ $icon['icon'] }} {{ $icon['color'] }} mt-1" style="font-size: 1rem;"></i>

                                {{-- Content --}}
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="min-width-0">
                                            <small class="fw-semibold d-block text-dark text-truncate" title="{{ $attachment['name'] }}">
                                                {{ Str::limit($attachment['name'], 25) }}
                                            </small>
                                            @if($attachment['notes'])
                                                <small class="text-muted d-block fst-italic">
                                                    <i class="fas fa-sticky-note me-1"></i>{{ Str::limit($attachment['notes'], 30) }}
                                                </small>
                                            @endif
                                            <small class="text-muted">
                                                {{ number_format($attachment['size'] / 1024, 1) }} KB
                                                <span class="mx-1">•</span>
                                                {{ $attachment['uploaded_at'] }}
                                            </small>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="d-flex gap-1 flex-shrink-0 ms-2">
                                            <a href="{{ $attachment['url'] }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary attachment-action-btn"
                                               title="Ver documento"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->canActionDocumentComponent('additional-attachments', 'delete'))
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger attachment-action-btn delete-attachment-btn"
                                                        data-media-id="{{ $attachment['id'] }}"
                                                        title="Eliminar"
                                                        data-bs-toggle="tooltip">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger attachment-action-btn"
                                                        disabled
                                                        title="No tienes permiso para eliminar adjuntos"
                                                        data-bs-toggle="tooltip">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert bg-light-subtle py-3 px-3 mb-0" role="alert" id="noAttachmentsAlert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-circle-info text-muted me-2 mt-1" style="font-size: 0.9rem;"></i>
                        <div>
                            <small class="fw-semibold d-block">Sin documentos adicionales</small>
                            <small class="text-muted">Aún no hay documentos adicionales adjuntos.</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .attachments-scroll {
        overflow-y: auto;
        max-height: 250px;
    }

    @media (min-width: 576px) { .attachments-scroll { max-height: 280px; } }
    @media (min-width: 768px) { .attachments-scroll { max-height: 320px; } }
    @media (min-width: 992px) { .attachments-scroll { max-height: 350px; } }

    .attachments-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .attachments-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .attachments-scroll::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 2px;
    }

    .attachments-scroll::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    .attachment-item {
        transition: all 0.2s ease;
        border-color: #e9ecef !important;
    }

    .attachment-item:hover {
        background-color: #f8f9fa;
    }

    .attachment-item:last-child {
        border-bottom: none !important;
    }

    .attachment-action-btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
        line-height: 1;
    }

    .attachment-action-btn:hover {
        transform: scale(1.05);
    }

    #uploadAdditionalAttachmentForm .form-control {
        border-color: #dee2e6;
    }

    #uploadAdditionalAttachmentForm .form-control:focus {
        border-color: #90bb13;
        box-shadow: 0 0 0 0.2rem rgba(144, 187, 19, 0.15);
    }

    .min-width-0 {
        min-width: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const documentUid = '{{ $document->uid }}';

        // ===== Upload Additional Attachment =====
        $('#uploadAdditionalAttachmentForm').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $('#uploadAttachmentBtn');
            const $fileInput = $('#additionalFile');
            const file = $fileInput[0].files[0];

            if (!file) {
                toastr.warning('Por favor selecciona un archivo', 'Aviso', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
                return;
            }

            // Validate size (10MB)
            if (file.size > 10485760) {
                toastr.error('El archivo es demasiado grande. Máximo 10MB', 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
                return;
            }

            const formData = new FormData($form[0]);
            const notes = $('#attachmentNotes').val();
            if (notes) {
                formData.append('notes', notes);
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Subiendo...');

            $.ajax({
                url: "{{ route('documents.upload-attachment', $document->uid) }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Documento subido correctamente', 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        // Clear form
                        $fileInput.val('');
                        $('#attachmentNotes').val('');

                        // Refresh attachments list
                        refreshAttachmentsList();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al cargar el archivo';
                    toastr.error(message, 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Subir documento');
                }
            });
        });

        // ===== Refresh Attachments List =====
        function refreshAttachmentsList() {
            $.ajax({
                url: "{{ route('documents.attachments', $document->uid) }}",
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const attachments = response.attachments;
                        const total = response.total;
                        const $container = $('#attachmentsList');

                        // Update badge
                        $('#attachmentCount').text(total + (total === 1 ? ' adjunto' : ' adjuntos'));

                        if (attachments.length === 0) {
                            $container.html(`
                                <div class="alert bg-light-subtle py-3 px-3 mb-0" role="alert" id="noAttachmentsAlert">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-circle-info text-muted me-2 mt-1" style="font-size: 0.9rem;"></i>
                                        <div>
                                            <small class="fw-semibold d-block">Sin documentos adicionales</small>
                                            <small class="text-muted">Aún no hay documentos adicionales adjuntos.</small>
                                        </div>
                                    </div>
                                </div>
                            `);
                        } else {
                            let html = '<div class="attachments-scroll">';
                            attachments.forEach(function(attachment) {
                                const sizeKB = (attachment.size / 1024).toFixed(1);
                                const ext = attachment.name.split('.').pop().toLowerCase();
                                const iconMap = {
                                    'pdf': 'fa-file-pdf text-danger',
                                    'doc': 'fa-file-word text-primary',
                                    'docx': 'fa-file-word text-primary',
                                    'jpg': 'fa-file-image text-success',
                                    'jpeg': 'fa-file-image text-success',
                                    'png': 'fa-file-image text-success'
                                };
                                const iconClass = iconMap[ext] || 'fa-file text-secondary';

                                const notesHtml = attachment.notes ?
                                    `<small class="text-muted d-block fst-italic">
                                        <i class="fas fa-sticky-note me-1"></i>${attachment.notes.substring(0, 30)}${attachment.notes.length > 30 ? '...' : ''}
                                    </small>` : '';

                                html += `
                                    <div class="attachment-item border-bottom py-2" data-attachment-id="${attachment.id}">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fas ${iconClass} mt-1" style="font-size: 1rem;"></i>
                                            <div class="flex-grow-1 min-width-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="min-width-0">
                                                        <small class="fw-semibold d-block text-dark text-truncate" title="${attachment.name}">
                                                            ${attachment.name.length > 25 ? attachment.name.substring(0, 25) + '...' : attachment.name}
                                                        </small>
                                                        ${notesHtml}
                                                        <small class="text-muted">
                                                            ${sizeKB} KB
                                                            <span class="mx-1">•</span>
                                                            ${attachment.uploaded_at}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex gap-1 flex-shrink-0 ms-2">
                                                        <a href="${attachment.url}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-primary attachment-action-btn"
                                                           title="Ver documento">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger attachment-action-btn delete-attachment-btn"
                                                                data-media-id="${attachment.id}"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            $container.html(html);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error refreshing attachments:', xhr);
                }
            });
        }

        // ===== Delete Attachment =====
        $(document).on('click', '.delete-attachment-btn', function() {
            const $btn = $(this);
            const mediaId = $btn.data('media-id');

            if (!confirm('¿Estás seguro de que deseas eliminar este adjunto?')) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('documents.delete-attachment', $document->uid) }}",
                method: 'POST',
                data: {
                    media_id: mediaId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Adjunto eliminado', 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        // Refresh list
                        refreshAttachmentsList();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al eliminar el archivo';
                    toastr.error(message, 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                }
            });
        });
    });
</script>
@endpush
