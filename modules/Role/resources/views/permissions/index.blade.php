@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Gestión de Permisos'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Permisos del Sistema</h5>
                    <p class="text-muted mb-0 small">Administra los permisos disponibles para los roles</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('settings.permissions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear Permiso
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-light-primary border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-key fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $permissions->total() }}</h6>
                                    <small class="text-muted">Total Permisos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-success border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-globe fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \Spatie\Permission\Models\Permission::where('guard_name', 'web')->count() }}</h6>
                                    <small class="text-muted">Permisos Web</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-info border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-server fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \Spatie\Permission\Models\Permission::where('guard_name', 'api')->count() }}</h6>
                                    <small class="text-muted">Permisos API</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-warning border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-shield-alt fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \Spatie\Permission\Models\Role::count() }}</h6>
                                    <small class="text-muted">Roles Totales</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="card-body border-top">
            <form action="{{ route('settings.permissions.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Buscar por nombre de permiso..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nombre del Permiso</th>
                            <th>Guard</th>
                            <th width="10%" class="text-center">Roles Asignados</th>
                            <th width="15%">Fecha de Creación</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $key => $permission)
                            <tr>
                                <td>{{ $permissions->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-key text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $permission->name }}</h6>
                                            @if($permission->description)
                                                <small class="text-muted">{{ Str::limit($permission->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($permission->guard_name === 'web')
                                        <span class="badge bg-light-success text-success">
                                            <i class="fas fa-globe me-1"></i>Web
                                        </span>
                                    @elseif($permission->guard_name === 'api')
                                        <span class="badge bg-light-info text-info">
                                            <i class="fas fa-server me-1"></i>API
                                        </span>
                                    @else
                                        <span class="badge bg-light-secondary text-dark">{{ $permission->guard_name }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $permission->roles()->count() }}</span>
                                </td>
                                <td>
                                    <small>{{ $permission->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('permissions.edit')
                                                <li>
                                                    <a href="{{ route('settings.permissions.edit', $permission->id) }}" class="dropdown-item">
                                                        <i class="fas fa-edit me-2"></i>Editar
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('permissions.delete')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item text-danger delete-btn"
                                                       data-url="{{ route('settings.permissions.destroy', $permission->id) }}"
                                                       data-title="¿Eliminar permiso {{ $permission->name }}?">
                                                        <i class="fas fa-trash me-2"></i>Eliminar
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron permisos</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($permissions->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $permissions->firstItem() }} - {{ $permissions->lastItem() }} de {{ $permissions->total() }} resultados
                    </div>
                    <div>
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    @include('theme.components.delete')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).data('url');
        const deleteTitle = $(this).data('title');

        $('#delete-modal .modal-title').text(deleteTitle);
        $('#delete-form').attr('action', deleteUrl);
        $('#delete-modal').modal('show');
    });
});
</script>
@endpush
