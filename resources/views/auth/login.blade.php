@extends('layouts.app_no_nav')

@section('content')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        .card-principal {
            text-align: center;
            width: 400px;
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .card-principal-header {
            background-image: url("https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo-header.jpg");
            height: 160px !important;
            background-position: top;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        html {
            background-color: #075260;
            background-image: url(https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/%E2%80%94Pngtree%E2%80%94architecture%20high%20rise%20building%20drawing_6959760.png);
            background-position: bottom;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
                 
        .btn-grad {
            background-image: linear-gradient(to right, #1A2980 0%, #096c7e 51%, #1A2980 100%);
            text-align: center;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;
            border-radius: 7px;
            display: block;
        }

        .btn-grad:hover {
            background-position: right center; /* change the direction of the change here */
            color: #fff;
            text-decoration: none;
        }

        .link-item-login {
            font-size: 12px; color: rgb(166 241 255) !important;
        }

        .button-password {
            background-image: linear-gradient(to right, #1A2980 0%, #096c7e 51%, #1A2980 100%);
            padding: 3px;
            cursor: pointer;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;
            border-bottom-right-radius: 3px;
            border-top-right-radius: 3px;
            display: block;
        }

        .button-password:hover {
            background-position: right center; /* change the direction of the change here */
            color: #fff;
            text-decoration: none;
        }
         
    </style>

    <main class="main-content  mt-0">
        <section>
            <!-- <div class="page-header min-vh-100">
                hola
            </div> -->
            <div class="container card-principal">
                <div class="card" style="background-color: #081329;">
                    <div class="card-header card-principal-header pb-0 text-start">
                        <div style="text-align: center;">
                            <img src="/img/logo_base_sin_texto.png" class="navbar-brand-img h-20 position-relative" style="width: 40px; align-self: center;" alt="main_logo-login">
                            <h4 class="mt-1 text-white font-weight-bolder position-relative">MAXIMO PH</h4>
                        </div>
                    </div>
                    <div class="card-body" style="text-align: center; background-color: #081329; align-self: center;">
                        <div style="width: 300px;">
                            <p id="error-login" style="color: red; margin-bottom: 0.3rem; font-size: 14px; display:none;">¡Usuario o contraseña incorrectos!</p>
                            <p id="error-recover" style="color: red; margin-bottom: 0.3rem; font-size: 14px; display:none;">¡El correo electronico no existe!</p>
                            <p id="success-recover" style="color: green; margin-bottom: 0.3rem; font-size: 14px; display:none;">¡Se ha cambiado la contraseña con exito!</p>
                            <h4 id="texto-login" class="mt-1 text-white font-weight-bolder position-relative" style="font-size: 17px; padding-top: 7px; padding-bottom: 15px;">
                                INICIAR SESION
                            </h4>

                            <h4 id="texto-recover" class="mt-1 text-white font-weight-bolder position-relative" style="font-size: 17px; padding-top: 7px; padding-bottom: 15px; display: none;" >
                                RECUPERAR CONTRASEÑA
                            </h4>

                            <div id="input_email_login" class="mb-3" style="margin-bottom: 0.5rem !important;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Correo electronico</label>
                                <input type="email" class="form-control form-control-sm" id="email_login" name="email_login">
                            </div>

                            <div id="input_code_login" class="mb-3" style="margin-bottom: 0.5rem !important; display: none;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Codigo de verificación</label>
                                <input type="text" maxlength="5" class="form-control form-control-sm" id="code_login" name="code_login" placeholder="5-sigit-code">
                            </div>

                            <div id="input-password" class="form-group">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Contraseña</label>
                                <div class="input-group" id="show_hide_password">
                                    <input class="form-control form-control-sm" type="password" id="password_login" name="password_login">
                                    <div class="input-group-addon button-password">
                                        <a ><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div id="input-new-password" class="form-group" style="display: none;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Contraseña nueva</label>
                                <div class="input-group" id="show_hide_new_password">
                                    <input class="form-control form-control-sm" type="password" id="new_password_login" name="new_password_login">
                                    <div class="input-group-addon button-password">
                                        <a ><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div id="input-retry-password" class="form-group" style="display: none;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Repetir contraseña</label>
                                <div class="input-group" id="show_hide_new_password_retry">
                                    <input class="form-control form-control-sm" type="password" id="new_password_retry_login" name="new_password_retry_login">
                                    <div class="input-group-addon button-password">
                                        <a ><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>

                            <button id="button-login" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; margin-top: 1.5rem;">Iniciar sesion</button>
                            <button id="button-login-loading" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.5rem;" disabled>
                                Cargando
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>

                            <button id="button-recover" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;">Buscar</button>
                            <button id="button-recover-loading" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;" disabled>
                                Buscando
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>

                            <button id="cambiar-password" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;">Actualizar contraseña</button>
                            <button id="button-confir-code" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;">Validar</button>
                            <button id="button-resend" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;">Volver a enviar email</button>
                            <button id="button-resend-disabled" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;" disabled>Volver a enviar email (60)</button>
                            <button id="button-resend-loading" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none; margin-top: 1.1rem;" disabled>
                                Enviando correo
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>

                            <p>
                                <a id="link-recover" class="link-opacity-100 link-item-login" style="cursor: pointer;">¿Has olvidado la contraseña?</a>
                                <a id="link-login" class="link-opacity-100 link-item-login" style="display: none; cursor: pointer;">Iniciar sesion</a>
                            </p>

                            <p class="text-white position-relative" style="margin-bottom: 0px; font-size: 12px;">VERSION {{ config('app.version') }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    
                    <h4 class="mt-1 text-white font-weight-bolder position-relative" style="font-size: 12px; padding-top: 7px; padding-bottom: 15px; letter-spacing: 0.25em; cursor:pointer;">
                        POWERED BY
                        <img src="/img/logo_contabilidad.png"  style="width: 40px; align-self: center;" alt="main_logo-login">
                    </h4>
                </div>
            </div>

            <script type="module">
                async function onGet() {
                    let url = "https://api.ipify.org?format=json";
                    var headers = {}

                    let response = await fetch("https://api.ipify.org?format=json", {
                        method : "GET",
                        mode: 'cors',
                        headers: headers
                    })
                    let data = await response.json();
                    localStorage.setItem("ip_geo", data ? data.ip: null);
                }
                $(document).ready(function() {
                    $("#show_hide_password a").on('click', function(event) {
                        event.preventDefault();
                        if($('#show_hide_password input').attr("type") == "text"){
                            $('#show_hide_password input').attr('type', 'password');
                            $('#show_hide_password i').addClass( "fa-eye-slash" );
                            $('#show_hide_password i').removeClass( "fa-eye" );
                        }else if($('#show_hide_password input').attr("type") == "password"){
                            $('#show_hide_password input').attr('type', 'text');
                            $('#show_hide_password i').removeClass( "fa-eye-slash" );
                            $('#show_hide_password i').addClass( "fa-eye" );
                        }
                    });
                });
                $(document).ready(function() {
                    $("#show_hide_new_password a").on('click', function(event) {
                        event.preventDefault();
                        if($('#show_hide_new_password input').attr("type") == "text"){
                            $('#show_hide_new_password input').attr('type', 'password');
                            $('#show_hide_new_password i').addClass( "fa-eye-slash" );
                            $('#show_hide_new_password i').removeClass( "fa-eye" );
                        }else if($('#show_hide_new_password input').attr("type") == "password"){
                            $('#show_hide_new_password input').attr('type', 'text');
                            $('#show_hide_new_password i').removeClass( "fa-eye-slash" );
                            $('#show_hide_new_password i').addClass( "fa-eye" );
                        }
                    });
                });
                $(document).ready(function() {
                    $("#show_hide_new_password_retry a").on('click', function(event) {
                        event.preventDefault();
                        if($('#show_hide_new_password_retry input').attr("type") == "text"){
                            $('#show_hide_new_password_retry input').attr('type', 'password');
                            $('#show_hide_new_password_retry i').addClass( "fa-eye-slash" );
                            $('#show_hide_new_password_retry i').removeClass( "fa-eye" );
                        }else if($('#show_hide_new_password_retry input').attr("type") == "password"){
                            $('#show_hide_new_password_retry input').attr('type', 'text');
                            $('#show_hide_new_password_retry i').removeClass( "fa-eye-slash" );
                            $('#show_hide_new_password_retry i').addClass( "fa-eye" );
                        }
                    });
                });
                onGet();

            </script>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@endsection
