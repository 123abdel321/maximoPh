

function openDropDownNotificaciones(open = false) {
    closeMenu();
    var notificacionesAbiertas = $("#notificacionesMaximo").attr('class');
    notificacionesAbiertas = notificacionesAbiertas == 'offcanvas offcanvas-end show' ? true : false;

    $("#offcanvas-body-notificaciones").empty();
    $('#offcanvas-body-notificaciones').css('background-image', 'url(https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/loading-gif.gif');

    if (!notificacionesAbiertas) {
        document.getElementById('button-open-notificaciones').click();
    }

    $.ajax({
        url: base_url + 'notificaciones',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        var data = res.data;
        console.log('data: ',data);
        $('#offcanvas-body-notificaciones').css('background-image', 'none');
        if (data.length) pintarNotificaciones(data);
        else pintarSinNotificaciones();
        setNotificaciones(res.total);

    }).fail((err) => {
        $('#offcanvas-body-notificaciones').css('background-image', 'none');
    });
}

function openDropDownNotificacion(id_notificacion) {
    closeMenu();
    var notificacionesAbiertas = $("#notificacionesMaximo").attr('class');
    notificacionesAbiertas = notificacionesAbiertas == 'offcanvas offcanvas-end show' ? true : false;

    if (!notificacionesAbiertas) {
        document.getElementById('button-open-notificaciones').click();
    }

    $.ajax({
        url: base_url + 'notificacion',
        method: 'GET',
        headers: headers,
        dataType: 'json',
        data: {id: id_notificacion}
    }).done((res) => {
        var data = res.data;
        pintarNotificacion(data);

    }).fail((err) => {
        $('#offcanvas-body-notificaciones').css('background-image', 'none');
    });
}

function pintarNotificaciones(notificaciones, animate = false) {
    for (let index = 0; index < notificaciones.length; index++) {
        let notificacion = notificaciones[index];

        var nombre = localStorage.getItem("empresa_nombre")[0]+''+localStorage.getItem("empresa_nombre")[1];
        var nombreCompleto = localStorage.getItem("empresa_nombre");
        if (notificacion.creador) {
            nombre = notificacion.creador.firstname.split('')[0]+''+notificacion.creador.firstname.split('')[1];
            nombreCompleto = notificacion.creador.firstname+' '+notificacion.creador.lastname;
        } else {
            console.log('notificacion: ',notificacion);
        }

        var iconHeader = `<div aria-expanded="false" style="background-color:#0023ff;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;cursor:pointer;">
                ${nombre}
            </div>`;

        if (notificacion.creador && notificacion.creador.avatar) {
            var iconHeader = `<div style="background-image:url(${notificacion.creador.avatar});background-size:cover;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;">
                </div>`;
        }
        var tipoColores = 'sin_leer';
        var iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_campana.png`;
        if (notificacion.estado) {
            tipoColores = 'leido';
            iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_check.png`;
        } else if (notificacion.notificacion_type == 13) {
            var tipoColores = 'mensaje';
            iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_mensaje.png`;
        }

        var html = `
            <div id="notificacion-header-${notificacion.id}" class="card-header ${tipoColores} row">
                <div class="col-2">
                    ${iconHeader}
                </div>
                <div class="col-8" style="align-content: center;">
                    <p class="nombre-notificacion">${nombreCompleto}</p>
                </div>
                <div class="col-2">
                    <span id="button-close-${notificacion.id}" class="badge text-bg-danger notificacion-close-button" onclick="cerrarNotificaciones(${notificacion.id}, 2)">
                        <i class="fa fa-times" aria-hidden="true" ></i>
                    </span>
                    <span id="button-close-loading-${notificacion.id}" class="badge text-bg-danger notificacion-close-button-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </div>
            </div>
            <div class="card-body row" style="padding-top: .8rem; padding-bottom: .8rem;">
                <div class="col-2" style="align-content: center;">
                    <img src="${iconNotification}" style="width: 30px;"/>
                </div>
                <div class="col-10">
                    ${notificacion.mensaje}
                </div>
            </div>
            <div id="notificacion-footer-${notificacion.id}" class="card-footer ${tipoColores}" onclick="${notificacion.function+"("+notificacion.data+", "+notificacion.id+")"}">
                <p style="margin-bottom: 0px; font-size: 12px;">${notificacion.fecha_creacion}&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></p> 
            </div>
        `;

        var notify = document.createElement('div');
        notify.setAttribute("class", "card card-notification ");
        if (animate) notify.setAttribute("class", "animate__animated animate__fadeInRightBig");
        notify.setAttribute("id", "card-notification-"+notificacion.id);
        notify.innerHTML = [
            html
        ].join('');
        document.getElementById('offcanvas-body-notificaciones').insertBefore(notify, null);
    }
}

function pintarNotificacion(notificacion) {

    var nombre = localStorage.getItem("empresa_nombre")[0]+''+localStorage.getItem("empresa_nombre")[1];
    var nombreCompleto = localStorage.getItem("empresa_nombre");
    if (notificacion.creador) {
        nombre = notificacion.creador.firstname.split('')[0]+''+notificacion.creador.firstname.split('')[1];
        nombreCompleto = notificacion.creador.firstname+' '+notificacion.creador.lastname;
    } else {
        console.log('notificacion: ',notificacion);
    }

    var iconHeader = `<div aria-expanded="false" style="background-color:#0023ff;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;cursor:pointer;">
            ${nombre}
        </div>`;

    if (notificacion.creador && notificacion.creador.avatar) {
        var iconHeader = `<div style="background-image:url(https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/${notificacion.creador.avatar});background-size:cover;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;">
            </div>`;
    }
    var tipoColores = 'sin_leer';
    var iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_campana.png`;
    if (notificacion.estado) {
        tipoColores = 'leido';
        iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_check.png`;
    } else if (notificacion.notificacion_type == 13) {
        var tipoColores = 'mensaje';
        iconNotification = `https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/icon_mensaje.png`;
    }

    var html = `
        <div id="notificacion-header-${notificacion.id}" class="card-header ${tipoColores} row">
            <div class="col-2">
                ${iconHeader}
            </div>
            <div class="col-8" style="align-content: center;">
                <p class="nombre-notificacion">${nombreCompleto}</p>
            </div>
            <div class="col-2">
                <span id="button-close-${notificacion.id}" class="badge text-bg-danger notificacion-close-button" onclick="cerrarNotificaciones(${notificacion.id}, 2)">
                    <i class="fa fa-times" aria-hidden="true" ></i>
                </span>
                <span id="button-close-loading-${notificacion.id}" class="badge text-bg-danger notificacion-close-button-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </div>
        </div>
        <div class="card-body row" style="padding-top: .8rem; padding-bottom: .8rem;">
            <div class="col-2" style="align-content: center;">
                <img src="${iconNotification}" style="width: 30px;"/>
            </div>
            <div class="col-10">
                ${notificacion.mensaje}
            </div>
        </div>
        <div id="notificacion-footer-${notificacion.id}" class="card-footer ${tipoColores}" onclick="${notificacion.function+"("+notificacion.data+", "+notificacion.id+")"}">
            <p style="margin-bottom: 0px; font-size: 12px;">${notificacion.fecha_creacion}&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></p> 
        </div>
    `;

    var notify = document.createElement('div');
    notify.setAttribute("class", "card card-notification animate__animated animate__fadeInRightBig");
    notify.setAttribute("id", "card-notification-"+notificacion.id);
    notify.innerHTML = [
        html
    ].join('');

    document.getElementById('offcanvas-body-notificaciones').insertBefore(notify, null);

}

function pintarSinNotificaciones() {
    
    var html = `
        <img style="height: 80px; margin-bottom: 10px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/notificaciones_1.png">
        <h4 style="color: #5a5a5a;">!Sin notificaciones¡</h4>
    `;

    var data = document.createElement('div');
    data.setAttribute("class", "sin-notificaciones-icon");
    data.innerHTML = [
        html
    ].join('');
    document.getElementById('offcanvas-body-notificaciones').insertBefore(data, null);
}

function abrirPqrsfNotificacion(id_pqrsf, id_notificacion) {
    findDataPqrsf(id_pqrsf);
    actualizarNotificaciones(id_notificacion, 2);
    actualizarPqrsfRemitente(id_pqrsf);

    $("#id_pqrsf_up").val(id_pqrsf);
    $("#mensaje_pqrsf_nuevo").val("");
}

function cerrarNotificaciones(id, estado) {
    $("#button-close-"+id).hide();
    $("#button-close-loading-"+id).show();

    $("#notificacion-header-"+id).removeClass('sin_leer');
    $("#notificacion-header-"+id).removeClass('leido');
    $("#notificacion-header-"+id).addClass('eliminando');
    $("#notificacion-footer-"+id).removeClass('sin_leer');
    $("#notificacion-footer-"+id).removeClass('leido');
    $("#notificacion-footer-"+id).addClass('eliminando');

    $.ajax({
        url: base_url + 'notificaciones',
        method: 'PUT',
        headers: headers,
        data: JSON.stringify({id: id, estado: estado}),
        dataType: 'json',
    }).done((res) => {
        $("#card-notification-"+id).addClass('animate__animated animate__fadeOutRightBig');
        setTimeout(function(){
            document.getElementById("card-notification-"+id).remove();
        },500);
        if (!res.count_total) {
            pintarSinNotificaciones();
        }
        localStorage.setItem("numero_notificaciones", res.count);
        setNotificaciones(res.count);

    }).fail((res) => {
        $("#button-close-"+id).show();
        $("#button-close-loading-"+id).hide();
    });
}

function actualizarNotificaciones(id, estado) {
    $.ajax({
        url: base_url + 'notificaciones',
        method: 'PUT',
        headers: headers,
        data: JSON.stringify({id: id, estado: estado}),
        dataType: 'json',
    }).done((res) => {
        if (!res.count) {
            pintarSinNotificaciones();
        }
        localStorage.setItem("numero_notificaciones", res.count);
        setNotificaciones(res.count);
    }).fail((res) => {
    });
}

function actualizarPqrsfRemitente(id) {
    $.ajax({
        url: base_url + 'pqrsf-destinatario',
        method: 'PUT',
        headers: headers,
        data: JSON.stringify({id: id}),
        dataType: 'json',
    }).done((res) => {
    }).fail((res) => {
    });
}

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}