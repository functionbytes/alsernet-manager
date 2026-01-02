@extends('layouts.theme')

@section('title', $role->name . ' - Roles')

@section('content')

    @include('theme.components.card', ['title' => 'Detalles del rol'])

    <div class="widget-content searchable-container list">

        @include('theme.components.alerts')

        <!-- Main Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 64px; height: 64px; background-color: #f5f6f8; color: #90bb13;">
                            <i class="fas fa-user-shield fs-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">{{ $role->name }}</h5>
                            <p class="small mb-0 text-muted">{{ $role->description ?? 'Sin descripción' }}</p>
                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-tag me-1"></i> {{ $role->guard_name }}
                                </span>

                                @if($role->is_default)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i> Por defecto
                                    </span>
                                @endif

                                @if(in_array($role->name, ['super-admin', 'customer']))
                                    <span class="badge bg-warning">
                                        <i class="fas fa-cog me-1"></i> Sistema
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('manager.roles.edit', $role) }}" class="btn btn-primary">
                            <i class="fas fa-pen me-1"></i> Editar
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('manager.roles.show.permissions', $role) }}">
                                        <i class="fas fa-lock me-2"></i> Gestionar permisos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('manager.roles.show.users', $role) }}">
                                        <i class="fas fa-users me-2"></i> Ver usuarios
                                    </a>
                                </li>
                                @if(!in_array($role->name, ['super-admin', 'customer']))
                                    <li>
                                        <a class="dropdown-item" href="{{ route('manager.roles.duplicate', $role) }}">
                                            <i class="fas fa-copy me-2"></i> Duplicar rol
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger confirm-delete"
                                           data-href="{{ route('manager.roles.destroy', $role) }}">
                                            <i class="fas fa-trash me-2"></i> Eliminar rol
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-subtle h-100 mb-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2 small">Total usuarios</h6>
                                <h3 class="mb-1 fw-bold text-primary">
                                    <i class="fas fa-users me-2"></i>{{ $role->users()->count() }}
                                </h3>
                                <small class="text-muted">Asignados a este rol</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-subtle h-100 mb-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2 small">Permisos activos</h6>
                                <h3 class="mb-1 fw-bold text-success">
                                    <i class="fas fa-check-circle me-2"></i>{{ $role->permissions()->count() }}
                                </h3>
                                <small class="text-muted">Total asignados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-subtle h-100 mb-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2 small">Fecha creación</h6>
                                <h3 class="mb-1 fw-bold text-info" style="font-size: 1rem;">
                                    {{ $role->created_at->format('d/m/Y') }}
                                </h3>
                                <small class="text-muted">{{ $role->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-subtle h-100 mb-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2 small">Guard</h6>
                                <h3 class="mb-1 fw-bold text-dark" style="font-size: 1rem;">
                                    @if($role->guard_name === 'web')
                                        <span class="badge bg-success-subtle text-success">Web</span>
                                    @elseif($role->guard_name === 'api')
                                        <span class="badge bg-info-subtle text-info">API</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $role->guard_name }}</span>
                                    @endif
                                </h3>
                                <small class="text-muted">Tipo de autenticación</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Information -->
            <div class="card-body border-bottom">
                <div class="mb-3">
                    <h6 class="fw-bold">Información del rol</h6>
                    <p class="text-muted small mb-0">Datos generales y configuración</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Nombre</label>
                        <p class="mb-0 fw-semibold">{{ $role->name }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Slug</label>
                        <p class="mb-0"><code class="bg-light px-2 py-1 rounded">{{ $role->slug }}</code></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Guard</label>
                        <p class="mb-0">
                            @if($role->guard_name === 'web')
                                <span class="badge bg-success">Web</span>
                            @elseif($role->guard_name === 'api')
                                <span class="badge bg-info">API</span>
                            @else
                                <span class="badge bg-secondary">{{ $role->guard_name }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Estado</label>
                        <p class="mb-0">
                            @if($role->is_default)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Rol por defecto
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-circle me-1"></i> No predeterminado
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Descripción</label>
                        <p class="mb-0">{{ $role->description ?? '—' }}</p>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Última actualización</label>
                        <p class="mb-0 fw-semibold">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        <small class="text-muted">{{ $role->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>

            <!-- Assigned Permissions -->
            <div class="card-body border-bottom">
                <div class="mb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold">Permisos asignados</h6>
                        <p class="text-muted small mb-0">Lista de permisos otorgados a este rol</p>
                    </div>
                    <a href="{{ route('manager.roles.show.permissions', $role) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Gestionar
                    </a>
                </div>

                @php
                    $groupedPermissions = $role->permissions()
                        ->get()
                        ->groupBy(fn($perm) => explode('.', $perm->name)[0]);
                @endphp

                @if($groupedPermissions->count() > 0)
                    <div class="row g-3">
                        @foreach($groupedPermissions as $category => $permissions)
                            <div class="col-md-12">
                                <div class="card bg-light-subtle mb-0">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-semibold text-uppercase small">
                                            <i class="fas fa-folder me-2" style="color: #90bb13;"></i>
                                            {{ ucwords(str_replace(['_', '-'], ' ', $category)) }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            @foreach($permissions as $permission)
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-check-circle text-success small"></i>
                                                        <code class="bg-white px-2 py-1 rounded small">
                                                            {{ $permission->name }}
                                                        </code>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width: 64px; height: 64px; background-color: #f5f6f8;">
                            <i class="fas fa-lock-open fs-3 text-muted"></i>
                        </div>
                        <h6 class="mb-1">Sin permisos asignados</h6>
                        <p class="text-muted mb-3 small">Este rol no tiene ningún permiso asignado</p>
                        <a href="{{ route('manager.roles.show.permissions', $role) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Asignar permisos
                        </a>
                    </div>
                @endif
            </div>

            <!-- System Information -->
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="fw-bold">Información del sistema</h6>
                    <p class="text-muted small mb-0">Fechas y datos de registro</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Fecha de creación</label>
                        <p class="mb-0 fw-semibold">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                        <small class="text-muted">{{ $role->created_at->diffForHumans() }}</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Última actualización</label>
                        <p class="mb-0 fw-semibold">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        <small class="text-muted">{{ $role->updated_at->diffForHumans() }}</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">ID del rol</label>
                        <p class="mb-0 fw-semibold">#{{ $role->id }}</p>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('manager.roles') }}" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-left me-1"></i> Volver a la lista
                </a>
            </div>

        </div>

    </div>

@endsection
