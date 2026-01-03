@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Editar Usuario'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    {{-- Stats Cards --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-light-info border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-calendar-alt fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $user->created_at->format('d/m/Y') }}</h6>
                                    <small class="text-muted">Fecha de registro</small>
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
                                    <i class="fas fa-shield-alt fa-2x text-success"></i>
                                </div>
                                <div>
                                    @php
                                        $roleName = $user->getRoleNames()->first() ?? 'Sin rol';
                                    @endphp
                                    <h6 class="mb-0">{{ Str::title(str_replace('-', ' ', $roleName)) }}</h6>
                                    <small class="text-muted">Rol actual</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-light-{{ $user->available ? 'success' : 'danger' }} border-0 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-circle-check fa-2x text-{{ $user->available ? 'success' : 'danger' }}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $user->available ? 'Activo' : 'Inactivo' }}</h6>
                                    <small class="text-muted">Estado</small>
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
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $user->updated_at->diffForHumans() }}</h6>
                                    <small class="text-muted">Última actualización</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">
            @csrf
            <input type="hidden" name="uid" value="{{ $user->uid }}">

            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Editar Usuario</h5>
                        <p class="text-muted mb-0 small">Actualiza la información del usuario en el sistema</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('settings.users.index') }}" class="btn btn-light me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                        <a href="{{ route('settings.users.show', $user->uid) }}" class="btn btn-info">
                            <i class="fas fa-eye me-2"></i>Ver Perfil
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Sección: Identificador único --}}
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-fingerprint fa-2x me-3"></i>
                        <div>
                            <strong>Identificador Único (UID)</strong>
                            <p class="mb-0 small"><code>{{ $user->uid }}</code></p>
                        </div>
                    </div>
                </div>

                {{-- Sección: Información personal --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-user me-2"></i>Información personal
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="firstname"
                               value="{{ $user->firstname }}"
                               placeholder="Ej: Juan" required>
                        <small class="text-muted">Nombre del usuario</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lastname"
                               value="{{ $user->lastname }}"
                               placeholder="Ej: Pérez" required>
                        <small class="text-muted">Apellido del usuario</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Identificación</label>
                        <input type="text" class="form-control" name="identification"
                               value="{{ $user->identification }}"
                               placeholder="Ej: 1234567890">
                        <small class="text-muted">DNI, cédula o documento de identidad</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="cellphone"
                               value="{{ $user->cellphone }}"
                               placeholder="Ej: +34 600 123 456">
                        <small class="text-muted">Número de contacto</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="address" rows="2"
                                  placeholder="Dirección completa del usuario">{{ $user->address }}</textarea>
                        <small class="text-muted">Dirección física del usuario</small>
                    </div>
                </div>

                {{-- Sección: Cuenta y acceso --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-key me-2"></i>Cuenta y acceso
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email"
                               value="{{ $user->email }}"
                               placeholder="Ej: usuario@ejemplo.com" required>
                        <small class="text-muted">Email para acceso al sistema</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password"
                               placeholder="Dejar en blanco para mantener la actual">
                        <small class="text-muted">Solo completar si deseas cambiar la contraseña (mínimo 8 caracteres)</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $roleId => $roleName)
                                @php
                                    $userRoleId = optional($user->roles->first())->id;
                                @endphp
                                <option value="{{ $roleId }}" {{ $userRoleId == $roleId ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('-', ' ', $roleName)) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Rol del usuario en el sistema</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="available">
                            <option value="1" {{ $user->available == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $user->available == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        <small class="text-muted">Estado de la cuenta</small>
                    </div>
                </div>

                {{-- Sección: Información adicional --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-info-circle me-2"></i>Información adicional
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" class="form-control" name="company"
                               value="{{ $user->company }}"
                               placeholder="Nombre de la empresa">
                        <small class="text-muted">Empresa a la que pertenece</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Zona horaria</label>
                        <select class="form-select" name="timezone">
                            <option value="">Seleccionar zona horaria</option>
                            <option value="America/New_York" {{ $user->timezone == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                            <option value="America/Los_Angeles" {{ $user->timezone == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST)</option>
                            <option value="America/Chicago" {{ $user->timezone == 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST)</option>
                            <option value="America/Denver" {{ $user->timezone == 'America/Denver' ? 'selected' : '' }}>America/Denver (MST)</option>
                            <option value="Europe/London" {{ $user->timezone == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                            <option value="Europe/Madrid" {{ $user->timezone == 'Europe/Madrid' ? 'selected' : '' }}>Europe/Madrid (CET)</option>
                            <option value="Europe/Paris" {{ $user->timezone == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (CET)</option>
                            <option value="Asia/Tokyo" {{ $user->timezone == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                            <option value="Australia/Sydney" {{ $user->timezone == 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney (AEST)</option>
                        </select>
                        <small class="text-muted">Zona horaria del usuario</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="verified"
                                   id="verified" value="1" {{ $user->email_verified_at ? 'checked' : '' }}>
                            <label class="form-check-label" for="verified">
                                <strong>Usuario verificado</strong>
                                <p class="text-muted small mb-0">Marcar si el usuario ha sido verificado</p>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Información de auditoría --}}
                <div class="alert alert-light border">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus me-1"></i>
                                <strong>Creado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
                                ({{ $user->created_at->diffForHumans() }})
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-edit me-1"></i>
                                <strong>Actualizado:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                                ({{ $user->updated_at->diffForHumans() }})
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Actualizar Usuario
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('settings.users.index') }}" class="btn btn-light w-100 mb-2">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $("#formUsers").validate({
        rules: {
            firstname: {
                required: true,
                minlength: 2,
                maxlength: 100,
            },
            lastname: {
                required: true,
                minlength: 2,
                maxlength: 100,
            },
            email: {
                required: true,
                email: true,
            },
            password: {
                required: false,
                minlength: 8,
            },
            role: {
                required: true,
            },
            cellphone: {
                maxlength: 20,
            },
            identification: {
                maxlength: 50,
            }
        },
        messages: {
            firstname: {
                required: "El nombre es obligatorio.",
                minlength: "Debe contener al menos 2 caracteres.",
                maxlength: "No puede exceder los 100 caracteres."
            },
            lastname: {
                required: "El apellido es obligatorio.",
                minlength: "Debe contener al menos 2 caracteres.",
                maxlength: "No puede exceder los 100 caracteres."
            },
            email: {
                required: "El email es obligatorio.",
                email: "Ingrese un email válido."
            },
            password: {
                minlength: "La contraseña debe tener al menos 8 caracteres."
            },
            role: {
                required: "Debe seleccionar un rol."
            }
        },
        submitHandler: function(form) {
            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...');

            $.ajax({
                url: "{{ route('settings.users.update') }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Usuario actualizado correctamente');
                    setTimeout(function() {
                        window.location.href = "{{ route('settings.users.index') }}";
                    }, 1500);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Actualizar Usuario');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al actualizar el usuario. Intente nuevamente.');
                    }

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    }
                }
            });
        }
    });
});
</script>
@endpush
