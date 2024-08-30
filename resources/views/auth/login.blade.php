@extends('layouts.app_no_nav')

@section('content')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        .card-principal {
            position: absolute;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, 10%);
            text-align: center;
            width: 400px;
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
                <div class="card card-plain" style="background-color: #081329;">
                    <div class="card-header card-principal-header pb-0 text-start">
                        <div style="text-align: center;">
                            <img src="/img/logo_base_sin_texto.png" class="navbar-brand-img h-20 position-relative" style="width: 40px; align-self: center;" alt="main_logo-login">
                            <h4 class="mt-1 text-white font-weight-bolder position-relative">MAXIMO PH</h4>
                        </div>
                    </div>
                    <div class="card-body" style="text-align: center; background-color: #081329; align-self: center;">
                        <div style="width: 300px;">
                        <p id="error-login" style="color: red; margin-bottom: 0.3rem; font-size: 14px; display:none;">¡Usuario o contraseña incorrectos!</p>
                            <h4 class="mt-1 text-white font-weight-bolder position-relative" style="font-size: 17px; padding-top: 7px; padding-bottom: 15px;">
                                INICIAR SESION
                            </h4>
                            <!-- <div class="flex flex-col mb-3">
                                <input type="email" id="email_login" name="email" class="form-control form-control-lg" value="" aria-label="Email" placeholder="Correo">
                            </div> -->
                            <div class="mb-3" style="margin-bottom: 0.5rem !important;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Correo electronico</label>
                                <input type="email" class="form-control form-control-sm" id="email_login" name="email_login">
                            </div>
                            <!-- <div class="mb-3" style="margin-bottom: 1.3rem !important;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Contraseña</label>
                                <input type="password" id="password_login" name="password" class="form-control form-control-sm" aria-label="Password" value="" onkeypress="changePassWord(event)">
                            </div> -->

                            <div class="form-group" style="margin-bottom: 1.3rem !important;">
                                <label for="exampleFormControlInput1" class="form-label" style="float: left; color: white; font-size: 13px;">Contraseña</label>
                                <div class="input-group" id="show_hide_password">
                                    <input class="form-control form-control-sm" type="password" id="password_login" name="password_login">
                                    <div class="input-group-addon button-password">
                                        <a ><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>

                            <button id="button-login" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%;">Iniciar sesion</button>
                            <button id="button-login-loading" type="button" class="btn btn-primary btn-sm btn-grad" style="height: 35px; width: 100%; display: none;" disabled>
                                Cargando
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>
                            <p><a class="link-opacity-100 link-item-login" href="#">¿Has olvidado la contraseña?</a></p>
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

            <!-- <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Iniciar sesión</h4>
                                    <p class="mb-0">Ingrese correo y contraseña para continuar</p>
                                </div>
                                <div class="card-body">

                                    <p id="error-login" style="color: red; display:none;">¡Usuario o contraseña incorrectos!</p>

                                    <div class="flex flex-col mb-3">
                                        <input type="email" id="email_login" name="email" class="form-control form-control-lg" value="" aria-label="Email" placeholder="Correo">
                                        @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                    </div>
                                    <div class="flex flex-col mb-3">
                                        <input type="password" id="password_login" name="password" class="form-control form-control-lg" aria-label="Password" value="" onkeypress="changePassWord(event)" placeholder="Contraseña">
                                        @error('password') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                    </div>
                                        
                                    <button type="submit" id="button-login" class="btn btn-lg btn-primary btn-lg w-100 mb-0">Ingresar</button>
                                    <button id="button-login-loading" class="btn btn-lg btn-primary btn-lg w-100 mb-0" style="display:none; float: left;" disabled>
                                        Cargando
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </button>
                           
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('/img/edificios.webp');
              background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-4"></span>
                                <img src="/img/logo_base_sin_texto.png" class="navbar-brand-img h-20 position-relative" style="width: 55px; align-self: center;" alt="main_logo-login">
                                <h4 class="mt-1 text-white font-weight-bolder position-relative">MAXIMO PH</h4>
                                <p class="text-white position-relative">VERSION {{ config('app.version') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
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
                onGet();
            </script>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@endsection
