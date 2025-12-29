@extends('layouts.managers')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formRoles" enctype="multipart/form-data" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" id="id" value="{{ $role->id }}">

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">Editar rol: {{ $role->name }}</h5>
                                <p class="card-subtitle mb-0 mt-2">Actualiza la información del rol en el sistema.</p>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('manager.roles.show.permissions', $role->id) }}" class="btn btn-sm btn-warning" title="Gestionar permisos">
                                    <i class="fa-duotone fa-lock"></i> Permisos
                                </a>
                                <a href="{{ route('manager.roles.show.users', $role->id) }}" class="btn btn-sm btn-info" title="Ver usuarios asignados">
                                    <i class="fa-duotone fa-users"></i> Usuarios
                                </a>
                                @can('role:delete')
                                    @if(!in_array($role->name, ['super-admin', 'customer']))
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                            <i class="fa-duotone fa-trash"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ $role->name }}" placeholder="Ej: supervisor-inventario" required>
                                <small class="text-muted">Mínimo 3 caracteres, máximo 50</small>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" name="slug" value="{{ $role->slug ?? '' }}" placeholder="slug-del-rol" readonly>
                                <small class="text-muted">Se genera automáticamente del nombre</small>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Describe el propósito de este rol...">{{ $role->description ?? '' }}</textarea>
                                <small class="text-muted">Máximo 255 caracteres</small>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Guard <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="guard_name" required>
                                    <option value="web" {{ $role->guard_name == 'web' ? 'selected' : '' }}>Web</option>
                                    <option value="api" {{ $role->guard_name == 'api' ? 'selected' : '' }}>API</option>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-check form-check-lg d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" {{ $role->is_default ? 'checked' : '' }}>
                                    <span class="form-check-label ms-2">Rol por defecto</span>
                                </label>
                                <small class="text-muted d-block">Los nuevos usuarios usarán este rol automáticamente</small>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-3 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light">
                                        <i class="fa-duotone fa-save"></i> Guardar cambios
                                    </button>
                                    <a href="{{ route('manager.roles') }}" class="btn btn-light px-4">
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar el rol <strong>{{ $role->name }}</strong>?
                    <div class="alert alert-warning mt-3">
                        <i class="fa-duotone fa-exclamation-triangle"></i>
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize form validation
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
                submitHandler: function (form) {
                    var formData = new FormData(form);
                    var $submitButton = $(form).find('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.roles.update', $role->id) }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function (response) {
                            if (response.success === true) {
                                toastr.success(response.message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right",
                                    timeOut: 1500,
                                    onHidden: function () {
                                        window.location.href = "{{ route('manager.roles') }}";
                                    }
                                });
                            } else {
                                toastr.warning(response.message, "Operación fallida", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right",
                                    timeOut: 2000,
                                    onHidden: function () {
                                        $submitButton.prop('disabled', false);
                                    }
                                });
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Error desconocido';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).map(err => err[0]).join('<br>');
                            }
                            toastr.error(errorMessage, "Error de validación", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            $submitButton.prop('disabled', false);
                        }
                    });
                }
            });

            // Handle delete confirmation
            $('#confirmDeleteBtn').click(function () {
                var roleId = {{ $role->id }};
                var $button = $(this);
                $button.prop('disabled', true).html('<i class="fa-duotone fa-spinner"></i> Eliminando...');

                $.ajax({
                    url: "{{ route('manager.roles.destroy', $role->id) }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "DELETE",
                    success: function (response) {
                        if (response.success === true) {
                            toastr.success(response.message, "Rol eliminado", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right",
                                timeOut: 1500,
                                onHidden: function () {
                                    window.location.href = "{{ route('manager.roles') }}";
                                }
                            });
                        } else {
                            toastr.error(response.message, "Error", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });
                            $button.prop('disabled', false).html('Eliminar');
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Error al eliminar el rol';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage, "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });
                        $button.prop('disabled', false).html('Eliminar');
                    }
                });
            });

            // Auto-generate slug from name
            $('input[name="name"]').on('keyup', function () {
                let name = $(this).val();
                let slug = name.toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('input[name="slug"]').val(slug);
            });
        });
    </script>
@endpush
