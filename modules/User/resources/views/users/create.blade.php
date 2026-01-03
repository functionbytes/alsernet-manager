@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Crear Usuario'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">
            @csrf

            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Nuevo Usuario del Sistema</h5>
                        <p class="text-muted mb-0 small">Complete la información para crear un nuevo usuario</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('settings.users.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Sección: Información personal --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-user me-2"></i>Información personal
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="firstname"
                               placeholder="Ej: Juan" required>
                        <small class="text-muted">Nombre del usuario</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lastname"
                               placeholder="Ej: Pérez" required>
                        <small class="text-muted">Apellido del usuario</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Identificación</label>
                        <input type="text" class="form-control" name="identification"
                               placeholder="Ej: 1234567890">
                        <small class="text-muted">DNI, cédula o documento de identidad</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="cellphone"
                               placeholder="Ej: +34 600 123 456">
                        <small class="text-muted">Número de contacto</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="address" rows="2"
                                  placeholder="Dirección completa del usuario"></textarea>
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
                               placeholder="Ej: usuario@ejemplo.com" required>
                        <small class="text-muted">Email para acceso al sistema</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password"
                               placeholder="Contraseña segura" required>
                        <small class="text-muted">Mínimo 8 caracteres</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $roleId => $roleName)
                                <option value="{{ $roleName }}">{{ Str::title(str_replace('-', ' ', $roleName)) }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Rol del usuario en el sistema</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="available">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
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
                               placeholder="Nombre de la empresa">
                        <small class="text-muted">Empresa a la que pertenece</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Zona horaria</label>
                        <select class="form-select" name="timezone">
                            <option value="">Seleccionar zona horaria</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                            <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                            <option value="America/Chicago">America/Chicago (CST)</option>
                            <option value="America/Denver">America/Denver (MST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                            <option value="Europe/Madrid" selected>Europe/Madrid (CET)</option>
                            <option value="Europe/Paris">Europe/Paris (CET)</option>
                            <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                            <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
                        </select>
                        <small class="text-muted">Zona horaria del usuario</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="verified"
                                   id="verified" value="1">
                            <label class="form-check-label" for="verified">
                                <strong>Usuario verificado</strong>
                                <p class="text-muted small mb-0">Marcar si el usuario ha sido verificado</p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Crear Usuario
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
                required: true,
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
                required: "La contraseña es obligatoria.",
                minlength: "La contraseña debe tener al menos 8 caracteres."
            },
            role: {
                required: "Debe seleccionar un rol."
            }
        },
        submitHandler: function(form) {
            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creando...');

            $.ajax({
                url: "{{ route('settings.users.store') }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Usuario creado correctamente');
                    setTimeout(function() {
                        window.location.href = "{{ route('settings.users.index') }}";
                    }, 1500);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Crear Usuario');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al crear el usuario. Intente nuevamente.');
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
