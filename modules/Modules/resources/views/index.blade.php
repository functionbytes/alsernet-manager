@extends('layouts.theme')

@section('title', 'Administración de módulos')

@section('content')

    @include('core::components.card', ['title' => 'Administración de módulos'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <div class="card">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Módulos del sistema</h5>
                        <p class="small mb-0 text-muted">Administra, habilita, deshabilita e instala módulos del sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.modules.uploadForm') }}" class="btn btn-primary">
                            Instalar módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalModules }}</h4>
                                        <small class="text-muted">Módulos instalados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">Habilitados</h6>
                                        <h4 class="mb-1 fw-bold">{{ $enabledCount }}</h4>
                                        <small class="text-muted">Módulos activos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-warning mb-2">Deshabilitados</h6>
                                        <h4 class="mb-1 fw-bold">{{ $disabledCount }}</h4>
                                        <small class="text-muted">Módulos inactivos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('settings.modules.index') }}">
                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch">
                        <div class="flex-fill">
                            <div class="input-group h-100">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre, alias o descripción..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="flex-shrink-0" style="min-width: 200px;">
                            <select name="status" class="form-select select2 h-100">
                                <option value="">Todos los estados</option>
                                <option value="enabled" {{ request('status') == 'enabled' ? 'selected' : '' }}>Habilitados</option>
                                <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Deshabilitados</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i>
                            </button>
                            @if(request('search') || request('status'))
                                <a href="{{ route('settings.modules.index') }}"
                                   class="btn btn-outline-secondary"
                                   title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modules List -->
            <div class="card-body">
                @if(count($modules) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="25%">Módulo</th>
                                <th width="35%">Descripción</th>
                                <th width="10%">Versión</th>
                                <th width="15%" class="text-center">Estado</th>
                                <th width="15%" class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($modules as $module)
                                <tr>
                                    <td>
                                        <div>
                                            <a href="{{ route('settings.modules.show', $module['alias']) }}" class="text-decoration-none">
                                                <strong>{{ $module['name'] }}</strong>
                                            </a>
                                            @if(in_array($module['name'], ['Role', 'Modules']))
                                                <br><small class="badge bg-primary-subtle text-primary">Módulo protegido</small>
                                            @endif
                                            <small class="d-block text-muted">{{ $module['alias'] }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($module['description'] ?: 'Sin descripción', 80) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-subtle text-dark">v{{ $module['version'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($module['enabled'])
                                            <span class="badge bg-success-subtle text-success">
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge bg-light text-black">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('settings.modules.show', $module['alias']) }}">
                                                        Ver detalles
                                                    </a>
                                                </li>
                                                @if(!in_array($module['name'], ['Role', 'Modules']))
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($module['enabled'])
                                                        <li>
                                                            <form action="{{ route('settings.modules.disable', $module['alias']) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-warning"
                                                                        onclick="return confirm('¿Deshabilitar el módulo {{ $module['name'] }}?')">
                                                                    Deshabilitar
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <form action="{{ route('settings.modules.enable', $module['alias']) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    Habilitar
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <form action="{{ route('settings.modules.uninstall', $module['alias']) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('¿Desinstalar el módulo {{ $module['name'] }}? Esta acción es irreversible.')">
                                                                Desinstalar
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
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
                                <i class="fas fa-cube fs-7"></i>
                            </div>
                            <h6 class="mb-1">
                                @if(request('search') || request('status'))
                                    No se encontraron módulos
                                @else
                                    No hay módulos instalados
                                @endif
                            </h6>
                            <p class="text-muted mb-3">
                                @if(request('search') || request('status'))
                                    No se encontraron resultados con los criterios de búsqueda
                                @else
                                    Instala tu primer módulo para extender la funcionalidad del sistema
                                @endif
                            </p>
                            @if(!request('search') && !request('status'))
                                <a href="{{ route('settings.modules.uploadForm') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Instalar primer módulo
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @if (session('success'))
            toastr.success('{{ session('success') }}', 'Éxito');
            @endif

            @if (session('error'))
            toastr.error('{{ session('error') }}', 'Error');
            @endif
        });
    </script>
@endpush
