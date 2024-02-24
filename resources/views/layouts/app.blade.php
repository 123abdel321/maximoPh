<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_base.png">
    <title>
        Maximo PH
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="assets/js/sistema/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="assets/css/argon-dashboard.css" rel="stylesheet" />
    <!-- DATATABLE -->
    <link href="assets/css/sistema/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/responsive.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/app.css" rel="stylesheet" />
    <!-- SELECT 2 -->
    <link href="assets/css/sistema/select2.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

</head>

<body class="{{ $class ?? '' }} " style="background-color: #060e26;">

    @guest
        @yield('content')
    @endguest

    @auth
        @if (in_array(request()->route()->getName(), ['sign-in-static', 'sign-up-static', 'login', 'register', 'recover-password', 'rtl', 'virtual-reality']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile', 'profile-static']))
                <div class="min-height-100 bg-dark position-absolute w-100 fondo-sistema" onclick="closeMenu()">
                    
                </div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav', ['menus', $menus])
            @include('layouts.navbars.auth.topnav')
            <div id="contenerdores-views" class="tab-content clearfix" onclick="closeMenu()">
                <main class="tab-pane main-content border-radius-lg change-view active" style="margin-left: 5px;" id="containner-dashboard">
                </main>
            </div>
            <br/>
            @include('components.fixed-plugin')
        @endif
    @endauth

    <!-- MODAL USUARIO ACCIÓN-->
    <div class="modal fade" id="modal-usuario-accion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
        <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modal-title-usuario-accion">Creado por</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row">  

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Usuario</label>
                        <input id="usuario_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Correo</label>
                        <input id="correo_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Fecha acción</label>
                        <input id="fecha_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger ml-auto" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
    </div>
    <!-- MODAL NIT INFORMACIÓN-->
    <div class="modal fade loader" id="modal-nit-informacion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
        <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modal-title-usuario-accion">Cedula Nit</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">  
                        <div class="justify-content-center col-12 col-md-6 col-sm-6">
                            <div style="text-align: -webkit-center; height: 80px;">
                                <img id="avatar_nit" src="/img/theme/tim.png" class="img-fluid border border-2 border-white" style="width: 80px; height: 100%; cursor: pointer; border-radius: 50%;" alt="no-imagen">
                            </div>
                        </div>
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Nombre completo</label>
                            <input id="nombre_completo_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Documento</label>
                            <input id="numero_documento_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Direccion</label>
                            <input id="direccion_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Telefono</label>
                            <input id="telefono_1_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Correo</label>
                            <input id="email_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Ciudad</label>
                            <input id="ciudad_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Observaciones</label>
                            <input id="observaciones_nit" class="form-control form-control-sm" type="text" disabled>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger ml-auto" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- NOTIFICAIONES TOAST -->
    <div class="contenedor-toast" id="contenedor-toast"></div>
    <!-- LOADING GLOBAL WATER + TE -->
    <div class="water" style="display: none">
        <svg class="tea" width="37" height="48" viewbox="0 0 37 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M27.0819 17H3.02508C1.91076 17 1.01376 17.9059 1.0485 19.0197C1.15761 22.5177 1.49703 29.7374 2.5 34C4.07125 40.6778 7.18553 44.8868 8.44856 46.3845C8.79051 46.79 9.29799 47 9.82843 47H20.0218C20.639 47 21.2193 46.7159 21.5659 46.2052C22.6765 44.5687 25.2312 40.4282 27.5 34C28.9757 29.8188 29.084 22.4043 29.0441 18.9156C29.0319 17.8436 28.1539 17 27.0819 17Z" stroke="var(--secondary)" stroke-width="2"></path>
            <path d="M29 23.5C29 23.5 34.5 20.5 35.5 25.4999C36.0986 28.4926 34.2033 31.5383 32 32.8713C29.4555 34.4108 28 34 28 34" stroke="var(--secondary)" stroke-width="2"></path>
            <path id="teabag" fill="var(--secondary)" fill-rule="evenodd" clip-rule="evenodd" d="M16 25V17H14V25H12C10.3431 25 9 26.3431 9 28V34C9 35.6569 10.3431 37 12 37H18C19.6569 37 21 35.6569 21 34V28C21 26.3431 19.6569 25 18 25H16ZM11 28C11 27.4477 11.4477 27 12 27H18C18.5523 27 19 27.4477 19 28V34C19 34.5523 18.5523 35 18 35H12C11.4477 35 11 34.5523 11 34V28Z"></path>
            <path id="steamL" d="M17 1C17 1 17 4.5 14 6.5C11 8.5 11 12 11 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="var(--secondary)"></path>
            <path id="steamR" d="M21 6C21 6 21 8.22727 19 9.5C17 10.7727 17 13 17 13" stroke="var(--secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </div>

    <!-- FOOTER -->
    @include('layouts.footers.footer')

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- <script src="assets/js/plugins/perfect-scrollbar.min.js"></script> -->
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
       
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="assets/js/argon-dashboard.js"></script>
    <!-- JQUERY --> 
    <script src="assets/js/sistema/jquery-3.5.1.js"></script>
    <!-- DATATABLE -->
    <script src="assets/js/sistema/jquery.dataTables.min.js"></script>
    <script src="assets/js/sistema/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sistema/dataTables.responsive.min.js"></script>
    <script src="assets/js/sistema/responsive.bootstrap5.min.js"></script>
    <script src="assets/js/sistema/dataTables.fixedHeader.min.js"></script>
    <script src="assets/js/sistema/dataTables.fixedColumns.min.js"></script>
    
    <!-- SELECT 2  -->
    <script src="assets/js/sistema/select2.full.min.js"></script>
    <!-- VALIDATE -->
    <script src="assets/js/sistema/jquery.validate.min.js"></script>
    <!-- sweetalert2 -->
    <script src="assets/js/sistema/sweetalert2.all.min.js"></script>
    <!-- countUp -->
    <script src="https://cdn.jsdelivr.net/npm/countup@1.8.2/dist/countUp.min.js"></script>
    <!-- PUSHER -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <!-- SISTEMA -->
    <script src="assets/js/sistema/sistema.js" rel="stylesheet"></script>
    <!-- <script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js" rel="stylesheet"></script> -->
    
    @stack('js')
</body>

</html>
