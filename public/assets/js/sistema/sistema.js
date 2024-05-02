
//LOCAL
// const base_url = 'http://127.0.0.1:8090/api/';
// const base_web = 'http://127.0.0.1:8090/';
// const base_web_erp = 'http://localhost:8000/';
// const base_url_erp = 'http://localhost:8000/api/';
//DEV
const base_url = 'https://maximoph.com/api/';
const base_web = 'https://maximoph.com/';
const base_web_erp = 'https://test.portafolioerp.com/';
const base_url_erp = 'https://test.portafolioerp.com/api/';

//PRO
// const base_url = 'https://app.portafolioerp.com/api/';
// const base_web = 'https://app.portafolioerp.com/';

const pusher = new Pusher('9ea234cc370d308638af', {cluster: 'us2'});
// Pusher.logToConsole = true;
const bucketUrl = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/';
const btnLogout = document.getElementById('sessionLogout');
const itemMenuActive = localStorage.getItem("item_active_menu");

let dateNow = new Date();
let dropDownNotificacionOpen = false;
const auth_token = localStorage.getItem("auth_token");
const auth_token_erp = localStorage.getItem("auth_token_erp");
const iconNavbarSidenavMaximo = document.getElementById('iconNavbarSidenavMaximo');
var menuOpen = false;

$.ajaxSetup({
    'headers':{
        "Authorization": auth_token,
        "Content-Type": "application/json"
    }
});
const headers = {
    "Authorization": auth_token,
    "Content-Type": "application/json",
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
};

const headersERP = {
    "Authorization": auth_token_erp,
    "Content-Type": "application/json",
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
};

const medidasSwiper = [
    'translate3d(calc(13% - 480px), 0px, -200px) rotateZ(4deg) scale(1);',
    'translate3d(calc(17.25% - 720px), 0px, -300px) rotateZ(6deg) scale(1);',
    'translate3d(calc(20% - 960px), 0px, -400px) rotateZ(8deg) scale(1);',
    'translate3d(calc(20% - 1200px), 0px, -400px) rotateZ(8deg) scale(1);',
    'translate3d(calc(20% - 1440px), 0px, -400px) rotateZ(8deg) scale(1);',
    'translate3d(calc(20% - 1680px), 0px, -400px) rotateZ(8deg) scale(1);',
    'translate3d(calc(20% - 1920px), 0px, -400px) rotateZ(8deg) scale(1);',
];

function dateDifferenceInDays (dateInitial, dateFinal) {
    var dias_diferencia = (dateFinal - dateInitial) / 86_400_000;
    if (dias_diferencia < 0) {
        return dias_diferencia * -1
    }
    return dias_diferencia
}

let body = document.getElementsByTagName('body')[0];
let className = 'g-sidenav-pinned';
let sidenav = document.getElementById('sidenav-main');
let sidenav2 = document.getElementById('sidenav-main-2');
let buttonMostrarLateral = document.getElementById('button-mostrar-lateral');
let buttonocultarLateral = document.getElementById('button-ocultar-lateral');
let iconSidenav = document.getElementById('iconSidenav');

var moduloCreado = {
    'entorno': false,
    'nit': false,
    'inmueble': false,
    'conceptofacturacion': false,
    'zona': false,
    'facturacion': false,
    'recibo': false,
    'cuotasmultas': false,
    'estadocuenta': false,
    'usuarios': false,
    'pagotransferencia': false,
    'porteria': false,
    'instalacionempresa': false,
    'importrecibos': false,
    'pqrsf': false,
};

var moduloRoute = {
    'entorno': 'configuracion',
    'nit': 'tablas',
    'inmueble': 'tablas',
    'conceptofacturacion': 'tablas',
    'zona': 'tablas',
    'facturacion': 'operaciones',
    'recibo': 'operaciones',
    'cuotasmultas': 'operaciones',
    'estadocuenta': 'administrativo',
    'usuarios': 'configuracion',
    'pagotransferencia': 'operaciones',
    'porteria': 'administrativo',
    'instalacionempresa': 'administrativo',
    'importrecibos': 'importador',
    'pqrsf': 'administrativo',
}

$('.water').show();
$('#containner-dashboard').load('/dashboard', function() {
    $('.water').hide();
});
$("#titulo-view").text('Inicio');

$(document).ajaxError(function myErrorHandler(event, xhr, ajaxOptions, thrownError) {
    // console.log('xhr: ',xhr);
    if(xhr.status == 401) {
        // document.getElementById('logout-form').submit();
    }
});

$("#nombre-empresa").text(localStorage.getItem("empresa_nombre"));
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));
setTimeout(function(){
    $(".fondo-sistema").css('background-image', 'url(' +bucketUrl + localStorage.getItem("fondo_sistema")+ ')');
},200);

if (localStorage.getItem("empresa_logo") == 'null') {
    $("#side_main_logo").attr('src', '/img/logo_blanco.png');
} else{ 
    $("#side_main_logo").attr('src', bucketUrl+localStorage.getItem("empresa_logo"));
}

if (iconNavbarSidenavMaximo) {
    iconNavbarSidenavMaximo.addEventListener("click", toggleSidenavMaximo);
}

if (iconSidenav) {
    iconSidenav.addEventListener("click", toggleSidenavMaximoClose);
}

if (sidenav2) {
    sidenav2.addEventListener("click", toggleSidenavMaximo);
}
//PORTERO
if (idRolUsuario == 4) {
    openNewItem('porteria', 'Porteria', 'fas fa-user-shield');
    closeMenu();
}

iniciarScrollBar();
buscarNotificaciones();

function iniciarScrollBar() {
    var dropDownNotificacion = document.querySelector('#dropdown-notificaciones');
    var offcanvasBodyPqrsf = document.querySelector('#offcanvas-body-pqrsf');

    new PerfectScrollbar(dropDownNotificacion);
    new PerfectScrollbar(offcanvasBodyPqrsf);
}

function setNotificaciones(total = 0) {
    var numeroNotificaciones = total ? total : parseInt(localStorage.getItem("numero_notificaciones"));
    if (numeroNotificaciones) {
        $("#number_notification").text(numeroNotificaciones);
        $("#number_notification").show();
        $("#bell_notification").addClass('animate__animated animate__infinite animate__slower animate__tada');
    } else {
        $("#number_notification").text('');
        $("#number_notification").hide();
        $("#bell_notification").remove('animate__animated animate__infinite animate__slower animate__tada');
    }
}

var channelPqrsf = pusher.subscribe('pqrsf-mensaje-'+localStorage.getItem("notificacion_code"));

channelPqrsf.bind('notificaciones', function(data) {
    var idPqrsfOpen = $("#id_pqrsf_up").val();
    console.log(data.id_pqrsf, idPqrsfOpen);
    if (data.id_pqrsf == idPqrsfOpen) {

        mostrarMensajesPqrsf(data.data);
        document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
        initSwipers();
        leerNotificaciones(data.id_notificacion);
    } else {
        buscarNotificaciones();
    }
});

function keyPressPqrsfMensaje(event) {
    if (event.keyCode == 13) {
        document.getElementById('button-send-pqrsf').click();
    }
}

function buscarNotificaciones() {
    $.ajax({
        url: base_url + 'notificaciones',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            localStorage.setItem("numero_notificaciones", res.total);
            setNotificaciones(res.total);
        }
    }).fail((res) => {
        agregarToast('error', 'Error al consultar notificaciones', res.message);
    });
}

function leerNotificaciones(id) {
    $.ajax({
        url: base_url + 'notificaciones',
        method: 'PUT',
        headers: headers,
        data: JSON.stringify({id: id, estado: 1}),
        dataType: 'json',
    }).done((res) => {
    }).fail((res) => {
    });
}

function openNewItem(id, nombre, icon) {
    if($('#containner-'+id).length == 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre);
    document.getElementById('sidenav-main-2').click();
}

function closeMenu() {
    if (sidenav.classList.contains('side-nav-maximo-open')) {
        toggleSidenavMaximo();
    }
}

function generateView(id, nombre, icon){
    $('.water').show();
    $('#contenerdores-views').append('<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>');
    $('#footer-navigation').append(generateNewTabButton(id, nombre, icon));
    $('#containner-'+id).load('/'+id, function() {

        if(!moduloCreado[id]) includeJs(id);
        else callInitFuntion(id);

        $('.water').hide();
    });
}

function callInitFuntion(id) {
    var functionInit = id+'Init';
    window[functionInit]();
}

function includeJs(id){
    let scriptEle = document.createElement("script");

    let urlFile = "assets/js/sistema/"+moduloRoute[id]+"/"+id+"-controller.js";
    scriptEle.setAttribute("src", urlFile);
    scriptEle.onload = function () {
        callInitFuntion(id);
    };
    document.body.appendChild(scriptEle);
    moduloCreado[id] = true;
}

function seleccionarView(id, nombre = 'Inicio'){

    $(".dtfh-floatingparent").remove();
    $('.change-view').removeClass("active");
    $('.seleccionar-view').removeClass("active");
    $('.button-side-nav').removeClass("active");
    $('#containner-'+id).addClass("active");
    $('#tab-'+id).addClass("active");
    $('#sidenav_'+id).addClass("active");
    
    $("#titulo-view").text(nombre);
}

function generateNewTabView(id){
    var html = '<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>';
}

function generateNewTabButton(id, nombre, icon){
    
    var html = `
        <li class="nav-item" id="lista_view_${id}">
            <div class="nav-link col seleccionar-view" onclick="seleccionarView('${id}', '${nombre}')" id="tab-${id}">
                <i class="${icon}"></i>&nbsp;
                ${nombre}&nbsp;&nbsp;
                <i class="fas fa-times-circle close_item_navigation" id="closetab_${id}" onclick="closeView(this)"></i>&nbsp;
            </div>
        </li>
    `;
    return html;
}

function closeView(nameView) {
    var id = nameView.id.split('_')[1];
    
    $("#lista_view_"+id).remove();
    $("#containner-"+id).empty();
    $("#containner-"+id).remove();

    setTimeout(() => {
        seleccionarView('dashboard');
    }, 10)
}

$("#tab-dashboard").click(function(event){
    seleccionarView('dashboard');
});

function toggleSidenavMaximo() {
    if (menuOpen) {
        body.classList.remove(className);
        menuOpen = false;
        sidenav.classList.remove('bg-transparent');
        sidenav.classList.add('side-nav-maximo-close');
        sidenav.classList.remove('side-nav-maximo-open');
        buttonMostrarLateral.classList.remove('ocultar');
        buttonocultarLateral.classList.add('ocultar');
        setTimeout(function() {
            sidenav.classList.remove('bg-white');
        }, 100);
    } else {
        body.classList.add(className);
        menuOpen = true;
        sidenav.classList.add('bg-white');
        sidenav.classList.remove('bg-transparent');
        iconSidenav.classList.remove('d-none');
        sidenav.classList.add('side-nav-maximo-open');
        sidenav.classList.remove('side-nav-maximo-close');
        buttonMostrarLateral.classList.add('ocultar');
        buttonocultarLateral.classList.remove('ocultar');
    }
}

function toggleSidenavMaximoOpen() {
    body.classList.add(className);
    sidenav.classList.add('bg-white');
    sidenav.classList.remove('bg-transparent');
    iconSidenav.classList.remove('d-none');
    sidenav.classList.add('side-nav-maximo-open');
    sidenav.classList.remove('side-nav-maximo-close');
}

function toggleSidenavMaximoClose() {
    body.classList.remove(className);
    setTimeout(function() {
    sidenav.classList.remove('bg-white');
    }, 100);
    sidenav.classList.remove('bg-transparent');
    sidenav.classList.add('side-nav-maximo-close');
    sidenav.classList.remove('side-nav-maximo-open');
}

//PERSONAL TABLE LENGUAJE
const lenguajeDatatable = {
    "sProcessing":     "",
    "sLengthMenu":     "Mostrar _MENU_ registros",
    "sZeroRecords":    "No se encontraron resultados",
    "sEmptyTable":     "Ningún registro disponible",
    "sInfo":           "Registros del _START_ al _END_ de un total de _TOTAL_ ",
    "sInfoEmpty":      "Registros del 0 al 0 de un total de 0 ",
    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix":    "",
    "sSearch":         "Buscar:",
    "sUrl":            "",
    "sInfoThousands":  ",",
    "oPaginate": {
        "sFirst":    "Primero",
        "sLast":     "Último",
        "sNext":     ">",
        "sPrevious": "<"
    },
    "oAria": {
        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
}

// $("#button-login").click(function(event){
    
//     $("#button-login-loading").show();
//     $("#button-login").hide();

//     $.ajax({
//         url: base_web + 'login',
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         method: 'POST',
//         data: {
//             "email": $('#email_login').val(),
//             "password": $('#password_login').val(),
//             "_token": $('meta[name="csrf-token"]').attr('content'),
//         },
//         dataType: 'json',
//     }).done((res) => {
//         $("#button-login-loading").hide();
//         $("#button-login").show();
//         if(res.success){
//             localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
//             localStorage.setItem("empresa_nombre", res.empresa.razon_social);
//             localStorage.setItem("empresa_logo", res.empresa.logo);
//             window.location.href = '/home';
//         }
//     }).fail((err) => {
//         $("#button-login-loading").hide();
//         $("#button-login").show();
//     });
// });

$(".btn-cerrar").click(function(event){
    console.log('asdasd');
});

function swalFire(titulo, mensaje, estado = true){
    var status = estado ? 'success' : 'error';
    Swal.fire(
        titulo,
        mensaje,
        status
    )
}

function getRowById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if(element.id == idData){
            return index;
        }
    }
    return false;
}

function getDataById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if(element.id == idData){
            return element;
        }
    }
    return false;
}

function showUser (id_usuario, fecha, creado) {

    if(!id_usuario) {
        return;
    }

    $('#usuario_accion').val('');
    $('#correo_accion').val('');
    $('#fecha_accion').val('');
    $("#modal-title-usuario-accion").html("Buscando usuario ...");

    $('#modal-usuario-accion').modal('show');
    $('.water').show();
    $.ajax({
        url: base_url + 'usuario-accion',
        method: 'GET',
        data: {id: id_usuario},
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            var data = res.data;
            $('#usuario_accion').val(data.username);
            $('#correo_accion').val(data.email);
            $('#fecha_accion').val(fecha);

            if (creado) {
                $("#modal-title-usuario-accion").html("Creado por: "+ data.username);
            } else {
                $("#modal-title-usuario-accion").html("Actualizado por: "+ data.username);
            }
        }
        $('.water').hide();
    }).fail((err) => {
        swalFire('Error al cargar usuario', '', false);
        $('.water').hide();
    });
}

function showNit (id_nit) {

    if(!id_nit) {
        return;
    }

    $('#numero_documento').val('');
    $('#nombre_completo').val('');
    $('#direccion').val('');
    $('#telefono_1').val('');
    $('#email').val('');
    $('#observaciones').val('');
    $('#ciudad').val('');

    $('#modal-nit-informacion').modal('show');
    $('.water').show();

    $.ajax({
        url: base_url + 'nit/informacion',
        method: 'GET',
        data: {id_nit: id_nit},
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            var data = res.data;
            if(data.logo_nit) {
                $('#avatar_nit').attr('src', 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/'+data.logo_nit);
            } else {
                $('#avatar_nit').attr('src', '/img/theme/tim.png');
            }
            $('#numero_documento_nit').val(data.numero_documento);
            $('#nombre_completo_nit').val(data.nombre_nit);
            $('#direccion_nit').val(data.direccion);
            $('#telefono_1_nit').val(data.telefono_1);
            $('#email_nit').val(data.email);
            $('#observaciones_nit').val(data.observaciones);
            $('#ciudad_nit').val(data.ciudad ? data.ciudad.nombre_completo : '');
        }
        $('.water').hide();
    }).fail((err) => {
        swalFire('Error al cargar nit', '', false);
        $('.water').hide();
    });
}


// const contenedorBotones = document.getElementById('contenedor-botones');
const contenedorToast = document.getElementById('contenedor-toast');

// Event listener para detectar click en los botones
// contenedorBotones.addEventListener('click', (e) => {
//     e.preventDefault();

//     const tipo = e.target.dataset.tipo;

//     if (tipo === 'exito') {
//         agregarToast({ tipo: 'exito', titulo: 'Exito!', descripcion: 'La operación fue exitosa.', autoCierre: true });
//     }
//     if (tipo === 'error') {
//         agregarToast({ tipo: 'error', titulo: 'Error', descripcion: 'Hubo un error', autoCierre: true });
//     }
//     if (tipo === 'info') {
//         agregarToast({ tipo: 'info', titulo: 'Info', descripcion: 'Esta es una notificación de información.' });
//     }
//     if (tipo === 'warning') {
//         agregarToast({ tipo: 'warning', titulo: 'Warning', descripcion: 'Ten cuidado' });
//     }
// });

// Event listener para detectar click en los toasts
contenedorToast.addEventListener('click', (e) => {
    const toastId = e.target.closest('div.toast').id;

    if (e.target.closest('button.btn-cerrar')) {
        cerrarToast(toastId);
    }
});

// Función para cerrar el toast
function cerrarToast(id){
    document.getElementById(id)?.classList.add('cerrando');
}

// Función para agregar la clase de cerrando al toast.
function agregarToast (tipo, titulo, descripcion, autoCierre = false) {
    // Crear el nuevo toast
    const nuevoToast = document.createElement('div');

    // Agregar clases correspondientes
    nuevoToast.classList.add('toast');
    nuevoToast.classList.add(tipo);
    if (autoCierre) nuevoToast.classList.add('autoCierre');

    // Agregar id del toast
    const numeroAlAzar = Math.floor(Math.random() * 100);
    const fecha = Date.now();
    const toastId = fecha + numeroAlAzar;
    nuevoToast.id = toastId;

    // Iconos
    const iconos = {
        exito: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"
                    />
                </svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                    />
                </svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"
                    />
                </svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                    />
                </svg>`,
    };

    // Plantilla del toast
    var toast = `
        <div class="contenido">
            <div class="icono">
                ${iconos[tipo]}
            </div>
            <div class="texto">
                <p class="titulo">${titulo}</p>
                <p class="descripcion">${descripcion}</p>
            </div>
        </div>
        <button class="btn-cerrar"  onclick="cerrarToast('${toastId}')" href="javascript:void(0)">
            <div class="icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                    />
                </svg>
            </div>
        </button>
    `;

    // Agregar la plantilla al nuevo toast
    nuevoToast.innerHTML = toast;

    // Agregamos el nuevo toast al contenedor
    contenedorToast.appendChild(nuevoToast);

    // Función para menajera el cierre del toast
    const handleAnimacionCierre = (e) => {
        if (e.animationName === 'cierre') {
            nuevoToast.removeEventListener('animationend', handleAnimacionCierre);
            nuevoToast.remove();
        }
    };

    if (autoCierre) {
        setTimeout(() => cerrarToast(toastId), 3000);
    }

    // Agregamos event listener para detectar cuando termine la animación
    nuevoToast.addEventListener('animationend', handleAnimacionCierre);
};

function removejscssfile(filename, filetype){
    var targetelement=(filetype=="js")? "script" : (filetype=="css")? "link" : "none" //determine element type to create nodelist from
    var targetattr=(filetype=="js")? "src" : (filetype=="css")? "href" : "none" //determine corresponding attribute to test for
    var allsuspects=document.getElementsByTagName(targetelement)
    for (var i=allsuspects.length; i>=0; i--){ //search backwards within nodelist for matching elements to remove
        if (allsuspects[i] && allsuspects[i].getAttribute(targetattr)!=null && allsuspects[i].getAttribute(targetattr).indexOf(filename)!=-1)
            allsuspects[i].parentNode.removeChild(allsuspects[i]) //remove element by calling parentNode.removeChild()
    }
}

function loadExcel(data) {
    window.open('https://'+data.url_file, "_blank");
    agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
}

$(document).on('click', '#descargarPlantilla', function () {
    
});

btnLogout.addEventListener('click', event => {

    event.preventDefault();
    $.ajax({
        url: base_web + 'logout',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.location.href = '/';
    }).fail((err) => {
    });
});

function numberWithCommas(x) {
    x = x.toString();
    var pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

function selectMenu(menu) {
    
}

function showAllMenus() {
    var menu1 = document.getElementsByClassName('tipo_menu_1');
    if (menu1.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu1.length; index++) {
            const element = menu1[index];
            element.style.display = 'block';
        }
    }

    var menu2 = document.getElementsByClassName('tipo_menu_2');
    if (menu2.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu2.length; index++) {
            const element = menu2[index];
            element.style.display = 'block';
        }
    }

    var menu3 = document.getElementsByClassName('tipo_menu_3');
    if (menu3.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu3.length; index++) {
            const element = menu3[index];
            element.style.display = 'block';
        }
    }
}

function hideAllMenus() {
    var menu1 = document.getElementsByClassName('tipo_menu_1');
    if (menu1.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu1.length; index++) {
            const element = menu1[index];
            element.style.display = 'none';
        }
    }

    var menu2 = document.getElementsByClassName('tipo_menu_2');
    if (menu2.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu2.length; index++) {
            const element = menu2[index];
            element.style.display = 'none';
        }
    }

    var menu3 = document.getElementsByClassName('tipo_menu_3');
    if (menu3.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu3.length; index++) {
            const element = menu3[index];
            element.style.display = 'none';
        }
    }
}

function formatNumber(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
} 

function formatCurrencyValue (value) {
    if (value) {
        value = value + '';

        if (value.indexOf(".") >= 0) {    
            var decimal_pos = value.indexOf(".");
            
            var left_side = value.substring(0, decimal_pos);
            var right_side = value.substring(decimal_pos);
        
            left_side = formatNumber(left_side);
            right_side = formatNumber(parseFloat(right_side).toFixed(2).slice(1));
            right_side = right_side.substring(0, 2);
        
            valorFormato = left_side + "." + right_side;
        
            return valorFormato;
        } else {
            return formatNumber(value)+".00";
        }

    } else {
        return '0.00';
    }
}
 
function formatCurrency(input, blur) {
    // appends $ to value, validates decimal side
    // and puts cursor back in right position.
    
    // get input value
    var input_val = input.val();
    input_val = input_val.replace(',', '');
    // don't validate empty input
    if (input_val === "") { return; }
    
    // original length
    var original_len = input_val.length;
  
    // initial caret position 
    var caret_pos = input.prop("selectionStart");
      
    // check for decimal
    if (input_val.indexOf(".") >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(".");
        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        // add commas to left side of number
        left_side = formatNumber(left_side);
        // validate right side
        right_side = formatNumber(right_side);
        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
            right_side += "00";
        }
        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        input_val = left_side + "." + right_side;
    } else {
        input_val = formatNumber(input_val);
        if (blur === "blur") {
            input_val += ".00";
        }
        input_val = input_val;
    }
    
    // send updated string to input
    input.val(input_val);
    
    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}

function stringToNumberFloat (value) {
    value = value+'';
    if (value) value = parseFloat(parseFloat(value.replaceAll(',', '')).toFixed(2));
    return value ? value : 0;
}

const toCamelCase = str => {
    const s =
      str &&
      str
        .match(
          /[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g
        )
        .map(x => x.slice(0, 1).toUpperCase() + x.slice(1).toLowerCase())
        .join(' ');
    return s.slice(0, 1).toUpperCase() + s.slice(1);
};

function monthYear (date) {
    var fecha = date.split('-');

    switch (fecha[1]) {
        case '01':
            return 'Enero de '+ fecha[0];
            break;
        case '02':
            return 'Febrero de '+ fecha[0];
            break;
        case '03':
            return 'Marzo de '+ fecha[0];
            break;
        case '04':
            return 'Abril de '+ fecha[0];
            break;
        case '05':
            return 'Mayo de '+ fecha[0];
            break;
        case '06':
            return 'Junio de '+ fecha[0];
            break;
        case '07':
            return 'Julio de '+ fecha[0];
            break;
        case '08':
            return 'Agosto de '+ fecha[0];
            break;
        case '09':
            return 'Septiembre de '+ fecha[0];
            break;
        case '10':
            return 'Octubre de '+ fecha[0];
            break;
        case '11':
            return 'Noviembre de '+ fecha[0];
            break;
        case '12':
            return 'Dociembre de '+ fecha[0];
            break;
        default:
            break;
    }
};

function daysWeek (date) {
    var days = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    var d = new Date(date);
    var dayName = days[d.getDay()];
    return dayName;
}

function createMensajePqrsf() {

    var form = document.querySelector('#form-pqrsf-mensajes');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    var idMensaje = $("#id_pqrsf_up").val();

    $("#button-send-pqrsf").hide();
    $("#button-send-pqrsf-loading").show();

    var ajxForm = document.getElementById("form-pqrsf-mensajes");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "pqrsf-mensaje/"+idMensaje);
    xhr.send(data);
    xhr.onload = function(res) {

        var responseData = JSON.parse(res.currentTarget.response);
        
        $("#button-send-pqrsf").show();
        $("#button-send-pqrsf-loading").hide();

        if (responseData.success) {
            mostrarMensajesPqrsf(responseData.data);
            
            $("#mensaje_pqrsf_nuevo").val("");
            setTimeout(function(){
                $("#mensaje_pqrsf_nuevo").focus().select();
            }, 100);
            
            initSwipers();
            document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
        }
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $("#button-send-pqrsf").show();
        $("#button-send-pqrsf-loading").hide();
    };
}

function mostrarMensajesPqrsf(mensajes) {
    for (let index = 0; index < mensajes.length; index++) {
        var html = ``;
        var className = '';
        var mensaje = mensajes[index];
        var htmlImagen = '';

        if (mensaje.archivos) htmlImagen = htmlSwiperImg(mensaje.archivos);
        
        if (id_usuario_logeado == mensaje.created_by) {
            className = 'mensaje-estilo-derecha';
            html+=`${htmlImagen}<p style="font-size: 13px; margin-bottom: 0; font-weight: 600;">${mensaje.descripcion}</p>
                <p style="font-size: 10px; margin-bottom: 0; font-weight: 500; text-align: end;">${definirTiempo(mensaje.created_at)}</p>
                <i class="fas fa-caret-down icono-mensaje-derecha"></i>`;
        } else {
            className = 'mensaje-estilo-izquierda';
            html+=`${htmlImagen}<p style="font-size: 13px; margin-bottom: 0; text-align-last: right; font-weight: 600;">${mensaje.descripcion}</p>
                <p style="font-size: 10px; margin-bottom: 0; font-weight: 500;">${definirTiempo(mensaje.created_at)}</p>
                <i class="fas fa-caret-down icono-mensaje-izquierda"></i>`;
        }
        var mensajeDising = document.createElement('div');
        mensajeDising.setAttribute("class", className);
        mensajeDising.innerHTML = [
            html
        ].join('');
        document.getElementById('offcanvas-body-pqrsf').insertBefore(mensajeDising, null);
    }
}

function openDropDownNotificaciones(open = false) {

    $("#dropdown-notificaciones").empty();

    $("#dropdown-notificaciones").removeClass('dropdown-menu-top-1');
    $("#dropdown-notificaciones").removeClass('dropdown-menu-top-2');
    $("#dropdown-notificaciones").removeClass('dropdown-menu-top-3');

    if (!open) {
        if (dropDownNotificacionOpen) {
            $("#dropdown-notificaciones").removeClass('show');
            dropDownNotificacionOpen = false;
        } else {
            $("#dropdown-notificaciones").addClass('show');
            dropDownNotificacionOpen = true;
        }
    } else {
        $("#dropdown-notificaciones").removeClass('show');
        $("#dropdown-notificaciones").addClass('show');

    }

    showPlaceholderMenu();

    $.ajax({
        url: base_url + 'notificaciones',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        var data = res.data;
        if (data.length) {
            $("#dropdown-notificaciones").empty();
            $("#dropdown-notificaciones").removeClass('dropdown-menu-top-2');
            pintarNotificaciones(data);
        } else {
            $("#dropdown-notificaciones").empty();
            $("#dropdown-notificaciones").removeClass('dropdown-menu-top-2');
            pintarSinNotificaciones();
        }
    }).fail((err) => {
        
    });
}

function showPlaceholderMenu() {
    $("#dropdown-notificaciones").addClass('dropdown-menu-top-2');

    var html = `<div class="row texto-dropdown-notificacion-info">
            <div class="col-11 text-wrap texto-dropdown-notificacion">
                <span class="placeholder col-12 placeholder-sm" style="min-height: 0.7em;"></span>
                <span class="placeholder col-12 placeholder-sm" style="min-height: 0.7em;"></span>
            </div>
            <div class="col-1" style="align-self: center;">
                <span class="placeholder placeholder-sm" style="width: 15px; height: 16px; margin-left: -2px;"></span>
            </div>
        </div>
        <div class="row texto-dropdown-notificacion-info">
            <div class="col-12 hora-notificacion">
                <span class="placeholder placeholder-sm" style="width: 45px; min-height: 0.5em;"></span>
            </div>
        </div>`;

    var mensajeDising = document.createElement('div');
        mensajeDising.setAttribute("class", "dropdown-item");
        mensajeDising.setAttribute("style", "width: 251px;");
        mensajeDising.innerHTML = [
            html
        ].join('');
        document.getElementById('dropdown-notificaciones').insertBefore(mensajeDising, null);

    var mensajeDising = document.createElement('div');
        mensajeDising.setAttribute("class", "dropdown-item");
        mensajeDising.setAttribute("style", "width: 251px;");
        mensajeDising.innerHTML = [
            html
        ].join('');
        document.getElementById('dropdown-notificaciones').insertBefore(mensajeDising, null);
}

function pintarNotificaciones(notificaciones) {
    for (let index = 0; index < notificaciones.length; index++) {
        let notificacion = notificaciones[index];

        var html = `
            <div class="row texto-dropdown-notificacion-${tipoNotificacion(notificacion.tipo)}">
                <div class="col-11 text-wrap texto-dropdown-notificacion">${notificacion.mensaje}</div>
                <div class="col-1" style="align-self: center;">
                    <i class="fas fa-share-square" style="color: #0a889f;"></i>
                </div>
            </div>
            <div class="row texto-dropdown-notificacion-${tipoNotificacion(notificacion.tipo)}">
                <div class="col-12 hora-notificacion">
                    ${definirTiempo(notificacion.created_at)}
                </div>
            </div>
        `;
        
        var notificacionesNumber = '1';
        if (notificaciones.length == 2) {
            notificacionesNumber = '2';
        } else if (notificaciones.length > 2) {
            notificacionesNumber = '3';
        }
        
        $("#dropdown-notificaciones").addClass("dropdown-menu-top-"+notificacionesNumber);
        var notify = document.createElement('div');
        notify.setAttribute("class", "dropdown-item item-notificacion");
        // notify.setAttribute("id", "item-notificacion-"+notificacion.data);
        notify.setAttribute("onclick", notificacion.function+"("+notificacion.data+", "+notificacion.id+")");
        notify.innerHTML = [
            html
        ].join('');
        document.getElementById('dropdown-notificaciones').insertBefore(notify, null);
    }
}

function pintarSinNotificaciones() {
    $("#dropdown-notificaciones").addClass("dropdown-menu-top-1");
    
    var html = `
        <div>¡SIN NOTIFICACIONES!</div>
        <div>
            <i class="fas fa-check-circle" style="font-size: 20px; color: #22b0c9; opacity: 0.5;"></i>
        </div>
    `;

    var notify = document.createElement('div');
    notify.setAttribute("class", "dropdown-item");
    notify.setAttribute("style", "text-align: center; width: 250px;");
    notify.innerHTML = [
        html
    ].join('');
    document.getElementById('dropdown-notificaciones').insertBefore(notify, null);
}

function abrirPqrsfNotificacion(id_pqrsf, id_notificacion) {
    findDataPqrsf(id_pqrsf);
    leerNotificaciones(id_notificacion);
    var numeroNotificaciones = parseInt(localStorage.getItem("numero_notificaciones"));
    localStorage.setItem("numero_notificaciones", numeroNotificaciones - 1);
    $("#id_pqrsf_up").val(id_pqrsf);
    $("#mensaje_pqrsf_nuevo").val("");
    openDropDownNotificaciones(true);
    setNotificaciones(numeroNotificaciones - 1);
}

function clickAddImgPqrsfEvent() {
    if (mostrarAgregarImagenes) {
        mostrarAgregarImagenes = false;
        $("#button-add-img").removeClass('button-add-img-select');
        $("#button-add-img").addClass('button-add-img');
        $("#input-images-mensaje").hide();
    }
    else {
        mostrarAgregarImagenes = true;
        $("#button-add-img").removeClass('button-add-img');
        $("#button-add-img").addClass('button-add-img-select');
        $("#input-images-mensaje").show();
    }
    document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
}

function tipoNotificacion(tipo) {
    if (tipo == '1') return 'info'; 
    if (tipo == '2') return 'warning'; 
    if (tipo == '3') return 'error'; 
    return 'success';
}

function initSwipers() {
    new Swiper(".mySwiper", {
        effect: "cards",
        grabCursor: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
}

function htmlSwiperImg(imagenes) {
    if (imagenes.length == 1) {
        return `<img style="height: 180px; object-fit: contain; width: -webkit-fill-available;" src="${bucketUrl+imagenes[0].url_archivo}">`;
    }

    var html = ``;

    for (let index = 0; index < imagenes.length; index++) {
        var imagen = imagenes[index];
        if (index) {
            html+=`<div class="swiper-slide" role="group" style="width: 300px !important; z-index: 7; transform: ${medidasSwiper[index]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down;" src="${bucketUrl+imagen.url_archivo}">
                    <div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
                    </div>
                </div>`;
        } else {
            html+=`<div class="swiper-slide swiper-slide-visible swiper-slide-fully-visible swiper-slide-active" role="group" style="width: 300px !important; z-index: 9; transform: ${medidasSwiper[0]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down;" src="${bucketUrl+imagen.url_archivo}">
                    <div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
                    </div>
                </div>`;
        }
    }
    return `<div class="swiper mySwiper swiper-flip swiper-3d swiper-initialized swiper-horizontal swiper-watch-progress">
        <div class="swiper-wrapper" id="swiper-wrapper-730a983e14310fcd9" aria-live="polite" style="cursor: grab; overflow: hidden;">
            ${html}
            <div class="swiper-button-next" tabindex="0" role="button" aria-label="Next slide" aria-controls="swiper-wrapper-3ee8ff5d94abab7c" aria-disabled="false"></div>
            <div class="swiper-button-prev swiper-button-disabled" tabindex="-1" role="button" aria-label="Previous slide" aria-controls="swiper-wrapper-3ee8ff5d94abab7c" aria-disabled="true"></div>
            <div class="swiper-pagination swiper-pagination-bullets swiper-pagination-horizontal"><span class="swiper-pagination-bullet swiper-pagination-bullet-active" aria-current="true"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span></div>
            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
        </div>
    </div>`;
}

function definirTiempo(date) {

    var hoy = fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    var fechaMensahe = new Date(date);
    var fecha_mensaje = fechaMensahe.getFullYear()+'-'+("0" + (fechaMensahe.getMonth() + 1)).slice(-2)+'-'+("0" + (fechaMensahe.getDate())).slice(-2);
    var hora_mensaje = fechaMensahe.getHours()+''.slice(-2)+':'+fechaMensahe.getMinutes()+''.slice(-2)
    var dias_diferencia = dateDifferenceInDays(
        new Date(fecha_mensaje),
        new Date(hoy)
    );

    if (fecha_mensaje == hoy) {
        return 'Hoy '+hora_mensaje;
    }

    if (dias_diferencia == 1) {
        return 'Ayer '+hora_mensaje;
    }

    if (dias_diferencia < 6) {
        return daysWeek(date)+' '+hora_mensaje;
    }

    return fecha_mensaje+' '+hora_mensaje;
}

function findDataPqrsf(id) {

    $("#offcanvas-body-pqrsf").empty();
    document.getElementById('button-open-datelle-pqrsf').click();

    $.ajax({
        url: base_url + 'pqrsf-find',
        method: 'GET',
        data: {
            id: id
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        var data = res.data;

        if (id_usuario_logeado == data.id_usuario) {
            if (data.creador.lastname) $("#id_name_person_pqrsf").text(data.creador.firstname+' '+data.creador.lastname);
            else $("#id_name_person_pqrsf").text(data.creador.firstname);
            if (data.usuario.avatar) $("#offcanvas_header_img").attr("src",bucketUrl + data.usuario.avatar);
        } else {
            if (data.usuario.lastname) $("#id_name_person_pqrsf").text(data.usuario.firstname+' '+data.usuario.lastname);
            else $("#id_name_person_pqrsf").text(data.usuario.firstname);
            if (data.creador.avatar) $("#offcanvas_header_img").attr("src",bucketUrl + data.creador.avatar);
        }
        
        mostrarDatosCabeza(data);
        mostrarMensajesPqrsf(data.mensajes);
        initSwipers();

        document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
    }).fail((err) => {
        
    });
}

function mostrarDatosCabeza(data) {
    if (data.archivos) agregarSwiperImg(data.archivos);

    var asunto = document.createElement('p');
    asunto.setAttribute("style", "font-weight: bold; margin-top: 15px;");
    asunto.innerHTML = [
        data.asunto
    ].join('');
    document.getElementById('offcanvas-body-pqrsf').insertBefore(asunto, null);

    var descripcion = document.createElement('p');
    descripcion.setAttribute("style", "font-size: 13px;");
    descripcion.innerHTML = [
        data.descripcion
    ].join('');
    document.getElementById('offcanvas-body-pqrsf').insertBefore(descripcion, null);
}

function agregarSwiperImg(imagenes) {
    var html = ``;
    var item = document.createElement('div');
    if (imagenes.length == 1) {
        html = `<img style="height: 180px; object-fit: contain; width: -webkit-fill-available;" src="${bucketUrl+imagenes[0].url_archivo}">`;
        item.innerHTML = [
            html
        ].join('');
        document.getElementById('offcanvas-body-pqrsf').insertBefore(item, null);
        return;
    }
    for (let index = 0; index < imagenes.length; index++) {
        var imagen = imagenes[index];
        if (index) {
            html+=`<div class="swiper-slide" role="group" style="width: 300px !important; z-index: 7; transform: ${medidasSwiper[index]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down;" src="${bucketUrl+imagen.url_archivo}">
                    <div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
                    </div>
                </div>`;
        } else {
            html+=`<div class="swiper-slide swiper-slide-visible swiper-slide-fully-visible swiper-slide-active" role="group" style="width: 300px !important; z-index: 9; transform: ${medidasSwiper[0]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down;" src="${bucketUrl+imagen.url_archivo}">
                    <div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
                    </div>
                </div>`;
        }
    }
    item.setAttribute("class", "swiper mySwiper swiper-flip swiper-3d swiper-initialized swiper-horizontal swiper-watch-progress");
    
    item.innerHTML = [
        `<div class="swiper-wrapper" id="swiper-wrapper-730a983e14310fcd9" aria-live="polite" style="cursor: grab; overflow: hidden;">
            ${html}
            <div class="swiper-button-next" tabindex="0" role="button" aria-label="Next slide" aria-controls="swiper-wrapper-3ee8ff5d94abab7c" aria-disabled="false"></div>
            <div class="swiper-button-prev swiper-button-disabled" tabindex="-1" role="button" aria-label="Previous slide" aria-controls="swiper-wrapper-3ee8ff5d94abab7c" aria-disabled="true"></div>
            <div class="swiper-pagination swiper-pagination-bullets swiper-pagination-horizontal"><span class="swiper-pagination-bullet swiper-pagination-bullet-active" aria-current="true"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span><span class="swiper-pagination-bullet"></span></div>
            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
        </div>`
    ].join('');
    document.getElementById('offcanvas-body-pqrsf').insertBefore(item, null);
    return;
}

$('.input-images-mensaje').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});