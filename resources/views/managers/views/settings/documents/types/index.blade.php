@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Tipos de Documentos'])

    <div class="widget-content searchable-container list">

        <!-- Header with action buttons -->
        <div class="card card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 fw-bold">Tipos de documentos</h5>
                            <p class="text-muted small mb-0">
                                Gestiona los tipos de documentos con soporte multi-idioma y requisitos personalizados.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('manager.settings.documents.types.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                <span class="d-none d-sm-inline">Crear Nuevo</span>
                            </a>
                            <a href="{{ route('manager.settings.documents.types.export') }}" class="btn btn-outline-primary">
                                <i class="fas fa-download me-1"></i>
                                <span class="d-none d-sm-inline">Exportar</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info alert -->
        <div class="alert alert-info bg-info-subtle border-0 mt-3" role="alert">
            <i class="fas fa-circle-info me-2"></i>
            <strong>Nota:</strong> Cada tipo de documento puede tener traducciones en múltiples idiomas y requisitos personalizados.
        </div>

        <!-- Document Types Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table search-table align-middle text-nowrap">
                        <thead class="header-item">
                            <tr>
                                <th class="fw-bold" style="width: 50px;"></th>
                                <th class="fw-bold">Tipo</th>
                                <th class="fw-bold">Etiqueta</th>
                                <th class="fw-bold text-center">Requisitos</th>
                                <th class="fw-bold text-center">Traducciones</th>
                                <th class="fw-bold text-center">Estado</th>
                                <th class="fw-bold text-center">Orden</th>
                                <th class="fw-bold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentTypes as $type)
                                @php
                                    $translation = $type->translate(session('lang_id', 1));
                                    $totalLangs = $langs->count();
                                    $completedLangs = $type->getTranslationsList()->count();
                                    $translationPercentage = $totalLangs > 0 ? round(($completedLangs / $totalLangs) * 100) : 0;
                                @endphp
                                <tr class="search-items">
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 36px; height: 36px; background-color: {{ $type->color }}20;">
                                                <i class="{{ $type->icon }} fs-5" style="color: {{ $type->color }};"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $type->slug }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-semibold">{{ $translation?->label ?? $type->slug }}</span>
                                            @if($translation?->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($translation->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($type->requirements->count() > 0)
                                            <span class="badge bg-info" title="{{ $type->requirements->count() }} requisito(s)">
                                                <i class="fas fa-file-alt me-1"></i>
                                                {{ $type->requirements->count() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-minus"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($translationPercentage === 100)
                                            <span class="badge bg-success" title="Completo: {{ $completedLangs }}/{{ $totalLangs }} idiomas">
                                                <i class="fas fa-check me-1"></i>
                                                {{ $completedLangs }}/{{ $totalLangs }}
                                            </span>
                                        @elseif($translationPercentage > 0)
                                            <span class="badge bg-warning" title="Incompleto: {{ $completedLangs }}/{{ $totalLangs }} idiomas">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ $completedLangs }}/{{ $totalLangs }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger" title="Sin traducciones">
                                                <i class="fas fa-times me-1"></i>
                                                0/{{ $totalLangs }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($type->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-pause-circle me-1"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $type->sort_order }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown dropstart">
                                            <a href="#" class="text-muted" id="dropdownMenuButton{{ $type->id }}"
                                               data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $type->id }}">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-3"
                                                       href="{{ route('manager.settings.documents.types.edit', $type->slug) }}">
                                                        <i class="fas fa-pencil fs-4"></i> Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('manager.settings.documents.types.toggle-active', $type->slug) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-3">
                                                            @if($type->is_active)
                                                                <i class="fas fa-pause fs-4"></i> Desactivar
                                                            @else
                                                                <i class="fas fa-play fs-4"></i> Activar
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li class="border-top my-2"></li>
                                                <li>
                                                    <button type="button"
                                                            class="dropdown-item d-flex align-items-center gap-3 text-danger delete-btn"
                                                            data-type="{{ $type->slug }}"
                                                            data-label="{{ $translation?->label ?? $type->slug }}">
                                                        <i class="fas fa-trash fs-4"></i> Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fs-1 mb-3 d-block"></i>
                                            <p class="mb-0">No hay tipos de documentos configurados.</p>
                                            <a href="{{ route('manager.settings.documents.types.create') }}"
                                               class="btn btn-primary btn-sm mt-3">
                                                <i class="fas fa-plus me-1"></i>
                                                Crear el primero
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el tipo de documento <strong id="deleteTypeName"></strong>?</p>
                    <div class="alert alert-warning border-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Advertencia:</strong> Esta acción eliminará todas las traducciones y requisitos asociados.
                        Esta operación no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const deleteTypeName = document.getElementById('deleteTypeName');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const typeSlug = this.getAttribute('data-type');
            const typeLabel = this.getAttribute('data-label');

            deleteTypeName.textContent = typeLabel;
            deleteForm.action = "{{ url('manager/settings/documents/types') }}/" + typeSlug;
            deleteModal.show();
        });
    });
});
</script>
@endpush
