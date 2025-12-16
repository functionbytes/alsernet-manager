@extends('layouts.managers')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formRoles" enctype="multipart/form-data" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">Crear nuevo rol</h5>
                                <p class="card-subtitle mb-0 mt-2">Complete la información para registrar un nuevo rol en el sistema.</p>
                            </div>
                            <a href="{{ route('manager.roles') }}" class="btn btn-light">
                                <i class="fa-duotone fa-arrow-left"></i> Atrás
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Ej: supervisor-inventario" required>
                                <small class="text-muted">Mínimo 3 caracteres, máximo 50</small>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" name="slug" placeholder="slug-del-rol" readonly>
                                <small class="text-muted">Se genera automáticamente del nombre</small>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Describe el propósito de este rol..."></textarea>
                                <small class="text-muted">Máximo 255 caracteres</small>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Guard <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="guard_name" required>
                                    <option value="web">Web</option>
                                    <option value="api">API</option>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-check form-check-lg d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1">
                                    <span class="form-check-label ms-2">Rol por defecto</span>
                                </label>
                                <small class="text-muted d-block">Los nuevos usuarios usarán este rol automáticamente</small>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-3 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light">
                                        <i class="fa-duotone fa-save"></i> Crear rol
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
                        url: "{{ route('manager.roles.store') }}",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
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
