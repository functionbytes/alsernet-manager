@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Gestión de Usuarios'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Usuarios del Sistema</h5>
                    <p class="text-muted mb-0 small">Administra los usuarios y sus roles</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('settings.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear Usuario
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
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $users->total() }}</h6>
                                    <small class="text-muted">Total Usuarios</small>
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
                                    <i class="fas fa-user-check fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'manager'); })->count() }}</h6>
                                    <small class="text-muted">Managers</small>
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
                                    <i class="fas fa-user-tie fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'customer'); })->count() }}</h6>
                                    <small class="text-muted">Clientes</small>
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
                                    <small class="text-muted">Roles Disponibles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="card-body border-top">
            <form action="{{ route('settings.users.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Buscar por nombre, email o identificación..."
                                   value="{{ $searchKey ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="role">
                            <option value="">Todos los roles</option>
                            @forelse($availableRoles as $roleName => $roleLabel)
                                <option value="{{ $roleName }}" {{ ($roleFilter ?? '') === $roleName ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('-', ' ', $roleName)) }}
                                </option>
                            @empty
                                <option disabled>No hay roles disponibles</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('settings.users.index') }}" class="btn btn-light w-100">
                            <i class="fas fa-redo me-2"></i>Limpiar
                        </a>
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
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th width="15%">Rol</th>
                            <th width="12%">Última Actualización</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $key => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ Str::words(Str::title($user->firstname . ' ' . $user->lastname), 12, '...') }}</h6>
                                            @if($user->document_id)
                                                <small class="text-muted">ID: {{ $user->document_id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->email }}</span>
                                </td>
                                <td>
                                    @php
                                        $roleName = $user->getRoleNames()->first() ?? 'Sin rol';
                                        $badgeClass = match($roleName) {
                                            'super-admin' => 'bg-light-danger text-danger',
                                            'manager' => 'bg-light-success text-success',
                                            'customer' => 'bg-light-info text-info',
                                            default => 'bg-light-secondary text-dark'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        <i class="fas fa-shield-alt me-1"></i>{{ Str::title(str_replace('-', ' ', $roleName)) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $user->updated_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($user->uid)
                                                <li>
                                                    <a href="{{ route('settings.users.show', $user->uid) }}" class="dropdown-item">
                                                        Ver
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('settings.users.edit', $user->uid) }}" class="dropdown-item">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item text-danger delete-btn"
                                                       data-url="{{ route('settings.users.destroy', $user->uid) }}"
                                                       data-title="¿Eliminar usuario {{ $user->firstname }} {{ $user->lastname }}?">
                                                        Eliminar
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <span class="dropdown-item text-muted">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Usuario sin UID
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron usuarios</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }} de {{ $users->total() }} resultados
                    </div>
                    <div>
                        {{ $users->appends(request()->input())->links() }}
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
