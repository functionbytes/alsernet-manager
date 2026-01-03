@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Crear Rol'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <form id="formRoles" enctype="multipart/form-data" role="form" onSubmit="return false">
            @csrf

            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Nuevo Rol del Sistema</h5>
                        <p class="text-muted mb-0 small">Complete la información para crear un nuevo rol</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('settings.roles.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Sección: Información básica --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-info-circle me-2"></i>Información básica
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               placeholder="Ej: supervisor-inventario" required>
                        <small class="text-muted">Mínimo 3 caracteres, máximo 50. Use minúsculas y guiones</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug <span class="text-muted">(Auto-generado)</span></label>
                        <input type="text" class="form-control bg-light" name="slug"
                               placeholder="Se genera automáticamente" readonly>
                        <small class="text-muted">Identificador único generado del nombre</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="3"
                                  placeholder="Describe el propósito y responsabilidades de este rol..."></textarea>
                        <small class="text-muted">Máximo 255 caracteres. Sea claro y conciso</small>
                    </div>
                </div>

                {{-- Sección: Configuración --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-cog me-2"></i>Configuración
                </h6>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Guard <span class="text-danger">*</span></label>
                        <select class="form-select" name="guard_name" required>
                            <option value="web" selected>Web (Navegador)</option>
                            <option value="api">API (Token/OAuth)</option>
                        </select>
                        <small class="text-muted">Define el tipo de autenticación para este rol</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default"
                                       id="is_default" value="1">
                                <label class="form-check-label" for="is_default">
                                    <strong>Rol por defecto</strong>
                                    <p class="text-muted small mb-0">Asignar automáticamente a nuevos usuarios</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Información sobre permisos --}}
                <div class="alert alert-info border-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Asignación de permisos:</strong> Una vez creado el rol, podrá asignar permisos usando la opción
                    "Gestionar Permisos" desde la lista de roles o desde la página de detalle del rol.
                </div>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Crear Rol
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('settings.roles.index') }}" class="btn btn-light w-100 mb-2">
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
    // Auto-generate slug from name
    $('input[name="name"]').on('keyup', function() {
        const name = $(this).val();
        const slug = name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('input[name="slug"]').val(slug);
    });

    // Form validation
    $("#formRoles").validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 50,
            },
            description: {
                maxlength: 255,
            },
            guard_name: {
                required: true,
            }
        },
        messages: {
            name: {
                required: "El nombre del rol es obligatorio.",
                minlength: "Debe contener al menos 3 caracteres.",
                maxlength: "No puede exceder los 50 caracteres."
            },
            description: {
                maxlength: "La descripción no puede exceder 255 caracteres."
            },
            guard_name: {
                required: "Debe seleccionar un guard."
            }
        },
        submitHandler: function(form) {
            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creando...');

            $.ajax({
                url: "{{ route('settings.roles.store') }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Rol creado correctamente');
                    setTimeout(function() {
                        window.location.href = "{{ route('settings.roles.index') }}";
                    }, 1500);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Crear Rol');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al crear el rol. Intente nuevamente.');
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
