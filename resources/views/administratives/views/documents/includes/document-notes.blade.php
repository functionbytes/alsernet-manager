<!-- Document Notes Section -->
<div class="card mt-4">
    <div class="card-header bg-light-secondary border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            Notas del documento
        </h6>
    </div>
    <div class="card-body">
        <!-- Add Note Form -->
        <form id="addNoteForm" class="mb-4">
            @csrf
            <div class="mb-3">
                <label for="noteContent" class="form-label fw-bold">Agregar Nueva Nota</label>
                <textarea class="form-control" id="noteContent" name="content" rows="3"
                          placeholder="Escribe una nota privada para este documento..." required></textarea>
                <small class="text-muted">Las notas son solo visibles para administradores</small>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                Agregar Nota
            </button>
        </form>

        <!-- Notes List -->
        @if($document->notes->isEmpty())
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
            <div class="notes-container">
                @foreach($document->notes as $note)
                    <div class="note-item card mb-3 border-left-primary" style="border-left: 4px solid #0d6efd;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <small class="text-muted">
                                        <strong>Admin ID:</strong> {{ $note->created_by }}
                                        <br>
                                        <strong>Fecha:</strong> {{ $note->created_at->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>
                                @if($note->is_internal)
                                    <span class="badge bg-warning">
                                        <i class="fa fa-lock me-1"></i> Privada
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fa fa-eyeme-1"></i> Visible
                                    </span>
                                @endif
                            </div>
                            <p class="card-text mb-0">{{ $note->content }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('addNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const content = document.getElementById('noteContent').value;
        const documentUid = '{{ $document->uid }}';

        fetch(`/administrative/orders/manage/${documentUid}/add-note`, {
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
                // Recargar la página o actualizar dinámicamente
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar la nota');
        });
    });
</script>
@endpush

<style>
    .note-item {
        transition: all 0.3s ease;
    }

    .note-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .border-left-primary {
        border-left: 4px solid #0d6efd !important;
    }
</style>
