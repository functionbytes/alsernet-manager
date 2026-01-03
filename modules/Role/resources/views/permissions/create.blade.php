@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Crear Permiso'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <form id="formPermissions" enctype="multipart/form-data" role="form" onSubmit="return false">
            @csrf

            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Nuevo Permiso del Sistema</h5>
                        <p class="text-muted mb-0 small">Complete la información para crear un nuevo permiso</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('settings.permissions.index') }}" class="btn btn-light">
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
                        <label class="form-label">Nombre del Permiso <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               placeholder="Ej: users.create" required>
                        <small class="text-muted">Use formato: módulo.acción (ej: products.view, orders.edit)</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Guard <span class="text-danger">*</span></label>
                        <select class="form-select" name="guard_name" required>
                            <option value="web" selected>Web (Navegador)</option>
                            <option value="api">API (Token/OAuth)</option>
                        </select>
                        <small class="text-muted">Define el tipo de autenticación para este permiso</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="3"
                                  placeholder="Describe qué permite hacer este permiso..."></textarea>
                        <small class="text-muted">Máximo 255 caracteres. Sea claro y conciso</small>
                    </div>
                </div>

                {{-- Sección: Información --}}
                <div class="alert alert-info border-info">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Convención de nombres:</strong> Use el formato "módulo.acción" para mantener consistencia.
                    Ejemplos: <code>users.view</code>, <code>products.create</code>, <code>orders.delete</code>
                </div>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Crear Permiso
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('settings.permissions.index') }}" class="btn btn-light w-100 mb-2">
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
    $("#formPermissions").validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100,
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
                required: "El nombre del permiso es obligatorio.",
                minlength: "Debe contener al menos 3 caracteres.",
                maxlength: "No puede exceder los 100 caracteres."
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
                url: "{{ route('settings.permissions.store') }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Permiso creado correctamente');
                    setTimeout(function() {
                        window.location.href = "{{ route('settings.permissions.index') }}";
                    }, 1500);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Crear Permiso');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al crear el permiso. Intente nuevamente.');
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
