@extends('layouts.managers')

@section('title', 'Biblioteca de Prompts')

@section('content')

    @include('managers.includes.card', ['title' => 'Biblioteca de Prompts'])

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
                        @if(request('search'))
                            <a href="{{ route('manager.settings.suppliers.prompts.index') }}" class="btn btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.suppliers.prompts.create') }}" class="btn btn-primary">
                            Nuevo prompt
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                                        <small class="text-muted">Prompts configurados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">Activos</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['active'] }}</h4>
                                        <small class="text-muted">Prompts habilitados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-warning mb-2">Inactivos</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['inactive'] }}</h4>
                                        <small class="text-muted">Prompts deshabilitados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-info mb-2">Globales</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['global'] }}</h4>
                                        <small class="text-muted">Uso general</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.suppliers.prompts.index') }}">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" name="search" class="form-control" placeholder="Buscar prompts..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Prompts List -->
            <div class="card-body">
                @if($prompts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="25%">Nombre</th>
                                <th width="12%">Alcance</th>
                                <th width="15%">Proveedor</th>
                                <th width="12%">Tono</th>
                                <th width="8%">Prioridad</th>
                                <th width="18%">Idioma</th>
                                <th width="10%" class="text-center">Estado</th>
                                <th width="10%" class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($prompts as $prompt)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $prompt->label }}</strong>
                                            @if($prompt->is_default)
                                                <span class="badge bg-primary-subtle text-primary ms-1">Por Defecto</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $scopeLabels = [
                                                'global' => ['label' => 'Global', 'color' => 'primary'],
                                                'supplier' => ['label' => 'Proveedor', 'color' => 'info'],
                                                'category' => ['label' => 'Categoría', 'color' => 'warning'],
                                                'source' => ['label' => 'Fuente', 'color' => 'secondary']
                                            ];
                                            $scope = $scopeLabels[$prompt->scope] ?? ['label' => ucfirst($prompt->scope), 'color' => 'light'];
                                        @endphp
                                        <span class="badge bg-{{ $scope['color'] }}-subtle text-{{ $scope['color'] }}">{{ $scope['label'] }}</span>
                                    </td>
                                    <td>
                                        @if($prompt->supplier)
                                            <small class="text-muted">{{ $prompt->supplier->name }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light-subtle text-dark">{{ ucfirst($prompt->tone) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $prompt->priority }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ strtoupper($prompt->output_language) }}</small>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('manager.settings.suppliers.prompts.toggle', $prompt->uid) }}" class="toggle-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch d-inline-block">
                                                <input type="checkbox" class="form-check-input toggle-checkbox" role="switch"
                                                       {{ $prompt->is_active ? 'checked' : '' }}
                                                       onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.settings.suppliers.prompts.edit', $prompt->uid) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('manager.settings.suppliers.prompts.duplicate', $prompt->uid) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            Duplicar
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button"
                                                            class="dropdown-item text-success delete-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#delete-modal"
                                                            data-url="{{ route('manager.settings.suppliers.prompts.destroy', $prompt->uid) }}"
                                                            data-title="Eliminar prompt: {{ $prompt->label }}">
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
                                <i class="fas fa-magic fs-7"></i>
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
                                    <i class="fas fa-plus"></i>
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

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle form handling
    $('.toggle-form').on('submit', function() {
        $(this).find('.toggle-checkbox').prop('disabled', true);
    });

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
