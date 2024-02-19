@extends('layouts.app_no_nav')

@section('content')
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                @include('layouts.navbars.guest.navbar')
            </div>
        </div>
    </div>
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
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
                                        <input type="email" id="email_login" name="email" class="form-control form-control-lg" value="{{ old('email') ?? '' }}" aria-label="Email" placeholder="Correo">
                                        @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                    </div>
                                    <div class="flex flex-col mb-3">
                                        <input type="password" id="password_login" name="password" class="form-control form-control-lg" aria-label="Password" value="" onkeypress="changePassWord(event)" placeholder="Contraseña">
                                        @error('password') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                    </div>
                                    <!-- <div class="form-check form-switch">
                                        <input class="form-check-input" name="remember" type="checkbox" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">Remember me</label>
                                    </div> -->
                                    <!-- <div class="text-center">
                                    </div> -->
                                        
                                    <button type="submit" id="button-login" class="btn btn-lg btn-primary btn-lg w-100 mb-0">Ingresar</button>
                                    <button id="button-login-loading" class="btn btn-lg btn-primary btn-lg w-100 mb-0" style="display:none; float: left;" disabled>
                                        Cargando
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </button>
                           
                                </div>
                                <!-- <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-1 text-sm mx-auto">
                                        Forgot you password? Reset your password
                                        <a href="{{ route('reset-password') }}" class="text-primary text-gradient font-weight-bold">here</a>
                                    </p>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-4 text-sm mx-auto">
                                        Don't have an account?
                                        <a href="{{ route('register') }}" class="text-primary text-gradient font-weight-bold">Sign up</a>
                                    </p>
                                </div> -->
                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('/img/edificios.webp');
              background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-4"></span>
                                <img src="/img/logo_blanco.png" class="navbar-brand-img h-20 position-relative" style="width: 300px; align-self: center;" alt="main_logo-login">
                                <!-- <h4 class="mt-1 text-white font-weight-bolder position-relative">MAXIMO PH</h4> -->
                                <!-- <p class="text-white position-relative">BETA {{ config('app.version') }}</p> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
