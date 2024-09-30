<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_blanco.png">
    <title>
        Maximo PH
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ secure_asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ secure_asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="{{ secure_asset('assets/js/sistema/42d5adcbca.js') }}" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ secure_asset('assets/css/argon-dashboard.css') }}" rel="stylesheet" />
    <!-- DATATABLE -->
    <link href="{{ secure_asset('assets/css/sistema/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ secure_asset('assets/css/sistema/responsive.bootstrap5.min.css') }}" rel="stylesheet" />
    <!-- SELECT 2 -->
    <link href="{{ secure_asset('assets/css/sistema/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ secure_asset('assets/css/sistema/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
    <style>
        .select2-selection{
            font-size: 13px !important;
        }
        .select2-selection--single{
            font-size: 13px !important;
        }
        .select2-results__option{
            font-size: 13px !important;
        }
        .accordion-button {
            padding: 7px !important;
        }
        .nav-link {
            font-size: 13px !important;
            padding: 5px !important;
        }
        .card .card-body{
            padding: 0.7rem;
        }
        table.dataTable td {
            color: black;
        }

        .search-table {
            margin-bottom: 0.8rem !important;
            width: 100% !important;
            max-width: 400px;
            padding-right: 0px;
            float: right !important;
        }

        label, .form-label {
            margin-bottom: 2px !important;
        }

        .fondo-sistema {
            background-image: url(/img/fondo-erp.png);
            height: 100% !important;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .btn {
            margin-bottom: 0.8rem !important;
        }

        .form-group {
            margin-bottom: 0.8rem !important;
        }

        .button-user {
            cursor: pointer;
        }

        .navbar-nav > .nav-item > .nav-link.active {
            background-image: linear-gradient(310deg, #344767 0%, #344767 100%) !important;
            color: white !important;
        }

        .navbar-nav > .nav-item > .nav-link.active > .icon > .text-dark {
            color: white !important;
        }

        .icon-user {
            font-size: 15px;
            padding: 5px;
            -webkit-animation: color_change 2s infinite alternate;
            -moz-animation: color_change 2s infinite alternate;
            -ms-animation: color_change 2s infinite alternate;
            -o-animation: color_change 2s infinite alternate;
            animation: color_change 2s infinite alternate;
        }

        .icon-user-none {
            font-size: 15px;
            padding: 5px;
        }

        .form-check-input:checked[type=radio] {
            background-image: linear-gradient(310deg, #344767 0%, #344767 100%);
        }

        .form-check:not(.form-switch) .form-check-input[type=radio]:checked {
            padding: 5px;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--highlighted {
            color: #fff;
            background-color: #596cff;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--disabled, .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option[aria-disabled=true] {
            color: #6c757d;
            background-color: #e9ecef;
        }

        @-webkit-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-moz-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-ms-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-o-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        .dtfh-floatingparent {
            top: 0px !important;
            /* left: 29px !important; */
        }
        thead tr:first-child th {
            background-color: #1c4587;
            color: white;
            font-weight: bold;
            font-size: 14px;
            z-index: 12;
            top: -52;
        }

        .footer-navigation {
            position: fixed;
            left: 0;
            bottom: -1px;
            width: 100%;
            z-index: 999;
            text-align: center;
        }

        .footer-navigation .nav {
            justify-content: center;
        }

        .close_item_navigation {
            color: red;
            cursor: pointer;
        }

        .footer-navigation .nav-item .nav-link {
            margin-bottom: -1px;
            background: none;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            margin-left: 1px;
            color: white;
            background-color: #2a3548;
            cursor: pointer;
        }

        .footer-navigation .nav-item .nav-link.active {
            background-color: #FFF !important;
            color: black;
            cursor: context-menu;
        }

        .button-side-nav {
            cursor: pointer;
            color: #FFF !important;
        }

        #navbar {
            display: flex;
            flex-direction: row;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .navbar > .container, .navbar > .container-fluid, .navbar > .container-sm, .navbar > .container-md, .navbar > .container-lg, .navbar > .container-xl, .navbar > .container-xxl {
            display: flex;
            flex-wrap: none !important;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-collapse {
            flex-basis: 0%;
            flex-grow: 1;
            align-items: center;
        }

        .collapse {
            display: none;
        }

        .collapse.show {
            display: block !important;
        }

        .collapse .navbar-collapse {
            display: block !important;
        }

        tr.odd:hover {
            background-color: #1c45872b;
        }

        tr.even:hover {
            background-color: #1c45872b;
        }

        tr.odd:focus {
            background-color: #1c45872b;
        }

        tr.even:focus {
            background-color: #1c45872b;
        }

        td.dtfc-fixed-right {
            background-color: white;
            border-left: solid 1px #e9ecef !important;
        }

        th.dtfc-fixed-right {
            right: 0px !important;
            border-left: solid 1px #e9ecef !important;
        }

        .dark-version td.dtfc-fixed-right {
            background-color: #111c44 !important;
        }

        .btn {
            box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%);
        }

        .btn:hover {
            box-shadow: 0px 7px 14px rgba(50, 50, 93, 0.1), 4px 3px 6px rgb(0 0 0 / 80%) !important;
        }

        .btn-close {
            color: black;
            place-self: baseline;
        }

        .navbar-vertical .navbar-nav .nav-item .nav-link[data-bs-toggle=collapse]:after {
            color: #fff;
        }

        /* Toast */
        .contenedor-toast {
            position: fixed;
            right: 20px;
            bottom: 40px;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column-reverse;
            gap: 20px;
            z-index: 9999 !important;
        }

        .toast {
            background: #ccc;
            display: flex;
            justify-content: space-between;
            border-radius: 10px;
            overflow: hidden;
            animation-name: apertura;
            animation-duration: 200ms;
            animation-timing-function: ease-out;
            position: relative;
            width: 100% !important;
        }

        .toast.exito {
            background: var(--bs-success);
            color: white !important;
        }
        .toast.error {
            background: var(--bs-danger);
            color: white !important;
        }
        .toast.info {
            background: var(--bs-info);
            color: white !important;
        }
        .toast.warning {
            background: var(--bs-warning);
            color: white !important;
        }

        .toast .contenido {
            display: grid;
            grid-template-columns: 30px auto;
            align-items: center;
            gap: 15px;
            padding: 15px;
            z-index: 9;
        }

        .toast .icono {
            color: rgba(0, 0, 0, 0.4);
        }

        .toast .titulo {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .toast .descripcion {
            font-size: 14px;
        }

        .toast .texto {
            transform: translateY(10%);
        }

        .toast .btn-cerrar {
            background: rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            padding: 0px 5px;
            transition: 0.3s ease all;
        }

        .toast .btn-cerrar:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .toast .btn-cerrar .icono {
            width: 20px;
            height: 20px;
            color: #fff;
        }

        @keyframes apertura {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toast.cerrando {
            animation-name: cierre;
            animation-duration: 200ms;
            animation-timing-function: ease-out;
            animation-fill-mode: forwards;
        }

        @keyframes cierre {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(calc(100% + 40px));
            }
        }

        .toast.autoCierre::after {
            content: '';
            width: 100%;
            height: 4px;
            background: rgba(0, 0, 0, 0.5);
            position: absolute;
            bottom: 0;
            animation-name: autoCierre;
            animation-duration: 5s;
            animation-timing-function: ease-out;
            animation-fill-mode: forwards;
        }

        @keyframes autoCierre {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }

        svg.tea {
            --secondary: #33406f;
            position: absolute;
            top: 40%;
            left: 50%;
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        svg.tea #teabag {
            transform-origin: top center;
            transform: rotate(3deg);
            animation: swing 2s infinite;
        }
        svg.tea #steamL {
            stroke-dasharray: 13;
            stroke-dashoffset: 13;
            animation: steamLarge 2s infinite;
        }
        svg.tea #steamR {
            stroke-dasharray: 9;
            stroke-dashoffset: 9;
            animation: steamSmall 2s infinite;
        }
        @-moz-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-webkit-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-o-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-moz-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-webkit-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-o-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-moz-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @-webkit-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @-o-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }

        .water{
            width:100px;
            height: 100px;
            background-color: skyblue;
            border-radius: 50%;
            position: fixed;
            z-index: 99999;
            box-shadow: inset 0 0 30px 0 rgba(0,0,0,.5), 0 4px 10px 0 rgba(0,0,0,.5);
            overflow: hidden;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .water:before, .water:after{
            content:'';
            position: absolute;
            width:100px;
            height: 100px;
            top: -30px;
            background-color: #fff;
        }
        .water:before{
            border-radius: 45%;
            background:rgba(255,255,255,.7);
            animation:wave 5s linear infinite;
        }
        .water:after{
            border-radius: 35%;
            background:rgba(255,255,255,.3);
            animation:wave 5s linear infinite;
        }
        @keyframes wave{
            0%{
                transform: rotate(0);
            }
            100%{
                transform: rotate(360deg);
            }
        }

    </style>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NPDX42D8');</script>
    <!-- End Google Tag Manager -->

    <script src="https://www.google.com/recaptcha/enterprise.js?render=6Lfmb0MqAAAAAHqhT6_aktU9V6ycmpn5FMG9zfQ_"></script>
</head>

<body class="{{ $class ?? '' }} ">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NPDX42D8"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    @guest
        @yield('content')
    @endguest

    <!--   Core JS Files   -->
    <script src="{{ secure_asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ secure_asset('assets/js/core/bootstrap.min.js') }}"></script>
    <!-- <script src="assets/js/plugins/perfect-scrollbar.min.js"></script> -->
    <script src="{{ secure_asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js') }}"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ secure_asset('assets/js/argon-dashboard.js') }}"></script>
    <!-- JQUERY -->
    <script src="{{ secure_asset('assets/js/sistema/jquery-3.5.1.js') }}"></script>
    <!-- DATATABLE -->
    <script src="{{ secure_asset('assets/js/sistema/jquery.dataTables.min.js') }}"></script>
    <script src="{{ secure_asset('assets/js/sistema/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ secure_asset('assets/js/sistema/dataTables.responsive.min.js') }}"></script>
    <script src="{{ secure_asset('assets/js/sistema/responsive.bootstrap5.min.js') }}"></script>
    <!-- SELECT 2  -->
    <script src="{{ secure_asset('assets/js/sistema/select2.full.min.js') }}"></script>
    <!-- VALIDATE -->
    <script src="{{ secure_asset('assets/js/sistema/jquery.validate.min.js') }}"></script>
    <!-- sweetalert2 -->
    <script src="{{ secure_asset('assets/js/sistema/sweetalert2.all.min.js') }}"></script>

    <script type="module">
        //LOCAL
        // const base_url = 'http://127.0.0.1:8090/api/';
        // const base_web = 'http://127.0.0.1:8090/';
        
        //LOCAL PUBLIC
        // const base_url = 'http://192.168.1.6:80/api/';
        // const base_web = 'http://192.168.1.6:80/';
        //DEV
        const base_url = 'https://maximoph.com/api/';
        const base_web = 'https://maximoph.com/';
        //PRO
        // const base_url = 'https://app.portafolioerp.com/api/';
        // const base_web = 'https://app.portafolioerp.com/';

        const buttonResend = document.getElementById('button-resend-disabled');
        const tokenRecaptcha = '6Lfmb0MqAAAAAHqhT6_aktU9V6ycmpn5FMG9zfQ_';

        var estadoCambioPass = true;
        let timeLeft = 20;

        $("#button-login").click(function(event){
            sendDataLogin();
        });

        $("#button-welcome").click(function(event){
            var contraNueva = $("#welcome_password_login").val();
            var contraAgain = $("#welcome_password_retry_login").val();

            localStorage.setItem("token_db_portafolio", '');
            localStorage.setItem("auth_token", '');
            localStorage.setItem("auth_token_erp", '');
            localStorage.setItem("empresa_nombre", '');
            localStorage.setItem("notificacion_code", '');
            localStorage.setItem("notificacion_code_general", '');
            localStorage.setItem("fondo_sistema", '');
            localStorage.setItem("empresa_logo", '');

            if (contraNueva != contraAgain) {
                $('#error-welcome').text("Las contraseñas no coinciden!");
                $('#error-welcome').show();
                return;
            } else {
                $('#error-welcome').hide();
            }

            $("#succes-welcome").hide();
            $("#button-welcome").hide();
            $("#button-welcome-loading").show();

            $.ajax({
                url: base_url + 'confirm-pass',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                data: {
                    "password": $('#welcome_password_login').val(),
                    "codigo": $('#welcome_codigo').val(),
                    "id_usuario": $('#welcome_id_usuario').val(),
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-welcome").show();
                $("#button-welcome-loading").hide();

                window.location.href = '/login';

            }).fail((err) => {
                $("#button-welcome").show();
                $("#button-welcome-loading").hide();
            });
        });

        $("#link-recover").click(function(event){
            $("#texto-login").hide();
            $("#texto-recover").show();

            $("#link-recover").hide();
            $("#link-login").show();
            
            $("#button-login").hide();
            $("#button-recover").show();

            $("#input-password").hide();

            $('#error-recover').hide();
            $('#error-login').hide();
            
        });

        $("#link-login").click(function(event){
            linkLogin();
        });

        function linkLogin() {
            estadoCambioPass = false;
            $("#texto-login").show();
            $("#texto-recover").hide();

            $("#link-recover").show();
            $("#link-login").hide();
            
            $("#button-login").show();
            $("#button-recover").hide();

            $("#input-password").show();

            $('#error-recover').hide();
            $('#error-login').hide();

            $("#input-new-password").hide();
            $("#input-retry-password").hide();

            $("#cambiar-password").hide();
            $("#button-resend").hide();
            $("#input_code_login").hide();
            $("#input_email_login").show();
            $("#button-confir-code").hide();
            $("#button-resend-disabled").hide();
            $("#button-recover").hide();
            $("#button-recover-loading").hide();
        }

        $("#button-recover").click(function(event){
            recuperarContra();
        });

        $("#button-confir-code").click(function(event){
            $("#button-resend").hide();
            $("#button-confir-code").hide();
            $("#button-login-loading").show();
            $("#button-resend-disabled").hide();
            
            $.ajax({
                url: base_url + 'validate-code',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                data: {
                    "email": $('#email_login').val(),
                    "code_general": $('#code_login').val()
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-recover-loading").hide();
                if(res.success){
                    estadoCambioPass = false;
                    $('#error-recover').hide();
                    $("#input-new-password").show();
                    $("#input-retry-password").show();
                    $("#button-resend").hide();
                    $("#input_code_login").hide();
                    $("#button-confir-code").hide();
                    $("#button-login-loading").hide();
                    $("#cambiar-password").show();
                } else {
                    $('#error-recover').text(res.message);
                    $('#error-recover').show();
                    $("#button-recover").hide();
                    $("#button-confir-code").show();
                    $("#button-login-loading").hide();
                    $("#button-login-loading").hide();
                }
            }).fail((err) => {
                err = err.responseJSON
                $('#error-recover').text(err.message);
                $('#error-recover').show();
                $("#button-recover").hide();
                $("#button-confir-code").show();
                $("#button-login-loading").hide();
                $("#button-login-loading").hide();
                if (timeLeft) {
                    $("#button-resend").hide();
                    $("#button-resend-disabled").show();
                } else {
                    $("#button-resend").show();
                    $("#button-resend-disabled").hide();
                }
            });
        });

        $("#cambiar-password").click(function(event){

            var contraNueva = $("#new_password_login").val();
            var contraAgain = $("#new_password_retry_login").val();

            if (contraNueva != contraAgain) {
                $('#error-recover').text("Las contraseñas no coinciden!");
                $('#error-recover').show();
                return;
            }

            $("#cambiar-password").hide();
            $("#button-login-loading").show();

            $.ajax({
                url: base_url + 'change-password',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                data: {
                    "email": $('#email_login').val(),
                    "new_password": $('#new_password_login').val()
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-recover-loading").hide();
                if(res.success){
                    linkLogin();
                    $("#success-recover").show();
                    $("#button-login-loading").hide();
                } else {
                    $('#error-recover').text(res.message);
                    $('#error-recover').show();
                    $("#cambiar-password").show();
                    $("#button-login-loading").hide();
                }
            }).fail((err) => {
                err = err.responseJSON
                $('#error-recover').text(err.message);
                $('#error-recover').show();
                $("#cambiar-password").show();
                $("#button-login-loading").hide();
            });
        });
        

        function recuperarContra() {
            $("#error-recover").hide();
            $("#button-recover").hide();
            $("#button-recover-loading").show();
            console.log('tokenRecaptcha: ',tokenRecaptcha);
            grecaptcha.enterprise.ready(async () => {
                grecaptcha.enterprise.execute(tokenRecaptcha, {action: 'validateEmail'}).then(function(token) {
                    $.ajax({
                        url: base_url + 'validate-email',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: 'POST',
                        data: {
                            "email": $('#email_login').val(),
                            "g-recaptcha-response": token,
                        },
                        dataType: 'json',
                    }).done((res) => {
                        $("#button-recover-loading").hide();
                        $("#button-recover").show();
                        if(res.success){
                            $('#error-recover').hide();
                            $("#input_code_login").show();
                            $("#input_email_login").hide();
                            $("#button-confir-code").show();
                            $("#button-resend-disabled").show();
                            $("#button-recover").hide();
                            $("#button-recover-loading").hide();

                            const countdown = setInterval(() => {
                            timeLeft--;
                            buttonResend.textContent = `Volver a enviar email (${timeLeft})`;

                            // Habilitar el botón cuando el tiempo llegue a 0
                            if (timeLeft <= 0) {
                                clearInterval(countdown);
                                if (estadoCambioPass) {
                                    buttonResend.textContent = 'Volver a enviar email (60)';
                                    buttonResend.disabled = false;
                                    $("#button-resend").show();
                                    $("#button-resend-disabled").hide();
                                }
                            }
                            }, 1000); // Actualizar cada segun
                        } else {
                            $('#error-recover').text(res.message);
                            $('#error-recover').show();
                        }
                    }).fail((err) => {
                        err = err.responseJSON

                        if (err.message == 'CSRF token mismatch.') {
                            window.location.href = '/login';
                            return;
                        }
                        var mensaje = err.message;
                        var errorsMsg = '';                        

                        if (typeof mensaje === 'object') {
                            Object.keys(mensaje).forEach(function(k){
                                errorsMsg +=k + ': ' + mensaje[k]+" <br>";
                            });
                        }
                        
                        else if (typeof mensaje === 'string') {
                            errorsMsg = mensaje;
                        }

                        $('#error-recover').html(errorsMsg);
                        $("#button-recover-loading").hide();
                        $("#button-recover").show();
                        $('#error-recover').show();
                    });       
                });
            });            
        }

        function changePassWord(event) {
            if(event.keyCode == 13) {
                sendDataLogin();
            }
        }

        $(document).on('keypress', '#email_login', function (event) {
            if (event.keyCode == 13) {
                setTimeout(function(){
                    $('#password_login').focus();
                    $('#password_login').select();
                },10);
            }
        });

        $(document).on('keypress', '#password_login', function (event) {
            if (event.keyCode == 13) {
                sendDataLogin();
            }
        });
        
        function sendDataLogin() {

            $('#error-login').hide();
            $("#success-recover").hide();
            $("#button-login-loading").show();
            $("#button-login").hide();

            grecaptcha.enterprise.ready(async () => {
                grecaptcha.enterprise.execute(tokenRecaptcha, {action: 'login'}).then(function(token) {
                    $.ajax({
                        url: base_web + 'login',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: 'POST',
                        data: {
                            "email": $('#email_login').val(),
                            "password": $('#password_login').val(),
                            "ip": localStorage.getItem("ip_geo"),
                            "g-recaptcha-response": token,
                        },
                        dataType: 'json',
                    }).done((res) => {
                        $("#button-login-loading").hide();
                        $("#button-login").show();
                        if(res.success){
                            localStorage.setItem("token_db_portafolio", res.token_db_portafolio);
                            localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
                            localStorage.setItem("auth_token_erp", res.token_api_portafolio);
                            localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                            localStorage.setItem("notificacion_code", res.notificacion_code);
                            localStorage.setItem("notificacion_code_general", res.notificacion_code_general);
                            localStorage.setItem("fondo_sistema", res.fondo_sistema);
                            localStorage.setItem("empresa_logo", res.empresa.logo);                    
        
                            var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                            if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
                            } else {
                                localStorage.setItem("item_active_menu", 'contabilidad');
                            }
        
                            window.location.href = '/home';
                        } else {
                            $('#error-login').show();
                        }
                    }).fail((err) => {
                        err = err.responseJSON;

                        if (err.message == 'CSRF token mismatch.') {
                            window.location.href = '/login';

                            return;
                        }

                        $("#button-login-loading").hide();
                        $("#button-login").show();
                        $('#error-login').show();
                        $('#error-login').text(err.message);
                    });            
                });
                
            });

        }
    </script>

     
</body>

</html>
