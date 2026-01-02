@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Gestionar usuarios del rol'])

    @include('theme.components.alerts')

    <div class="widget-content searchable-container list">

        <!-- Main Card -->
        <div class="card card-body border">

            <!-- Header Section -->
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-12">
                    <h5 class="mb-2">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Usuarios con rol: <strong>{{ $role->name }}</strong>
                    </h5>
                    <p class="text-muted mb-0 small">
                        Gestiona los usuarios asignados a este rol
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        @if($search)
                            <a href="{{ route('manager.settings.roles.show.users', $role->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-2"></i>Limpiar filtros
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.roles.show', $role->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Volver al rol
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignUsersModal">
                            <i class="fas fa-user-plus me-2"></i>Asignar usuarios
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <p class="text-muted small mb-1">Total usuarios</p>
                            <h5 class="mb-0 fw-bold">{{ $users->total() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted small mb-1">Usuarios activos</p>
                            <h5 class="mb-0 fw-bold">{{ $users->where('is_active', true)->count() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
                            <p class="text-muted small mb-1">Usuarios inactivos</p>
                            <h5 class="mb-0 fw-bold">{{ $users->where('is_active', false)->count() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-shield fa-2x text-info mb-2"></i>
                            <p class="text-muted small mb-1">Permisos del rol</p>
                            <h5 class="mb-0 fw-bold">{{ $role->permissions()->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-12">
                    <form class="position-relative form-search" action="{{ route('manager.settings.roles.show.users', $role->id) }}" method="GET">
                        <div class="row g-2">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Nombre, apellido o email..." @isset($search) value="{{ $search }}" @endisset>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar usuarios">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table or Empty State -->
            @if($users->count() > 0)
                <div class="table-responsive mb-3">
                    <table class="table search-table align-middle text-nowrap">
                        <thead class="header-item">
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Registrado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $key => $user)
                                <tr class="search-items">
                                    <td>{{ ($users->currentPage() - 1) * $users->perPage() + $key + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-circle" style="width: 40px; height: 40px;">
                                                <span class="avatar-initials rounded-circle d-flex align-items-center justify-content-center" style="background-color: #90bb13; color: white; font-weight: bold; font-size: 14px;">
                                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</p>
                                                @if($user->roles()->count() > 1)
                                                    <span class="badge bg-info text-dark small">{{ $user->roles()->count() }} roles</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $user->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown dropstart">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('manager.users.show', $user->id) }}" class="dropdown-item">
                                                        <i class="fas fa-eye me-2"></i>Ver perfil
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('manager.users.edit', $user->id) }}" class="dropdown-item">
                                                        <i class="fas fa-pen-to-square me-2"></i>Editar usuario
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger remove-user-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->first_name }} {{ $user->last_name }}" data-role-id="{{ $role->id }}">
                                                        <i class="fas fa-trash me-2"></i>Remover del rol
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

                <!-- Pagination Info -->
                <div class="row align-items-center mt-3">
                    <div class="col-auto">
                        <small class="text-muted">
                            Mostrando {{ ($users->currentPage() - 1) * $users->perPage() + 1 }} - {{ min($users->currentPage() * $users->perPage(), $users->total()) }} de {{ $users->total() }} usuarios
                        </small>
                    </div>
                </div>

                <!-- Pagination Links -->
                @if($users->hasPages())
                    <nav class="d-flex justify-content-center">
                        {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
                    </nav>
                @endif

            @elseif($search)
                <!-- Empty State: Search Results -->
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron usuarios con los criterios de búsqueda</p>
                    <a href="{{ route('manager.settings.roles.show.users', $role->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-times me-2"></i>Limpiar búsqueda
                    </a>
                </div>
            @else
                <!-- Empty State: No Users Assigned -->
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-3">Este rol no tiene usuarios asignados aún</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignUsersModal">
                        <i class="fas fa-user-plus me-2"></i>Asignar usuarios
                    </button>
                </div>
            @endif

        </div>

    </div>

    <!-- Modal: Assign Users -->
    <div class="modal fade" id="assignUsersModal" tabindex="-1" aria-labelledby="assignUsersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignUsersModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Asignar usuarios al rol
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignUsersForm" method="POST" action="{{ route('manager.settings.roles.assign.users') }}">
                    @csrf
                    <input type="hidden" name="role_id" value="{{ $role->id }}">

                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-circle-info me-2"></i>
                            <strong>Nota:</strong> Al seleccionar usuarios, se les asignará el rol "<strong>{{ $role->name }}</strong>". Los usuarios podrán acceder a todos los permisos asociados a este rol.
                        </div>

                        <div class="form-group mb-3">
                            <label for="userSelect" class="form-label">Selecciona los usuarios</label>
                            <select id="userSelect" name="user_ids[]" class="form-control" style="width: 100%;" multiple="multiple">
                                <!-- Options loaded via AJAX with Select2 -->
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveAssignBtn">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            // Initialize Select2 for User Selection
            $('#userSelect').select2({
                placeholder: 'Busca y selecciona usuarios...',
                allowClear: true,
                multiple: true,
                ajax: {
                    url: '{{ route("manager.users.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    }
                },
                templateResult: function(user) {
                    if (!user.id) {
                        return user.text;
                    }
                    const initials = user.first_name.charAt(0).toUpperCase() + user.last_name.charAt(0).toUpperCase();
                    return $('<span>' +
                        '<span style="display: inline-block; width: 32px; height: 32px; background-color: #90bb13; color: white; border-radius: 50%; text-align: center; line-height: 32px; margin-right: 8px; font-weight: bold; font-size: 12px;">' + initials + '</span>' +
                        '<span>' + user.first_name + ' ' + user.last_name + ' <small class="text-muted">(' + user.email + ')</small></span>' +
                        '</span>');
                },
                templateSelection: function(user) {
                    if (!user.id) {
                        return user.text;
                    }
                    return user.first_name + ' ' + user.last_name;
                }
            });

            // Handle Form Submission
            $('#assignUsersForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const $submitBtn = $('#saveAssignBtn');

                $submitBtn.prop('disabled', true);
                $submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Éxito', { positionClass: 'toast-bottom-right' });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(response.message, 'Error', { positionClass: 'toast-bottom-right' });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error al asignar usuarios.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message, 'Error', { positionClass: 'toast-bottom-right' });
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html('<i class="fas fa-save me-2"></i>Guardar');
                    }
                });
            });

            // Handle Remove User from Role
            $(document).on('click', '.remove-user-btn', function(e) {
                e.preventDefault();

                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');
                const roleId = $(this).data('role-id');

                if (confirm('¿Estás seguro de que deseas remover a ' + userName + ' de este rol?')) {
                    $.ajax({
                        url: '{{ route("manager.settings.roles.remove.user") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            user_id: userId,
                            role_id: roleId
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message, 'Éxito', { positionClass: 'toast-bottom-right' });
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                toastr.error(response.message, 'Error', { positionClass: 'toast-bottom-right' });
                            }
                        },
                        error: function(xhr) {
                            let message = 'Error al remover usuario del rol.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            toastr.error(message, 'Error', { positionClass: 'toast-bottom-right' });
                        }
                    });
                }
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
