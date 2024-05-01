var swiper = null;
var pqrsf_table = null;
var $comboUsuarioPqrsf = null;
var mostrarAgregarImagenes = false;

function pqrsfInit() {

    pqrsf_table  = $('#pqrsfTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'pqrsf',
        },
        columns: [
            {"data":'id'},
            {"data": function (row, type, set){
                if (row.usuario) {
                    return row.usuario.firstname;
                }
                return '';
            }},
            {"data": function (row, type, set){
                return row.asunto;
            }},
            {"data": function (row, type, set){
                return `<div  class="text-wrap width-500">${row.descripcion}</div >`;
            }},
            {"data":'created_at'},
            {"data": function (row, type, set){
                if (row.estado == '1') {
                    return `<span class="badge bg-info">EN PROCESO</span><br/>`;
                }
                if (row.estado == '2') {
                    return `<span class="badge bg-success">CERRADO</span><br/>`;
                }
                return `<span class="badge bg-light text-dark">ACTIVO</span><br/>`;;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="readpqrsf_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success read-pqrsf" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>&nbsp;';
                    return html;
                }
            },
        ],
        columnDefs: [{ width: 500, targets: 4 }],
    });

    if (pqrsf_table) {
        pqrsf_table.on('click', '.read-pqrsf', function() {
            $("#offcanvas-body-pqrsf").empty();
            var id = this.id.split('_')[1];
            $("#id_pqrsf_up").val(id);
            
            loadingDataPqrsf();
            findDataPqrsf(id);            
        });
    }
    
    $comboUsuarioPqrsf = $('#id_usuario_pqrsf').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#pqrsfFormModal'),
        delay: 250,
        placeholder: "Seleccione un usuario",
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url + 'usuarios/combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    pqrsf_table.ajax.reload();
}

var channel = pusher.subscribe('pqrsf-mensaje-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    var idPqrsfOpen = $("#id_pqrsf_up").val();
    if (data.id_pqrsf == idPqrsfOpen) {
        mostrarMensajesPqrsf(data.data);
        document.getElementById("offcanvas-body-pqrsf").scrollTop = 10000000;
        initSwipers();
    }
    console.log('pqrsf-notificaciones', data);
});

function clickAddImgPqrsfEvent() {
    if (mostrarAgregarImagenes) {
        mostrarAgregarImagenes = false;
        $("#button-add-img").removeClass('button-add-img-select');
        $("#button-add-img").addClass('button-add-img');
        $("#input-images-pqrsf").hide();
    }
    else {
        mostrarAgregarImagenes = true;
        $("#button-add-img").removeClass('button-add-img');
        $("#button-add-img").addClass('button-add-img-select');
        $("#input-images-pqrsf").show();
    }
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

function loadingDataPqrsf() {
    var descripcion = document.createElement('div');
    descripcion.innerHTML = [
        `<div id="offcanvas-body-pqrsf-loading">
            <h5 class="card-title placeholder-glow">
                <span class="placeholder col-12" style="height: 190px;"></span>
            </h5>
            <h5 class="card-title placeholder-glow">
                <span class="placeholder col-3 placeholder-lg"></span>
                <span class="placeholder col-2 placeholder-lg"></span>
                <span class="placeholder col-5 placeholder-lg"></span>
            </h5>
            <h5 class="card-title placeholder-glow">
                <span class="placeholder col-12 placeholder-sm"></span>
                <span class="placeholder col-12 placeholder-sm"></span>
                <span class="placeholder col-12 placeholder-sm"></span>
                <span class="placeholder col-12 placeholder-sm"></span>
            </h5>
        </div>`
    ].join('');
    document.getElementById('offcanvas-body-pqrsf').insertBefore(descripcion, null);
}

function findDataPqrsf(id) {

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
        $("#offcanvas-body-pqrsf").empty();

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

function mostrarMensajesPqrsf(mensajes) {
    for (let index = 0; index < mensajes.length; index++) {
        var html = ``;
        var className = '';
        var mensaje = mensajes[index];

        if (mensaje.archivos) agregarSwiperImg(mensaje.archivos);
        
        if (id_usuario_logeado == mensaje.created_by) {
            className = 'mensaje-estilo-derecha';
            html+=`<p style="font-size: 13px; margin-bottom: 0; font-weight: 500;">${mensaje.descripcion}</p>
                <i class="fas fa-caret-down icono-mensaje-derecha"></i>`;
        } else {
            className = 'mensaje-estilo-izquierda';
            html+=`<p style="font-size: 13px; margin-bottom: 0; text-align-last: right; font-weight: 500;">${mensaje.descripcion}</p>
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
            initSwipers();
            $("#mensaje_pqrsf").val(" ");
            $("#mensaje_pqrsf").val("");
            setTimeout(function(){
                $("#mensaje_pqrsf").focus().select();
            });
            
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

$('.input-images-pqrsf').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

$(document).on('click', '#generatePqrsfNuevo', function () {
    clearFormPqrsf();
    $("#pqrsfFormModal").modal('show');
});

$(document).on('change', '#tipo_pqrsf', function () {
    var tipoPorteria = $("#tipo_pqrsf").val();
    if (tipoPorteria == '5') {
        $("#input_hora_inicio_pqrsf").show();
        $("#input_id_usuario_pqrsf").show();
        $("#input_hora_fin_pqrsf").show();
        $("#input_dias_pqrsf").show();
    } else {
        $("#input_hora_inicio_pqrsf").hide();
        $("#input_id_usuario_pqrsf").hide();
        $("#input_hora_fin_pqrsf").hide();
        $("#input_dias_pqrsf").hide();
    }
});

$("#form-pqrsf").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-pqrsf');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#savePqrsf").hide();
    $("#savePqrsfLoading").show();

    var ajxForm = document.getElementById("form-pqrsf");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "pqrsf");
    xhr.send(data);
    xhr.onload = function(res) {
        console.log('res: ',res);
        var responseData = JSON.parse(res.currentTarget.response);
        $('#savePqrsf').show();
        $('#savePqrsfLoading').hide();

        if (responseData.success) {
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
        }

        $("#pqrsfFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#savePqrsf').show();
        $('#savePqrsfLoading').hide();
    };
});

function clearFormPqrsf () {
    
}

function crearSwiper (row) {
    return '';
    var html = ``;
    if (row.archivos) {
        for (let index = 0; index < row.archivos.length; index++) {
            var imagen = row.archivos[index];
            if (index) {
                html+=`<div class="swiper-slide" role="group" aria-label="3 / 9" style="width: 240px; z-index: 7; transform: ${medidasSwiper[index]}">
                        <img style="width: 80px; height: 80px; object-fit: cover;" src="${bucketUrl+imagen.url_archivo}">
                    </div>`;
            } else {
                html+=`<div class="swiper-slide swiper-slide-visible swiper-slide-fully-visible swiper-slide-active" role="group" style="z-index: 9; transform: ${medidasSwiper[0]}">

                
                            <img style="width: 80px; height: 80px; object-fit: cover;" src="${bucketUrl+imagen.url_archivo}">
                        </div>`;
            }
        }
        return `<div class="swiper mySwiper swiper-flip swiper-3d swiper-initialized swiper-horizontal swiper-watch-progress">
                    <div class="swiper-wrapper" id="swiper-wrapper-730a983e14310fcd9" aria-live="polite" style="cursor: grab;">
                        ${html}
                    </div>
    
                    <div class="swiper-pagination swiper-pagination-bullets swiper-pagination-horizontal" style="margin-top: -20px !important; margin-left: 10px !important; position: absolute !important;">
                        <span class="swiper-pagination-bullet swiper-pagination-bullet-active" aria-current="true"></span>
                        <span class="swiper-pagination-bullet"></span>
                        <span class="swiper-pagination-bullet"></span>
                    </div>
    
                </div>`;
    } else {
        return `<img src="${bucketUrl+'img/no-photo.jpg'}"></img>`
    }
}