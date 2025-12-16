@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                    <input type="hidden" id="uid" name="uid" value="{{ $user->uid }}">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">
                                Editar usuario:
                                <span class="badge bg-primary">
                                    {{ implode(', ', array_map(fn($r) => ucwords(str_replace('-', ' ', $r)), $userRoles)) ?: 'Sin rol' }}
                                </span>
                            </h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                        <label  class="form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                        <label  class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                        <label  class="form-label">Correo electronico</label>
                                        <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                        <label  class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña">
                                </div>
                            </div>

                            <div class="col-6 divShops d-none">
                                <div class="mb-3">
                                    <label class="form-label">Tiendas/Almacenes</label>
                                    <select class="form-control select2" id="shop" name="shop">
                                        <option value="">Seleccione una tienda/almacén</option>
                                        @foreach($shops as $id => $name)
                                            <option value="{{ $id }}" {{ $user->shop_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <label id="shop-error" class="error d-none" for="shop"></label>
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-control select2" id="available" name="available">
                                        <option value="1" {{ $user->available == 1 ? 'selected' : '' }}>Público</option>
                                        <option value="0" {{ $user->available == 0 ? 'selected' : '' }}>Oculto</option>
                                    </select>
                            </div>

                            @php
                                // Get the first role ID of the user
                                $userRoleId = optional($user->roles->first())->id;
                            @endphp

                            <div class="col-6 mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-control select2" id="roles" name="role" required>
                                    <option value="">Seleccione un rol</option>
                                    @foreach($roles as $id => $name)
                                        <option value="{{ $id }}"
                                            @if ($userRoleId == $id) selected @endif
                                            data-role-name="{{ strtolower($name) }}">
                                            {{ ucwords(str_replace('-', ' ', $name)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="errors d-none">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                            Guardar
                                    </button>
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
        Dropzone.autoDiscover = false;

        $(document).ready(function() {

            // Roles that require shop/warehouse assignment
            const rolesRequiringAssignment = {!! json_encode($rolesRequiringAssignment) !!};

            // Function to check if selected role requires shop assignment
            function roleRequiresShop() {
                const roleId = $('#roles').val();
                if (!roleId) return false;

                const selectedOption = $('#roles option:selected');
                const roleName = selectedOption.text().toLowerCase();

                // Check if the selected role name is in the rolesRequiringAssignment array
                return rolesRequiringAssignment.some(role => roleName.includes(role.toLowerCase()));
            }

            // Show/hide shop assignment field based on selected role
            function updateShopVisibility() {
                if (roleRequiresShop()) {
                    $('.divShops').removeClass("d-none");
                } else {
                    $('.divShops').addClass("d-none");
                }
            }

            // Handle role change
            $('#roles').change(function(e) {
                e.preventDefault();
                updateShopVisibility();
            });

            // Initialize on page load
            updateShopVisibility();

            jQuery.validator.addMethod(
                'emailExt',
                function (value, element, param) {
                    return value.match(
                        /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
                    )
                },
                'Porfavor ingrese email valido',
            );

            $("#formUsers").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    firstname: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    lastname: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    },
                    available: {
                        required: true,
                    },
                    role: {
                        required: true,
                    },
                    shops: {
                        required: false,
                    },
                    email: {
                        required: true,
                        email: true,
                        emailExt: true,
                    },
                    password: {
                        required: false,
                        minlength: 3,
                        maxlength: 100,
                    },

                },
                messages: {
                    firstname: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    lastname: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 3 caracter",
                        maxlength: "Debe contener al menos 100 caracter",
                    },
                    email: {
                        required: 'Tu email ingresar correo electrónico es necesario.',
                        email: 'Por favor, introduce una dirección de correo electrónico válida.',
                    },
                    password: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 6 caracter",
                        maxlength: "Debe contener al menos 10 caracter",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formUsers');
                    var formData = new FormData($form[0]);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.users.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
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
                                    window.location.href = "{{ route('manager.users') }}";
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



