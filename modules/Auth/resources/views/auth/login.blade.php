@extends('layouts.auth')

@section('title', 'Ingresar')

@section('content')

    <div id="main-wrapper">
        <div class="position-relative overflow-hidden auth-bg min-vh-100 w-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100 my-5 my-xl-0">
                    <div class="col-md-9 d-flex flex-column justify-content-center">
                        <div class="card mb-0 bg-body auth-login m-auto w-100">
                            <div class="row gx-0">
                                <!-- ------------------------------------------------- -->
                                <!-- Part 1 -->
                                <!-- ------------------------------------------------- -->
                                <div class="col-xl-6 border-end">
                                    <div class="row justify-content-center py-4">
                                        <div class="col-lg-11">
                                            <div class="card-body">
                                                <a href="../horizontal/index.html" class="text-nowrap logo-img d-block mb-4 w-100">
                                                    <img src="../assets/images/logos/logo.svg" class="dark-logo" alt="Logo-Dark">
                                                </a>
                                                <h2 class="lh-base mb-4">Let's get you signed in</h2>
                                                <div class="row">
                                                    <div class="col-6 mb-2 mb-sm-0">
                                                        <a class="btn btn-white shadow-sm text-dark link-primary border fw-semibold d-flex align-items-center justify-content-center rounded-1 py-6" href="javascript:void(0)" role="button">
                                                            <img src="../assets/images/svgs/facebook-icon.svg" alt="matdash-img" class="img-fluid me-2" width="18" height="18">
                                                            <span class="d-none d-xxl-inline-flex"> Sign in with </span>&nbsp; Facebook
                                                        </a>
                                                    </div>
                                                    <div class="col-6">
                                                        <a class="btn btn-white shadow-sm text-dark link-primary border fw-semibold d-flex align-items-center justify-content-center rounded-1 py-6" href="javascript:void(0)" role="button">
                                                            <img src="../assets/images/svgs/google-icon.svg" alt="matdash-img" class="img-fluid me-2" width="18" height="18">
                                                            <span class="d-none d-xxl-inline-flex"> Sign in with </span>&nbsp; Google
                                                        </a>

                                                    </div>
                                                </div>
                                                <div class="position-relative text-center my-4">
                                                    <p class="mb-0 fs-12 px-3 d-inline-block bg-body z-index-5 position-relative">Or sign in with
                                                        email
                                                    </p>
                                                    <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
                                                </div>
                                                <form id="formLogin" class="row sign-in-form" enctype="multipart/form-data" role="form" onSubmit="return false">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="exampleInputEmail1" class="form-label">Correo electrónico</label>
                                                        <input class="form-control email" id="email" type="text" name="email"  placeholder="example@example.com" >
                                                    </div>
                                                    <div class="mb-4">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <label for="exampleInputPassword1" class="form-label">Contraseña</label>
                                                        </div>
                                                        <input class="form-control password" id="password" type="password" name="password" placeholder="* * * * * * * * *">
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked="">
                                                            <label class="form-check-label text-dark" for="flexCheckChecked">
                                                                Keep me logged in
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn--theme hover--theme submit">Ingresar</button>
                                                    <div class="d-flex align-items-center">
                                                        <p class="fs-12 mb-0 fw-medium">Don’t have an account yet?</p>
                                                        <a class="text-primary fw-bolder ms-2" href="../horizontal/authentication-register2.html">Sign Up Now</a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>X
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {


            $(".position-relative span").on("click", function () {
                const input = $(this).siblings("input"); // Obtener el campo de entrada asociado
                const currentType = input.attr("type"); // Obtener el tipo actual del input

                // Alternar entre 'password' y 'text'
                const newType = currentType === "password" ? "text" : "password";
                input.attr("type", newType);

                // Cambiar el icono del ojo (opcional)
                const svg = $(this).find("svg");
                if (newType === "text") {
                    svg.attr("fill", "gray"); // Cambiar el color del ícono al mostrar contraseña
                } else {
                    svg.attr("fill", "currentColor"); // Restaurar el color al ocultar contraseña
                }
            });


            jQuery.validator.addMethod('emailExt', function(value, element, param) {
                return value.match(/^(([^<>()[\]\.,;:\s@"]+(\.[^<>()[\]\.,;:\s@"]+)*)|(".+"))@(([^<>()[\]\.,;:\s@"]+\.)+[^<>()[\]\.,;:\s@"]{2,})$/i);
            }, 'Por favor, ingrese un email válido.');

            $("#formLogin").validate({
                ignore: ".ignore",
                rules: {
                    email: {
                        required: true,
                        email: true,
                        emailExt: true,
                    },
                    password: {
                        required: true,
                        minlength: 6,
                        maxlength: 100,
                    },
                },
                messages: {
                    email: {
                        required: 'El campo de correo electrónico es necesario.',
                        email: 'Por favor, introduce una dirección de correo electrónico válida.',
                    },
                    password: {
                        required: 'El campo de contraseña es necesario.',
                        minlength: 'Debe contener al menos 6 caracteres.',
                        maxlength: 'Debe contener como máximo 100 caracteres.',
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formLogin');
                    var formData = new FormData($form[0]);
                    var email = $("#email").val();
                    var password = $("#password").val();
                    var remember = $("#remember").prop("checked");

                    formData.append('email', email);
                    formData.append('password', password);
                    formData.append('remember', remember);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('auth.login') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            if(response.success == true){
                                window.location.href = response.redirect;
                            }else{
                                $submitButton.prop('disabled', false);
                                error = response.message;
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
