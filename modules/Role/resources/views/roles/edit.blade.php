@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Editar Rol'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <form id="formRoles" enctype="multipart/form-data" role="form" onSubmit="return false">
            @csrf
            <input type="hidden" name="id" id="id" value="{{ $role->id }}">

            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Editar Rol: {{ $role->name }}</h5>
                        <p class="text-muted mb-0 small">Actualiza la información del rol en el sistema</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <a href="{{ route('settings.roles.show.permissions', $role->id) }}"
                               class="btn btn-warning" title="Gestionar permisos">
                                <i class="fas fa-lock me-2"></i>Permisos
                            </a>
                            <a href="{{ route('settings.roles.show.users', $role->id) }}"
                               class="btn btn-info" title="Ver usuarios asignados">
                                <i class="fas fa-users me-2"></i>Usuarios
                            </a>
                            @if(!in_array($role->name, ['super-admin', 'customer']))
                                <button type="button" class="btn btn-danger delete-btn"
                                        data-url="{{ route('settings.roles.destroy', $role->id) }}"
                                        data-title="¿Eliminar rol {{ $role->name }}?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
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
                               value="{{ $role->name }}"
                               placeholder="Ej: supervisor-inventario" required>
                        <small class="text-muted">Mínimo 3 caracteres, máximo 50. Use minúsculas y guiones</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug <span class="text-muted">(Auto-generado)</span></label>
                        <input type="text" class="form-control bg-light" name="slug"
                               value="{{ $role->slug ?? '' }}"
                               placeholder="Se genera automáticamente" readonly>
                        <small class="text-muted">Identificador único generado del nombre</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="3"
                                  placeholder="Describe el propósito y responsabilidades de este rol...">{{ $role->description ?? '' }}</textarea>
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
                            <option value="web" {{ $role->guard_name == 'web' ? 'selected' : '' }}>Web (Navegador)</option>
                            <option value="api" {{ $role->guard_name == 'api' ? 'selected' : '' }}>API (Token/OAuth)</option>
                        </select>
                        <small class="text-muted">Define el tipo de autenticación para este rol</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default"
                                       id="is_default" value="1" {{ $role->is_default ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    <strong>Rol por defecto</strong>
                                    <p class="text-muted small mb-0">Asignar automáticamente a nuevos usuarios</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @if(in_array($role->name, ['super-admin', 'customer']))
                    <div class="alert alert-warning border-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Rol del sistema:</strong> Este es un rol protegido del sistema. Algunas opciones están limitadas para preservar la integridad del sistema.
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
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

    {{-- Delete Modal --}}
    @include('theme.components.delete')

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
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');

            $.ajax({
                url: "{{ route('settings.roles.update', $role->id) }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Rol actualizado correctamente');
                    setTimeout(function() {
                        window.location.href = "{{ route('settings.roles.index') }}";
                    }, 1500);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Cambios');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al actualizar el rol. Intente nuevamente.');
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
