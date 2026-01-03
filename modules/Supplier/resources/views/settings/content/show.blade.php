@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Revisión de Contenido - ' . $content->product_name])

    <div class="widget-content searchable-container list">

        <!-- Breadcrumb -->
        <div class="card card-body mb-3 bg-light-secondary">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('settings.suppliers.content.index') }}">
                            <i class="fas fa-arrow-left me-2"></i> Cola de Contenido
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ $content->product_name }}</li>
                </ol>
            </nav>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Quality Metrics -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card {{ $content->quality_score >= 80 ? 'bg-success' : ($content->quality_score >= 50 ? 'bg-warning' : 'bg-danger') }} text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-chart-line fs-7"></i>
                            </div>
                            <div>
                                <p class="mb-1">Puntuación de Calidad</p>
                                <h3 class="mb-0">{{ $content->quality_score }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-align-left fs-7 text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Longitud</p>
                                <h5 class="mb-0">{{ $content->word_count ?? 'N/A' }} palabras</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-language fs-7 text-info"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Idioma</p>
                                <h5 class="mb-0">{{ strtoupper($content->language ?? 'es') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-light-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clock fs-7 text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">Generado</p>
                                <h6 class="mb-0">{{ $content->created_at->diffForHumans() }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card card-body mb-3">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" id="approveBtn">
                    <i class="fas fa-check me-2"></i> Aprobar
                </button>
                <button type="button" class="btn btn-danger" id="rejectBtn">
                    <i class="fas fa-times me-2"></i> Rechazar
                </button>
                <button type="button" class="btn btn-warning" id="regenerateBtn">
                    <i class="fas fa-redo me-2"></i> Regenerar
                </button>
                <button type="button" class="btn btn-primary" id="editBtn">
                    <i class="fas fa-edit me-2"></i> Editar
                </button>
                <button type="button" class="btn btn-info" id="publishBtn">
                    <i class="fas fa-upload me-2"></i> Publicar
                </button>
                <button type="button" class="btn btn-outline-secondary ms-auto" id="toggleComparisonBtn">
                    <i class="fas fa-exchange-alt me-2"></i> Alternar Comparación
                </button>
            </div>
        </div>

        <!-- Content View -->
        <div class="row">

            <!-- Original Data -->
            <div class="col-lg-6 mb-3" id="originalDataColumn">
                <div class="card card-body h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Datos Originales</h5>
                        <span class="badge bg-secondary">Fuente: {{ $content->supplier_name }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Producto</label>
                        <p class="text-dark">{{ $content->product_name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Descripción Original</label>
                        <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                            <p class="mb-0">{{ $content->original_data ?? 'Sin datos originales' }}</p>
                        </div>
                    </div>

                    @if($content->metadata)
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Metadatos</label>
                            <pre class="bg-light p-3 rounded font-monospace small" style="max-height: 200px; overflow-y: auto;">{{ json_encode(json_decode($content->metadata), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Generated Content -->
            <div class="col-lg-6 mb-3" id="generatedContentColumn">
                <div class="card card-body h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Contenido Generado</h5>
                        <span class="badge {{ $content->status === 'approved' ? 'bg-success' : ($content->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                            {{ ucfirst($content->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Tipo de Contenido</label>
                        <p class="text-dark">{{ ucfirst($content->content_type) }}</p>
                    </div>

                    <div class="mb-3" id="editableContentContainer">
                        <label class="form-label fw-semibold text-muted">Contenido Generado</label>
                        <div class="bg-light p-3 rounded" id="generatedContentDisplay" style="max-height: 400px; overflow-y: auto;">
                            <p class="mb-0">{{ $content->generated_content }}</p>
                        </div>
                        <textarea class="form-control d-none" id="generatedContentEdit" rows="15">{{ $content->generated_content }}</textarea>
                    </div>

                    @if($content->prompt_used)
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Prompt Utilizado</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#promptCollapse">
                                <i class="fas fa-eye me-2"></i> Ver Prompt
                            </button>
                            <div class="collapse mt-2" id="promptCollapse">
                                <pre class="bg-light p-3 rounded font-monospace small" style="max-height: 200px; overflow-y: auto;">{{ $content->prompt_used }}</pre>
                            </div>
                        </div>
                    @endif>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Métricas de Calidad</label>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar {{ $content->quality_score >= 80 ? 'bg-success' : ($content->quality_score >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                 style="width: {{ $content->quality_score }}%">
                                {{ $content->quality_score }}%
                            </div>
                        </div>
                        @if($content->quality_metrics)
                            @php
                                $metrics = json_decode($content->quality_metrics, true);
                            @endphp
                            <ul class="list-unstyled small mb-0">
                                @foreach($metrics as $key => $value)
                                    <li><strong>{{ ucfirst($key) }}:</strong> {{ is_numeric($value) ? round($value, 2) : $value }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Revision History -->
        @if($content->revisions && count($content->revisions) > 0)
            <div class="card card-body mb-3">
                <h5 class="mb-3">Historial de Revisiones</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Acción</th>
                                <th>Usuario</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($content->revisions as $revision)
                                <tr>
                                    <td>{{ $revision->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $revision->action === 'approved' ? 'bg-success' : ($revision->action === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ ucfirst($revision->action) }}
                                        </span>
                                    </td>
                                    <td>{{ $revision->user->name ?? 'Sistema' }}</td>
                                    <td>{{ $revision->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const contentId = {{ $content->id }};
    let isEditing = false;

    // Toggle comparison view
    $('#toggleComparisonBtn').on('click', function() {
        $('#originalDataColumn').toggleClass('col-lg-6 col-lg-12');
        $('#generatedContentColumn').toggleClass('col-lg-6 col-lg-12');

        const icon = $(this).find('i');
        icon.toggleClass('fa-exchange-alt fa-expand');
    });

    // Edit mode toggle
    $('#editBtn').on('click', function() {
        isEditing = !isEditing;

        if (isEditing) {
            $('#generatedContentDisplay').addClass('d-none');
            $('#generatedContentEdit').removeClass('d-none');
            $(this).html('<i class="fas fa-save me-2"></i> Guardar Cambios');
            $(this).removeClass('btn-primary').addClass('btn-success');
        } else {
            saveEditedContent();
        }
    });

    // Save edited content
    function saveEditedContent() {
        const editedContent = $('#generatedContentEdit').val();

        $.ajax({
            url: '{{ route("backups.suppliers.content.update", ":id") }}'.replace(':id', contentId),
            method: 'PUT',
            data: {
                generated_content: editedContent
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Contenido actualizado', 'Éxito');
                    $('#generatedContentDisplay p').text(editedContent);
                    $('#generatedContentDisplay').removeClass('d-none');
                    $('#generatedContentEdit').addClass('d-none');
                    $('#editBtn').html('<i class="fas fa-edit me-2"></i> Editar');
                    $('#editBtn').removeClass('btn-success').addClass('btn-primary');
                    isEditing = false;
                }
            },
            error: function() {
                toastr.error('Error al actualizar contenido', 'Error');
            }
        });
    }

    // Approve content
    $('#approveBtn').on('click', function() {
        performAction('approve', 'aprobar');
    });

    // Reject content
    $('#rejectBtn').on('click', function() {
        Swal.fire({
            title: 'Rechazar contenido',
            input: 'textarea',
            inputLabel: 'Motivo del rechazo (opcional)',
            inputPlaceholder: 'Escribe el motivo...',
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                performAction('reject', 'rechazar', result.value);
            }
        });
    });

    // Regenerate content
    $('#regenerateBtn').on('click', function() {
        performAction('regenerate', 'regenerar');
    });

    // Publish content
    $('#publishBtn').on('click', function() {
        performAction('publish', 'publicar');
    });

    // Generic action handler
    function performAction(action, actionName, notes = null) {
        const btn = $(`#${action}Btn`);
        const originalHtml = btn.html();

        Swal.fire({
            title: `¿${actionName.charAt(0).toUpperCase() + actionName.slice(1)} contenido?`,
            text: `Esta acción se registrará en el historial.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${actionName}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');

                $.ajax({
                    url: '{{ route("backups.suppliers.content.action", ":id") }}'.replace(':id', contentId),
                    method: 'POST',
                    data: {
                        action: action,
                        notes: notes
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Contenido');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function() {
                        toastr.error(`Error al ${actionName} contenido`, 'Error');
                        btn.prop('disabled', false);
                        btn.html(originalHtml);
                    }
                });
            }
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
