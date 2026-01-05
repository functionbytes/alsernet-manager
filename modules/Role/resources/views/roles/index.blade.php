@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Gestión de Roles'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Roles del Sistema</h5>
                    <p class="text-muted mb-0 small">Administra los roles y permisos de usuario</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('settings.roles.matrix') }}" class="btn btn-info me-2">
                        <i class="fas fa-table me-2"></i>Matriz de Permisos
                    </a>
                    <a href="{{ route('settings.roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear Rol
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
                                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $roles->total() }}</h6>
                                    <small class="text-muted">Total Roles</small>
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
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $roles->where('is_default', true)->count() }}</h6>
                                    <small class="text-muted">Roles por Defecto</small>
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
                                    <i class="fas fa-lock fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \Spatie\Permission\Models\Role::whereIn('name', ['super-admin', 'customer'])->count() }}</h6>
                                    <small class="text-muted">Roles del Sistema</small>
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
                                    <i class="fas fa-users fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $roles->sum('users_count') }}</h6>
                                    <small class="text-muted">Usuarios Asignados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="card-body border-top">
            <form action="{{ route('settings.roles.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Buscar por nombre o descripción..."
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
                            <th>Nombre del Rol</th>
                            <th>Guard</th>
                            <th>Descripción</th>
                            <th width="10%" class="text-center">Usuarios</th>
                            <th width="10%" class="text-center">Estado</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $key => $role)
                            <tr>
                                <td>{{ $roles->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-shield-alt text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $role->name }}</h6>
                                            @if($role->slug)
                                                <small class="text-muted">{{ $role->slug }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light-secondary text-dark">{{ $role->guard_name }}</span>
                                </td>
                                <td>
                                    <small>{{ Str::limit($role->description ?? 'Sin descripción', 50) }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $role->users_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    @if($role->is_default)
                                        <span class="badge bg-success">Por defecto</span>
                                    @else
                                        <span class="badge bg-light-secondary text-dark">Normal</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!in_array($role->name, ['super-admin', 'customer']))
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="{{ route('settings.roles.edit', $role->id) }}" class="dropdown-item">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('settings.roles.show.permissions', $role->id) }}" class="dropdown-item">
                                                        Gestionar permisos
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('settings.roles.show.modules', $role->id) }}" class="dropdown-item">
                                                        Gestionar modulos
                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="{{ route('settings.roles.show.users', $role->id) }}" class="dropdown-item">
                                                       Ver usuarios
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item delete-btn"
                                                       data-url="{{ route('settings.roles.destroy', $role->id) }}"
                                                       data-title="¿Eliminar rol {{ $role->name }}?">
                                                        Eliminar
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="badge bg-black">
                                            Sistema
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron roles</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($roles->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $roles->firstItem() }} - {{ $roles->lastItem() }} de {{ $roles->total() }} resultados
                    </div>
                    <div>
                        {{ $roles->links() }}
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
