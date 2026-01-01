@extends('layouts.theme')

@section('title', 'Prompts de IA')

@section('content')

    @include('managers.components.card', ['title' => 'Prompts de IA'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Prompts de IA</h5>
                        <p class="small mb-0 text-muted">Gestiona plantillas de prompts para generación de contenido con IA</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('status') || request('scope'))
                            <a href="{{ route('manager.settings.suppliers.prompts.index') }}" class="btn btn-secondary">
                                Limpiar filtros
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.suppliers.prompts.create') }}" class="btn btn-primary">
                            Nuevo prompt
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.suppliers.prompts.index') }}">
                    <div class="row align-items-center g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-magnifying-glass"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por nombre, alcance o tono..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="scope">
                                <option value="">Todos los alcances</option>
                                <option value="global" {{ request('scope') === 'global' ? 'selected' : '' }}>Global</option>
                                <option value="supplier" {{ request('scope') === 'supplier' ? 'selected' : '' }}>Proveedor</option>
                                <option value="category" {{ request('scope') === 'category' ? 'selected' : '' }}>Categoría</option>
                                <option value="supplier_category" {{ request('scope') === 'supplier_category' ? 'selected' : '' }}>Prov+Cat</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Prompts List -->
            <div class="card-body">
                @if($prompts->count() > 0)
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-circle-info me-2"></i>
                        Los prompts definen las plantillas de IA para generar contenido según alcance, tipo y tono
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Alcance</th>
                                <th>Tipo de contenido</th>
                                <th>Proveedor</th>
                                <th>Tono</th>
                                <th class="text-center">Prioridad</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($prompts as $prompt)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $prompt->label }}</strong>
                                            @if($prompt->seo_focus)
                                                <span class="badge bg-info-subtle text-info ms-1">
                                                    <i class="fa fa-magnifying-glass"></i> SEO
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fa fa-language me-1"></i>{{ strtoupper($prompt->output_language) }}
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            $scopeLabels = [
                                                'global' => ['label' => 'Global', 'color' => 'primary'],
                                                'supplier' => ['label' => 'Proveedor', 'color' => 'info'],
                                                'category' => ['label' => 'Categoría', 'color' => 'warning'],
                                                'supplier_category' => ['label' => 'Prov+Cat', 'color' => 'success'],
                                                'source' => ['label' => 'Fuente', 'color' => 'secondary']
                                            ];
                                            $scope = $scopeLabels[$prompt->scope] ?? ['label' => ucfirst($prompt->scope), 'color' => 'light'];
                                        @endphp
                                        <span class="badge bg-{{ $scope['color'] }}-subtle text-{{ $scope['color'] }}">{{ $scope['label'] }}</span>
                                    </td>
                                    <td>
                                        <small>{{ ucfirst(str_replace('_', ' ', $prompt->content_type)) }}</small>
                                    </td>
                                    <td>
                                        @if($prompt->supplier)
                                            <small class="text-muted">{{ $prompt->supplier->name }}</small>
                                        @else
                                            <span class="badge bg-light text-muted">
                                                <i class="fa fa-minus"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ ucfirst($prompt->tone) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $prompt->priority }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($prompt->is_active)
                                            <span class="badge bg-success-subtle text-success">
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.settings.suppliers.prompts.edit', $prompt->uid) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('manager.settings.suppliers.prompts.duplicate', $prompt->uid) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            Duplicar
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('manager.settings.suppliers.prompts.toggle', $prompt->uid) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            @if($prompt->is_active)
                                                                Desactivar
                                                            @else
                                                                Activar
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a
                                                        class="dropdown-item text-success delete-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete-modal"
                                                        data-url="{{ route('manager.settings.suppliers.prompts.destroy', $prompt->uid) }}"
                                                        data-title="Eliminar prompt: {{ $prompt->label }}">
                                                        Eliminar
                                                    </a>
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
                                <i class="fa fa-wand-magic-sparkles fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay prompts para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    No se encontraron resultados para "{{ request('search') }}"
                                @else
                                    Crea tu primer prompt para IA
                                @endif
                            </p>
                            @if(!request('search'))
                                <a href="{{ route('manager.settings.suppliers.prompts.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus"></i> Crear Primer Prompt
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($prompts->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $prompts->firstItem() }}</strong> a <strong>{{ $prompts->lastItem() }}</strong>
                            de <strong>{{ $prompts->total() }}</strong> prompts
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $prompts->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('managers.components.delete')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete modal functionality
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        const deleteTitle = $(this).data('title');

        $('#delete-modal .modal-title').text(deleteTitle);
        $('#delete-form').attr('action', deleteUrl);
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
