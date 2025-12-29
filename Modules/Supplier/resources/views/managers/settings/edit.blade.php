@extends('layouts.managers')

@section('content')

    <div class="card w-100">

        <form id="formSupplier" enctype="multipart/form-data" role="form" onSubmit="return false">

            {{ csrf_field() }}

            <input type="hidden" id="slack" name="slack" value="{{ $supplier->uid }}">

            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <h5 class="mb-0">Editar proveedor: {{ $supplier->name }}</h5>
                </div>
                <p class="card-subtitle mb-3 mt-3">
                    Modifica la configuración del proveedor <mark><code>{{ $supplier->code }}</code></mark>. Los cambios afectarán a todas las fuentes asociadas.
                </p>

                <div class="row">

                    <!-- Código -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Código
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="code"
                                   name="code"
                                   value="{{ $supplier->code }}"
                                   required
                                   placeholder="Código único del proveedor"
                                   pattern="[A-Z0-9_-]+"
                                   title="Solo letras mayúsculas, números, guiones y guiones bajos">
                            <small class="form-text text-muted">Identificador único (mayúsculas, ej: NIKE)</small>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Nombre
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   value="{{ $supplier->name }}"
                                   required
                                   placeholder="Nombre del proveedor">
                            <small class="form-text text-muted">Nombre descriptivo que se mostrará en la interfaz</small>
                        </div>
                    </div>

                    <!-- Sitio Web -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Sitio web</label>
                            <input type="url"
                                   class="form-control"
                                   id="website"
                                   name="website"
                                   value="{{ $supplier->website }}"
                                   placeholder="https://proveedor.com">
                            <small class="form-text text-muted">URL del sitio web del proveedor</small>
                        </div>
                    </div>

                    <!-- Prioridad -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">
                                Prioridad
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="priority"
                                   name="priority"
                                   value="{{ $supplier->priority }}"
                                   required
                                   min="1"
                                   max="100"
                                   placeholder="1">
                            <small class="form-text text-muted">Orden de prioridad (1-100, menor primero)</small>
                        </div>
                    </div>

                    <!-- Email de Contacto -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Email de contacto</label>
                            <input type="email"
                                   class="form-control"
                                   id="contact_email"
                                   name="contact_email"
                                   value="{{ $supplier->contact_email }}"
                                   placeholder="contacto@proveedor.com">
                            <small class="form-text text-muted">Email de contacto del proveedor</small>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Estado</label>
                            <select class="form-select select2"
                                    id="is_active"
                                    name="is_active"
                                    data-placeholder="Seleccionar estado...">
                                <option value="1" {{ $supplier->is_active == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ $supplier->is_active == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <label id="is_active-error" class="error d-none" for="is_active"></label>
                            <small class="form-text text-muted">Solo los proveedores activos se sincronizarán</small>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="control-label col-form-label">Descripción</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Descripción del proveedor...">{{ $supplier->description }}</textarea>
                            <small class="form-text text-muted">Información adicional sobre el proveedor (opcional)</small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                    Guardar
                </button>
                <a href="{{ route('manager.settings.suppliers.index') }}" class="btn btn-secondary px-4 waves-effect waves-light mt-2 w-100">
                    Volver
                </a>
            </div>

        </form>

    </div>

@endsection



@push('scripts')

  <script type="text/javascript">

    $(document).ready(function() {

      // Initialize Select2
      $('.select2').select2({
        allowClear: false,
        minimumResultsForSearch: Infinity
      });

      $("#formSupplier").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
          code: {
            required: true,
            minlength: 2,
            maxlength: 20,
          },
          name: {
            required: true,
            minlength: 3,
            maxlength: 255,
          },
          website: {
            url: true,
          },
          priority: {
            required: true,
            number: true,
            min: 1,
            max: 100,
          },
          contact_email: {
            email: true,
          },
          is_active: {
            required: true,
          },

        },
        messages: {
          code: {
            required: "El código es necesario.",
            minlength: "Debe contener al menos 2 caracteres",
            maxlength: "No debe exceder 20 caracteres",
          },
          name: {
            required: "El nombre es necesario.",
            minlength: "Debe contener al menos 3 caracteres",
            maxlength: "No debe exceder 255 caracteres",
          },
          website: {
            url: "Debe ser una URL válida.",
          },
          priority: {
            required: "La prioridad es necesaria.",
            number: "Debe ser un número.",
            min: "La prioridad mínima es 1",
            max: "La prioridad máxima es 100",
          },
          contact_email: {
            email: "Debe ser un email válido.",
          },
          is_active: {
            required: "Es necesario un estado.",
          },
        },
        submitHandler: function(form) {

          var $form = $('#formSupplier');
          var formData = new FormData($form[0]);
          var slack = $("#slack").val();
          var code = $("#code").val();
          var name = $("#name").val();
          var website = $("#website").val();
          var priority = $("#priority").val();
          var contact_email = $("#contact_email").val();
          var description = $("#description").val();
          var is_active = $("#is_active").val();

          formData.append('slack', slack);
          formData.append('code', code);
          formData.append('name', name);
          formData.append('website', website);
          formData.append('priority', priority);
          formData.append('contact_email', contact_email);
          formData.append('description', description);
          formData.append('is_active', is_active);

          var $submitButton = $('button[type="submit"]');
          $submitButton.prop('disabled', true);

          $.ajax({
            url: "{{ route('manager.settings.suppliers.update', $supplier->uid) }}",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "PUT",
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {

              if(response.success == true){

                message = response.message;

                toastr.success(message, "Operación exitosa", {
                  closeButton: true,
                  progressBar: true,
                  positionClass: "toast-bottom-right"
                });

                setTimeout(function() {
                  window.location = "{{ route('manager.settings.suppliers.index') }}";
                }, 2000);

              }else{

                $submitButton.prop('disabled', false);
                error = response.message;

                toastr.warning(error, "Operación fallida", {
                  closeButton: true,
                  progressBar: true,
                  positionClass: "toast-bottom-right"
                });

                $('.errors').text(error);
                $('.errors').removeClass('d-none');

              }

            }
          });

        }

      });

    });

  </script>


@endpush
