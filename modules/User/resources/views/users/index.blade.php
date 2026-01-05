@extends('layouts.theme')

@section('page_title', 'Usuarios')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb Card --}}
    @include('theme.components.card', [
        'title' => 'Gestión de usuarios',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/home')],
            ['label' => 'Configuración', 'url' => ''],
            ['label' => 'Usuarios', 'active' => true]
        ]
    ])

    <div class="widget-content searchable-container list">

        {{-- Main Card --}}
        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Usuarios del sistema</h5>
                        <p class="small mb-0 text-muted">Gestiona usuarios, roles y permisos de acceso</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.roles.index') }}" class="btn btn-secondary">
                            Ver roles
                        </a>
                        <a href="{{ route('settings.users.create') }}" class="btn btn-primary">
                            Nuevo usuario
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="card-body border-bottom">
                    <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-exclamation-circle fs-4 me-2 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-2">Errores de Validación</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="card-body border-bottom">
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-check fs-4 me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @include('theme.components.alerts')

            {{-- Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('settings.users.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-7">
                            <label for="search" class="form-label fw-semibold">Búsqueda</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="Buscar por nombre, email o identificación..."
                                   value="{{ $searchKey ?? '' }}">
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="role" class="form-label fw-semibold">Rol</label>
                            <select id="role" name="role" class="form-select">
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

                        <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fa fa-magnifying-glass me-2"></i>Buscar
                            </button>
                            @if ($searchKey || $roleFilter)
                                <a href="{{ route('settings.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-xmark"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Users Table --}}
            @if ($users->count() > 0)
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">Usuario</th>
                                <th width="20%">Email</th>
                                <th width="15%">Rol</th>
                                <th width="15%">Última actualización</th>
                                <th width="15%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width: 40px; height: 40px; background-color: #90bb13; color: white; font-weight: bold; font-size: 14px;">
                                                {{ strtoupper(substr($user->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->lastname ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong class="d-block">{{ Str::title($user->firstname . ' ' . $user->lastname) }}</strong>
                                                @if($user->document_id)
                                                    <small class="text-muted d-block">ID: {{ $user->document_id }}</small>
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
                                                'super-admin' => 'bg-danger-subtle text-danger',
                                                'manager' => 'bg-success-subtle text-success',
                                                'customer' => 'bg-info-subtle text-info',
                                                default => 'bg-secondary-subtle text-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ Str::title(str_replace('-', ' ', $roleName)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->updated_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($user->uid)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('settings.users.show', $user->uid) }}">
                                                            Ver perfil
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('settings.users.edit', $user->uid) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item delete-btn"
                                                                data-url="{{ route('settings.users.destroy', $user->uid) }}"
                                                                data-title="¿Eliminar usuario {{ $user->firstname }} {{ $user->lastname }}?">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                @else
                                                    <li>
                                                        <span class="dropdown-item text-muted">
                                                            Usuario sin UID
                                                        </span>
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
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fa fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                    <h5 class="fw-bold mb-2">No hay usuarios</h5>
                    <p class="text-muted mb-4">
                        @if ($searchKey || $roleFilter)
                            No se encontraron resultados con los filtros aplicados.
                        @else
                            Comienza creando tu primer usuario.
                        @endif
                    </p>
                    @if ($searchKey || $roleFilter)
                        <a href="{{ route('settings.users.index') }}" class="btn btn-secondary">
                            Ver todos
                        </a>
                    @else
                        <a href="{{ route('settings.users.create') }}" class="btn btn-primary">
                            + Crear ahora
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }} de {{ $users->total() }} usuarios
                        </div>
                        <div>
                            {{ $users->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

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
