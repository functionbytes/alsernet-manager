@extends('layouts.managers')

@section('title', 'Tipos de Documentos')

@section('content')

    @include('managers.includes.card', ['title' => 'Tipos de Documentos'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <!-- System Settings Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Tipos de documentos</h5>
                        <p class="small mb-0 text-muted">Gestiona los tipos de documentos con soporte multi-idioma y requisitos personalizados</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('status'))
                            <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.documents.types.create') }}" class="btn btn-primary">
                            Nuevo tipo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.documents.types') }}">
                    <div class="row align-items-center g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-magnifying-glass"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por tipo o etiqueta..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select select2" name="status" data-minimum-results-for-search="Infinity">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Document Types List -->
            <div class="card-body">
                @if($documentTypes->count() > 0)
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-circle-info me-2"></i>
                        Cada tipo de documento puede tener traducciones en múltiples idiomas y requisitos personalizados
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%"></th>
                                    <th width="15%">Tipo</th>
                                    <th width="25%">Etiqueta</th>
                                    <th width="10%" class="text-center">Requisitos</th>
                                    <th width="10%" class="text-center">Traducciones</th>
                                    <th width="10%" class="text-center">Estado</th>
                                    <th width="10%" class="text-center">Orden</th>
                                    <th width="10%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentTypes as $type)
                                    @php
                                        $translation = $type->translate(session('lang_id', 1));
                                        $totalLangs = $langs->count();
                                        $completedLangs = $type->getTranslationsList()->count();
                                        $translationPercentage = $totalLangs > 0 ? round(($completedLangs / $totalLangs) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                                 style="width: 32px; height: 32px; background-color: {{ $type->color }}20;">
                                                <i class="{{ $type->icon }}" style="color: {{ $type->color }};"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ $type->slug }}</code>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $translation?->label ?? $type->slug }}</strong>
                                                @if($translation?->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($translation->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($type->requirements->count() > 0)
                                                <span class="badge bg-info-subtle text-info">
                                                    <i class="fa fa-file-lines me-1"></i>
                                                    {{ $type->requirements->count() }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted">
                                                    <i class="fa fa-minus"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($translationPercentage === 100)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fa fa-circle-check me-1"></i>
                                                    {{ $completedLangs }}/{{ $totalLangs }}
                                                </span>
                                            @elseif($translationPercentage > 0)
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fa fa-triangle-exclamation me-1"></i>
                                                    {{ $completedLangs }}/{{ $totalLangs }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fa fa-circle-xmark me-1"></i>
                                                    0/{{ $totalLangs }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($type->is_active)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fa fa-circle-check me-1"></i>
                                                    Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    <i class="fa fa-circle-pause me-1"></i>
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $type->sort_order }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.settings.documents.types.edit', $type->slug) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('manager.settings.documents.types.toggle-active', $type->slug) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                @if($type->is_active)
                                                                    Desactivar
                                                                @else
                                                                    Activar
                                                                @endif
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item text-danger delete-btn"
                                                                data-type="{{ $type->slug }}"
                                                                data-label="{{ $translation?->label ?? $type->slug }}">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="round-48 rounded-circle bg-light-subtle text-muted mb-3 d-flex align-items-center justify-content-center">
                                <i class="fa fa-inbox fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay tipos de documentos para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    No se encontraron resultados para "{{ request('search') }}"
                                @else
                                    Crea tu primer tipo de documento para comenzar
                                @endif
                            </p>
                            @if(!request('search'))
                                <a href="{{ route('manager.settings.documents.types.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus"></i> Crear Primer Tipo
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($documentTypes->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $documentTypes->firstItem() }}</strong> a <strong>{{ $documentTypes->lastItem() }}</strong>
                            de <strong>{{ $documentTypes->total() }}</strong> tipos
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $documentTypes->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="fa fa-triangle-exclamation me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">¿Estás seguro de que deseas eliminar el tipo de documento <strong class="text-danger" id="deleteTypeName"></strong>?</p>
                    <div class="alert alert-warning bg-warning-subtle border border-warning mb-0">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-circle-exclamation me-2 mt-1"></i>
                            <div>
                                <strong>Advertencia:</strong>
                                <p class="mb-0 small mt-1">Esta acción eliminará todas las traducciones y requisitos asociados. Esta operación no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-light-subtle" data-bs-dismiss="modal">
                        <i class="fa fa-xmark me-1"></i>
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash me-1"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        allowClear: false,
        minimumResultsForSearch: Infinity
    });

    // Delete modal functionality
    const deleteModal = new bootstrap.Modal($('#deleteModal')[0]);
    const deleteForm = $('#deleteForm');
    const deleteTypeName = $('#deleteTypeName');

    $('.delete-btn').on('click', function() {
        const typeSlug = $(this).data('type');
        const typeLabel = $(this).data('label');

        deleteTypeName.text(typeLabel);
        deleteForm.attr('action', "{{ url('manager/settings/documents/types') }}/" + typeSlug);
        deleteModal.show();
    });

    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
