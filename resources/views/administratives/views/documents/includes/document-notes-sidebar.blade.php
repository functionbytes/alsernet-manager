<!-- Document Notes Sidebar (Col-3) -->
<div class="card">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Notas</h5>
        <p class="small mb-0 text-muted">Anotaciones y comentarios sobre el documento</p>
    </div>
    <div class="card-body">
        <!-- Notes List -->
        @if(!$document || $document->notes->isEmpty())
            <div class="alert bg-light-subtle py-3 px-3 mb-0" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-circle-info text-black me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block">No hay notas aún.</small>
                        <small class="text-muted">No hay notas para este documento aún registradass.</small>
                    </div>
                </div>
            </div>
        @else
            <div class="notes-scroll scrollable mb-3">
                @foreach($document->notes->sortByDesc('created_at') as $note)
                    <div class="note-item border-bottom px-2 px-md-0 py-2" data-note-id="{{ $note->id }}">
                        <!-- Note Author & Date -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <div class="d-flex align-items-center gap-2 min-width-0">
                                <div class="avatar-initials flex-shrink-0"
                                     style="background-color: #f6f7f9;"
                                     data-bs-toggle="tooltip" data-bs-title="{{ $note->author?->full_name ?? 'Sistema' }}">
                                    {{ strtoupper(substr($note->author->firstname ?? '', 0, 1) . substr($note->author->lastname ?? '', 0, 1)) }}
                                </div>
                                <div class="min-width-0">
                                    <small class="fw-semibold d-block text-truncate">
                                        @if($note->author)
                                            {{ $note->author->firstname }} {{ substr($note->author->lastname ?? '', 0, 1) }}.
                                        @else
                                            Sistema
                                        @endif
                                    </small>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $note->created_at->format('d M Y H:i') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                @if(auth()->check() && $note->created_by === auth()->user()->id)
                                    <div class="note-actions-editable">
                                        <button class="btn-note-edit" data-note-id="{{ $note->id }}" data-bs-toggle="tooltip" data-bs-title="Editar nota">
                                            <i class="fas fa-pen-to-square text-black fs-2"></i>
                                        </button>
                                        <button class="btn-note-delete" data-note-id="{{ $note->id }}" data-bs-toggle="tooltip" data-bs-title="Eliminar nota">
                                            <i class="fas fa-trash text-black fs-2"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Note Content -->
                        <div class="note-content mt-3">
                            <p class="text-dark mb-0 small text-truncate-3"  data-full-text="{{ $note->content }}">
                                {{ $note->content }}
                            </p>
                        </div>

                        <!-- Edit Form (Hidden by default) -->
                        <div class="note-edit-form d-none mt-2" style="display: none;">
                            <textarea class="form-control form-control-sm note-edit-textarea" rows="2" style="resize: none; font-size: 0.85rem;">{{ $note->content }}</textarea>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn-note-save btn-sm btn btn-success" data-note-id="{{ $note->id }}">
                                    <i class="fas fa-check me-1"></i> Guardar
                                </button>
                                <button class="btn-note-cancel btn-sm btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="border-top my-3"></div>

        <!-- Add Note Form -->
        <form id="addNoteForm">
            @csrf
            <div class="mb-2">
                <textarea class="form-control form-control-sm" id="noteContent" name="content" rows="2"
                          placeholder="Escribe una nota..." required style="resize: none;"></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary  w-100">
                    <i class="fas fa-plus me-1"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const documentUid = '{{ $document->uid }}';

    // Function to rebind event listeners after adding new notes
    function rebindNoteEventListeners() {
        // Edit Note
        document.querySelectorAll('.btn-note-edit').forEach(btn => {
            btn.removeEventListener('click', editNoteHandler);
            btn.addEventListener('click', editNoteHandler);
        });

        // Delete Note
        document.querySelectorAll('.btn-note-delete').forEach(btn => {
            btn.removeEventListener('click', deleteNoteHandler);
            btn.addEventListener('click', deleteNoteHandler);
        });

        // Save Note
        document.querySelectorAll('.btn-note-save').forEach(btn => {
            btn.removeEventListener('click', saveNoteHandler);
            btn.addEventListener('click', saveNoteHandler);
        });

        // Cancel Note Edit
        document.querySelectorAll('.btn-note-cancel').forEach(btn => {
            btn.removeEventListener('click', cancelNoteHandler);
            btn.addEventListener('click', cancelNoteHandler);
        });
    }

    // Edit Note Handler
    function editNoteHandler(e) {
        const noteId = this.dataset.noteId;
        const noteItem = document.querySelector(`[data-note-id="${noteId}"]`);
        const noteContent = noteItem.querySelector('.note-content');
        const editForm = noteItem.querySelector('.note-edit-form');

        noteContent.style.display = 'none';
        editForm.classList.remove('d-none');
        editForm.style.display = 'block';
        editForm.querySelector('.note-edit-textarea').focus();
    }

    // Cancel Edit Handler
    function cancelNoteHandler(e) {
        e.preventDefault();
        const noteItem = this.closest('.note-item');
        const noteContent = noteItem.querySelector('.note-content');
        const editForm = noteItem.querySelector('.note-edit-form');

        editForm.classList.add('d-none');
        editForm.style.display = 'none';
        noteContent.style.display = 'block';
    }

    // Save Note Handler
    function saveNoteHandler(e) {
        e.preventDefault();
        const noteId = this.dataset.noteId;
        const noteItem = document.querySelector(`[data-note-id="${noteId}"]`);
        const editForm = noteItem.querySelector('.note-edit-form');
        const textarea = editForm.querySelector('.note-edit-textarea');
        const content = textarea.value;

        if (!content.trim()) {
            toastr.warning('La nota no puede estar vacía', 'Atención', {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
            return;
        }

        const $btn = this;
        $btn.disabled = true;
        $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';

        fetch(`/administrative/documents/manage/${documentUid}/update-note/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const noteContent = noteItem.querySelector('.note-content');
                noteContent.querySelector('p').textContent = content;
                editForm.classList.add('d-none');
                editForm.style.display = 'none';
                noteContent.style.display = 'block';

                toastr.success('Nota actualizada correctamente', 'Éxito', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
            } else {
                toastr.error('Error: ' + (data.message || 'No se pudo actualizar la nota'), 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al actualizar la nota', 'Error', {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
        })
        .finally(() => {
            $btn.disabled = false;
            $btn.innerHTML = '<i class="fas fa-check me-1"></i> Guardar';
        });
    }

    // Delete Note Handler
    function deleteNoteHandler(e) {
        e.preventDefault();
        const noteId = this.dataset.noteId;

        if (confirm('¿Estás seguro de que deseas eliminar esta nota?')) {
            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/administrative/documents/manage/${documentUid}/delete-note/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const noteItem = document.querySelector(`[data-note-id="${noteId}"]`);
                    noteItem.remove();

                    toastr.success('Nota eliminada correctamente', 'Éxito', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });

                    // Reload if no notes left
                    if (document.querySelectorAll('.note-item').length === 0) {
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    toastr.error('Error: ' + (data.message || 'No se pudo eliminar la nota'), 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-trash-alt text-danger"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al eliminar la nota', 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
                $btn.disabled = false;
                $btn.innerHTML = '<i class="fas fa-trash-alt text-danger"></i>';
            });
        }
    }

    // Initial binding on page load
    document.addEventListener('DOMContentLoaded', function() {
        rebindNoteEventListeners();
    });

    // Add New Note
    document.getElementById('addNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const content = document.getElementById('noteContent').value;

        if (!content.trim()) {
            toastr.warning('La nota no puede estar vacía', 'Atención', {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
            return;
        }

        const $btn = this.querySelector('button[type="submit"]');
        $btn.disabled = true;
        $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

        fetch(`/administrative/documents/manage/${documentUid}/add-note`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add note to DOM without reloading
                const notesList = document.querySelector('.notes-scroll');
                const noteTemplate = document.createElement('div');
                noteTemplate.className = 'note-item border-bottom px-2 px-md-0 py-2';
                noteTemplate.setAttribute('data-note-id', data.note.id);

                const userInitials = (data.note.author?.firstname || 'S').charAt(0).toUpperCase() +
                                    (data.note.author?.lastname || '').charAt(0).toUpperCase();

                noteTemplate.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <div class="d-flex align-items-center gap-2 min-width-0">
                            <div class="avatar-initials flex-shrink-0" style="background-color: #f6f7f9;">
                                ${userInitials}
                            </div>
                            <div class="min-width-0">
                                <small class="fw-semibold d-block text-truncate">
                                    ${data.note.author?.firstname || 'Sistema'} ${(data.note.author?.lastname || '').charAt(0)}.
                                </small>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">
                                    ${new Date().toLocaleDateString('es-ES', {year: 'numeric', month: 'short', day: 'numeric'})}
                                    ${new Date().toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}
                                </small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <div class="note-actions-editable">
                                <button class="btn-note-edit" data-note-id="${data.note.id}" data-bs-toggle="tooltip" data-bs-title="Editar nota">
                                    <i class="fas fa-pen-to-square text-black fs-2"></i>
                                </button>
                                <button class="btn-note-delete" data-note-id="${data.note.id}" data-bs-toggle="tooltip" data-bs-title="Eliminar nota">
                                    <i class="fas fa-trash text-black fs-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="note-content mt-3">
                        <p class="text-dark mb-0 small text-truncate-3" data-full-text="${data.note.content}">
                            ${data.note.content}
                        </p>
                    </div>
                    <div class="note-edit-form d-none mt-2" style="display: none;">
                        <textarea class="form-control form-control-sm note-edit-textarea" rows="2" style="resize: none; font-size: 0.85rem;">${data.note.content}</textarea>
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn-note-save btn-sm btn btn-success" data-note-id="${data.note.id}">
                                <i class="fas fa-check me-1"></i> Guardar
                            </button>
                            <button class="btn-note-cancel btn-sm btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                        </div>
                    </div>
                `;

                // If notes list doesn't exist, create it (first note)
                if (!notesList) {
                    const noNotesAlert = document.querySelector('[role="alert"]');
                    if (noNotesAlert) {
                        noNotesAlert.parentElement.innerHTML = `
                            <div class="notes-scroll scrollable mb-3">
                                <ul class="timeline-widget mb-0 position-relative" style="list-style: none; padding-left: 0;">
                                    ${noteTemplate.outerHTML}
                                </ul>
                            </div>
                        `;
                    }
                } else {
                    notesList.querySelector('ul').insertAdjacentHTML('afterbegin', noteTemplate.outerHTML);
                }

                // Rebind event listeners for new note
                rebindNoteEventListeners();

                // Clear form
                document.getElementById('noteContent').value = '';

                toastr.success('Nota guardada correctamente', 'Éxito', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
            } else {
                toastr.error('Error: ' + (data.message || 'No se pudo guardar la nota'), 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-bottom-right"
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al agregar la nota', 'Error', {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-bottom-right"
            });
        })
        .finally(() => {
            $btn.disabled = false;
            $btn.innerHTML = '<i class="fas fa-plus me-1"></i> Agregar';
        });
    });
</script>
@endpush

<style>
    .notes-scroll {
        overflow-y: auto;
        border-top: 1px solid #e9ecef;
        height: 250px;
    }

    @media (min-width: 576px) {
        .notes-scroll {
            height: 300px;
        }
    }

    @media (min-width: 768px) {
        .notes-scroll {
            height: 350px;
        }
    }

    @media (min-width: 992px) {
        .notes-scroll {
            height: 400px;
        }
    }

    .notes-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .notes-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .notes-scroll::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 2px;
    }

    .notes-scroll::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    .note-item {
        transition: all 0.2s ease;
        border-color: #e9ecef !important;
    }

    .note-item:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6 !important;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .text-truncate-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .min-width-0 {
        min-width: 0;
    }

    .avatar-initials {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        flex-shrink: 0;
    }

    .note-actions-editable {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn-note-edit,
    .btn-note-delete,
    .btn-note-save,
    .btn-note-cancel {
        background: none;
        border: none;
        padding: 0.25rem 0.35rem;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-note-edit,
    .btn-note-delete {
        opacity: 0.65;
        padding: 0.3rem 0.4rem;
    }

    .btn-note-edit:hover {
        opacity: 1;
        transform: scale(1.15);
    }

    .btn-note-delete:hover {
        opacity: 1;
        transform: scale(1.15);
    }

    .note-edit-form textarea {
        border-color: #0d6efd;
    }

    .note-edit-form .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    #addNoteForm .form-control {
        border-color: #dee2e6;
        font-size: 0.875rem;
    }

    #addNoteForm .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    #addNoteForm .form-label {
        color: #495057;
        font-size: 0.8rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 575px) {
        .note-item {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .note-actions {
            gap: 0.15rem;
        }

        .btn-note-edit,
        .btn-note-delete {
            padding: 0.2rem 0.3rem;
            font-size: 0.8rem;
        }
    }
</style>
