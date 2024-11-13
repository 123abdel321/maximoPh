let mostrarAgregarImagenesTurnos = false;
let updatingStatusTurnos = false;
let openStatusTurnos = false;

function mostrarModalEvento(idTurno) {
    console.log('mostrarModalEvento: ',idTurno);
    $("#row-action-turnos").hide();
    $("#offcanvas-body-turnos").empty();
    document.getElementById('button-open-datelle-turnos').click();

    $.ajax({
        url: base_url + 'turnos',
        method: 'GET',
        data: {id: idTurno},
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        var data = res.data;

        $("#id_turnos_up").val(data.id);

        if (id_usuario_logeado == data.id_usuario) {
            $("#row-action-turnos").show();
            var nombreCreador = data.creador.firstname;
            nombreCreador+= data.creador.lastname ? ' '+data.creador.lastname : '';
            if (data.nit) nombreCreador+= ' '+data.nit.apartamentos;
            
            $("#id_name_person_turnos").text(nombreCreador);

            if (data.creador.avatar) {
                $("#offcanvas_turnos_header_img").attr("src",bucketUrl + data.creador.avatar);
            }
        } else {
            if (turno_responder) {
                $("#row-action-turnos").show();
            }
            if (data.responsable) {
                if (data.responsable.avatar) $("#offcanvas_turnos_header_img").attr("src",bucketUrl + data.responsable.avatar);
                
                if (data.responsable.lastname) {
                    $("#id_name_person_turnos").text(data.responsable.firstname+' '+data.responsable.lastname);
                } else {
                    $("#id_name_person_turnos").text(data.responsable.firstname);
                }
            } else if (data.creador) {
                if (data.creador.avatar) $("#offcanvas_turnos_header_img").attr("src",bucketUrl + data.creador.avatar);
            }
        }

        mostrarDatosCabezaTurno(data);
        mostrarMensajesTurno(data.eventos);
        actualizarBotonesTurno(data);
        initSwipers();

        document.getElementById("offcanvas-body-turnos").scrollTop = 10000000;

    }).fail((err) => {
        $('#updateZona').show();
        $('#saveZonaLoading').hide();
        var errorsMsg = "";
        var mensaje = err.responseJSON.message;
        if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
        } else {
            errorsMsg = mensaje
        }
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function mostrarDatosCabezaTurno(data) {
    if (data.archivos) agregarSwiperImgTurnos(data.archivos);
    console.log('data: ',data);
    let opciones = {
        weekday: 'long',  // Día de la semana completo
        year: 'numeric',  // Año
        month: 'long',    // Mes completo
        day: 'numeric',   // Día del mes
        hour: 'numeric',  // Hora
        minute: 'numeric', // Minuto
        second: 'numeric', // Segundo
    };

    var tipoMensaje = 'Turno';
    if (data.tipo == 1) tipoMensaje = 'Tarea';

    var fechaInicio = new Intl.DateTimeFormat('es-ES', opciones).format(new Date(data.fecha_inicio.replace(" ", "T")));
    var fechaFin = new Intl.DateTimeFormat('es-ES', opciones).format(new Date(data.fecha_fin.replace(" ", "T")));

    var detalleTiempos = document.createElement('p');
    detalleTiempos.setAttribute("style", "font-size: 13px;");
    detalleTiempos.innerHTML = [
        `<span class="badge bg-gradient-dark">${tipoMensaje}</span><br/><b>Fecha inicio:</b> ${fechaInicio} <br/> <b>Fecha fin</b>: ${fechaFin} <br/>`
    ].join('');
    document.getElementById('offcanvas-body-turnos').insertBefore(detalleTiempos, null);

    var asunto = document.createElement('h3');
    asunto.setAttribute("style", "font-weight: bold; margin-top: 15px; text-align: center; font-size: 20px;");
    asunto.innerHTML = [
        data.asunto
    ].join('');
    document.getElementById('offcanvas-body-turnos').insertBefore(asunto, null);

    var descripcion = document.createElement('p');
    descripcion.setAttribute("style", "font-size: 14px; font-weight: 600;");
    descripcion.innerHTML = [
        data.descripcion
    ].join('');
    document.getElementById('offcanvas-body-turnos').insertBefore(descripcion, null);

    if (data.estado == 2 && idRolUsuario != 1 && idRolUsuario != 2) $("#row-actios-pqrsf").hide();
    else $("#row-actios-pqrsf").show();
}

function mostrarMensajesTurno(mensajes) {
    for (let index = 0; index < mensajes.length; index++) {
        var html = ``;
        var className = '';
        var mensaje = mensajes[index];
        var htmlImagen = '';

        if (mensaje.archivos) htmlImagen = htmlSwiperImgTurno(mensaje.archivos);
        
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
        document.getElementById('offcanvas-body-turnos').insertBefore(mensajeDising, null);
    }
}

function agregarSwiperImgTurnos(imagenes) {
    var html = ``;
    var item = document.createElement('div');
    if (imagenes.length == 1) {
        html = `<img style="height: 180px; object-fit: contain; width: -webkit-fill-available;" src="${bucketUrl+imagenes[0].url_archivo}">`;
        item.innerHTML = [
            html
        ].join('');
        document.getElementById('offcanvas-body-turnos').insertBefore(item, null);
        return;
    }
    for (let index = 0; index < imagenes.length; index++) {
        var imagen = imagenes[index];
        if (index) {
            html+=`<div class="swiper-slide" role="group" style="width: 300px !important; z-index: 7; transform: ${medidasSwiper[index]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down; width: -webkit-fill-available; object-fit: contain;" src="${bucketUrl+imagen.url_archivo}">
                    <div class="swiper-slide-shadow swiper-slide-shadow-cards" style="opacity: 0;">
                    </div>
                </div>`;
        } else {
            html+=`<div class="swiper-slide swiper-slide-visible swiper-slide-fully-visible swiper-slide-active" role="group" style="width: 300px !important; z-index: 9; transform: ${medidasSwiper[0]} background-color: #c7c7c7;">
                    <img style="height: 180px; object-fit: scale-down; width: -webkit-fill-available; object-fit: contain;" src="${bucketUrl+imagen.url_archivo}">
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
    document.getElementById('offcanvas-body-turnos').insertBefore(item, null);
    return;
}

$("#butonActionActivoTurno").click( function(){
    ajaxActualizarEstadoTurno(0);
});

$("#butonActionProcesoTurno").click( function(){
    ajaxActualizarEstadoTurno(1);
});

$("#butonActionCerradoTurno").click( function(){
    ajaxActualizarEstadoTurno(2);
});

function ajaxActualizarEstadoTurno(estado) {
    if (!updatingStatusTurnos) {

        updatingStatusTurnos = true;
        var idMensaje = $("#id_turnos_up").val();

        $("#turnos-button-change-status-iconNormal").hide();
        $("#turnos-button-change-status-iconLoading").show();

        $.ajax({
            url: base_url + 'turnos-estado',
            method: 'POST',
            data: JSON.stringify({
                id: idMensaje,
                estado: estado
            }),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                var data = res.data;
                updatingStatusTurnos = false;
                $("#turnos-button-change-status-iconNormal").show();
                $("#turnos-button-change-status-iconLoading").hide(); 
                
                document.getElementById('content-button-status-turnos').click();
                actualizarEstadosTurno(data.estado);
                
                $(".update-status-turnos").hide();
                $("#turnos-button-change-status").addClass('button-change-status');
                $("#turnos-button-change-status").removeClass('button-change-status-select');
            }
        }).fail((res) => {
            updatingStatusTurnos = false;
            $("#turnos-button-change-status-iconNormal").show();
            $("#turnos-button-change-status-iconLoading").hide(); 
        });
    }
}

function htmlSwiperImgTurno(imagenes) {
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

function keyPressturnosMensaje(event) {
    if (event.keyCode == 13) {
        document.getElementById('button-send-turnos').click();
    }
}

function actualizarBotonesTurno(cabezaMensaje) {
    if (idRolUsuario == 1 || idRolUsuario == 2) {
        $('#content-button-status-turnos').show();
        $("#content-button-time-turnos-disabled").hide();
    } else {
        $('#content-button-status-turnos').hide();
        $("#content-button-time-turnos-disabled").show();
    }

    actualizarEstadosTurno(cabezaMensaje.estado);
}

function actualizarEstadosTurno (estado) {

    $("#estado_en_mensaje_turnos").removeClass('turnos-chat-activo');
    $("#estado_en_mensaje_turnos").removeClass('turnos-chat-proceso');
    $("#estado_en_mensaje_turnos").removeClass('turnos-chat-cerrado');

    $("#butonActionActivoTurno").hide();
    $("#butonActionProcesoTurno").hide();
    $("#butonActionCerradoTurno").hide();

    if (estado == 0 || estado == 3) {
        $("#estado_en_mensaje_turnos").addClass('turnos-chat-activo');
        $("#butonActionProcesoTurno").show();
        $("#butonActionCerradoTurno").show();
        $("#estado_en_mensaje_turnos").text("Activo");
    }
    if (estado == 1) {
        $("#estado_en_mensaje_turnos").addClass('turnos-chat-proceso');
        $("#butonActionActivoTurno").show();
        $("#butonActionCerradoTurno").show();
        $("#estado_en_mensaje_turnos").text("En proceso");
    }
    if (estado == 2) {
        $("#estado_en_mensaje_turnos").addClass('turnos-chat-cerrado');
        $("#butonActionActivoTurno").show();
        $("#butonActionProcesoTurno").show();
        $("#estado_en_mensaje_turnos").text("Cerrado");
    }
}

function clickAddImgturnosEvent() {
    if (mostrarAgregarImagenesTurnos) {
        mostrarAgregarImagenesTurnos = false;
        $("#button-add-img-turnos").removeClass('button-add-img-select');
        $("#button-add-img-turnos").addClass('button-add-img');
        $("#input-images-turnos").hide();
    }
    else {
        mostrarAgregarImagenesTurnos = true;
        $("#button-add-img-turnos").removeClass('button-add-img');
        $("#button-add-img-turnos").addClass('button-add-img-select');
        $("#input-images-turnos").show();
    }
    setTimeout(function(){
        document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
    },10);
}

function createMensajeturnos() {

    var form = document.querySelector('#form-turnos-mensajes');
    $('#mensaje_turnos_nuevo').removeClass("is-invalid-textarea");

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        
        $('#mensaje_turnos_nuevo').addClass("is-invalid-textarea");
        return;
    }

    var idMensaje = $("#id_turnos_up").val();

    $("#button-send-turnos").hide();
    $("#button-send-turnos-loading").show();

    var ajxForm = document.getElementById("form-turnos-mensajes");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "turnos-mensaje/"+idMensaje);
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        
        mostrarAgregarImagenesTurnos = false;
        $("#button-add-img-turnos").removeClass('button-add-img-turnos-select');
        $("#button-add-img-turnos").addClass('button-add-img-turnos');
        $("#input-images-turnos").hide();

        openStatusTurnos = false;
        $(".update-status-turnos").hide();
        $("#turnos-button-change-status").addClass('button-change-status');
        $("#turnos-button-change-status").removeClass('button-change-status-select');
        
        $("#button-send-turnos").show();
        $("#button-send-turnos-loading").hide();

        if (responseData.success) {
            resetImageTurnosUploader();
            $("#mensaje_turnos_nuevo").val("");
            setTimeout(function(){
                $("#mensaje_turnos_nuevo").focus().select();
            }, 100);
            document.getElementById("offcanvas-body-turnos").scrollTop = 10000000;
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
        }
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $("#button-send-turnos").show();
        $("#button-send-turnos-loading").hide();
    };
}

function changeEstadoTurnos() {
    if (openStatusTurnos) {
        openStatusTurnos = false;
        $(".update-status-turnos").hide();
        $("#turnos-button-change-status").addClass('button-change-status');
        $("#turnos-button-change-status").removeClass('button-change-status-select');
    } 
    else {
        openStatusTurnos = true;
        $(".update-status-turnos").show();
        $("#turnos-button-change-status").removeClass('button-change-status');
        $("#turnos-button-change-status").addClass('button-change-status-select');
    }
    setTimeout(function(){
        document.getElementById("offcanvas-body-turnos").scrollTop = 10000000;
    },10);
}

function resetImageTurnosUploader() {
    // Destruye el componente
    $('.input-images-turnos').html('');

    // // Reinicializa el componente
    $('.input-images-turnos').imageUploader({
        imagesInputName: 'photos',
        preloadedInputName: 'old',
        maxFiles: 10
    });
}