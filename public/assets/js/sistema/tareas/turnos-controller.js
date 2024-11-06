var $comboTurnoUsuarioFilter = null;
var $comboUsuarioTurno = null;
var calendarioTurnos = null;
var $comboTurno = null;
var turnos_table = null;
var diaTurno = [
    "diaTurno1",
    "diaTurno2",
    "diaTurno3",
    "diaTurno4",
    "diaTurno5",
    "diaTurno6",
    "diaTurno7"
];

function turnosInit() {

    var calendarEl = document.getElementById('turnos-fullcalender');
    calendarioTurnos = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',  // Vista inicial (mes)
        // timeZone: 'UTC',
        headerToolbar: {
            left: 'prev,next today',     // Botones de navegación
            center: 'title',             // Título del calendario
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'  // Vistas disponibles
        },
        editable: true,
        droppable: true,
        expandRows: true,
        selectable: true,
        locale: 'es',
        events: {
            url: 'turnos-event',
            method: 'GET',
            extraParams: function() {
                return {
                    id_empleado: $("#id_usuario_filter_turno").val(),
                    id_proyecto: $("#id_proyecto_filter_turno").val(),
                    tipo: $("#tipo_actividad_filter_turno").val(),
                    estado: $("#estado_filter_turno").val(),
                };
            },
            failure: function() {
                agregarToast('error', 'Actualización errada', 'Error al cargar los eventos!');
            }
        },
        eventDrop: function(info) {
            cambiarRangoDeTurno(info.event);
        },
        eventReceive: function(info) {
            cambiarRangoDeTurno(info.event);
        },
        eventResize: function(info) {
            cambiarRangoDeTurno(info.event);
        },
        select: function(info) {
            seleccionarRangoDeTurnos(info);
        },
        eventClick: function(info) {
            mostrarModalEvento(info.event.id);
        },
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true, // Hacer que las filas de eventos ocupen todo el espacio vertical disponible
        buttonText: {
            prev: 'Ant',
            next: 'Sig',
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Agenda',
        },
        buttonHints: {
            prev: '$0 antes',
            next: '$0 siguiente',
            today(buttonText) {
                return (buttonText === 'Día') ? 'Hoy' :
                    ((buttonText === 'Semana') ? 'Esta' : 'Este') + ' ' + buttonText.toLocaleLowerCase();
            },
        },
        viewHint(buttonText) {
            return 'Vista ' + (buttonText === 'Semana' ? 'de la' : 'del') + ' ' + buttonText.toLocaleLowerCase();
        },
        weekText: 'Sm',
        weekTextLong: 'Semana',
        allDayText: 'Todo el día',
        moreLinkText: 'más',
        moreLinkHint(eventCnt) {
            return `Mostrar ${eventCnt} eventos más`;
        },
        noEventsText: 'No hay eventos para mostrar',
        navLinkHint: 'Ir al $0',
        closeHint: 'Cerrar',
        timeHint: 'La hora',
        eventHint: 'Evento',
        views: {
            dayGridMonth: {
                titleFormat: { year: 'numeric', month: 'long' }
            },
            timeGridWeek: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            },
            timeGridDay: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            },
            listWeek: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            }
        },
        datesSet: function(view) {
            setTimeout(function() {
                applyPerfectScrollbar();
            }, 0);
        }
    });
    calendarioTurnos.render();

    turnos_table = $('#turnosTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'turnos-table',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_turnos_filter').val();
                d.fecha_hasta = $('#fecha_hasta_turnos_filter').val();
                d.id_proyecto = $('#id_proyecto_filter_turno_table').val();
                d.id_usuario = $('#id_usuario_filter_turno_table').val();
                d.tipo = $('#tipo_actividad_filter_turno_table').val();
                d.estado = $('#estado_turnos_filter_table').val();
            }
        },
        columns: [
            {"data":'id'},
            {"data": function (row, type, set){
                if (row.estado == '1') {
                    return `<span class="badge bg-info">EN PROCESO</span><br/>`;
                }
                if (row.estado == '2') {
                    return `<span class="badge bg-success">CERRADO</span><br/>`;
                }
                if (row.estado == '3') {
                    return `<span class="badge bg-dark">VISTO</span><br/>`;
                }
                return `<span class="badge bg-danger">SIN LEER</span><br/>`;
            }},
            {"data": function (row, type, set){
                if (row.tipo == 0) {
                    return `TURNO`;
                }
                if (row.tipo == 1) {
                    return `TAREA`;
                }
                return `NINGUNO`;
            }},
            {"data": function (row, type, set){
                if (row.responsable) {
                    return row.responsable.firstname+' '+row.responsable.lastname;
                }
                return '';
            }},
            {"data": function (row, type, set){
                return row.asunto;
            }},
            {"data": function (row, type, set){
                return `<div  class="text-wrap width-500">${row.descripcion}</div >`;
            }},
            {"data":'fecha_creacion'},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="readturnos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success read-turnos" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>&nbsp;';
                    if (eliminarTurnos && row.eventos.length == 0) html+= '<span id="deleteturnos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-turnos" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ],
        columnDefs: [{ width: 500, targets: 5 }],
    });

    if (turnos_table) {
        //MOSTRAR TURNOS
        turnos_table.on('click', '.read-turnos', function() {
            var id = this.id.split('_')[1];
            mostrarModalEvento(id);
        });
        //BORRAR TURNOS
        turnos_table.on('click', '.drop-turnos', function() {
            var trTurno = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, turnos_table);
            var texto = 'Turno';
            var responsable = data.responsable.firstname+' '+data.responsable.lastname;
            if (data.tipo == 1) texto = 'Tarea';
            
            Swal.fire({
                title: 'Eliminar '+texto+' de: '+responsable+'?',
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'turnos',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            turnos_table.row(trTurno).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', texto+' eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            });
        });
    }

    $("#id_usuario_filter_turno").on('change', function(event) {
        reloadTurnos();
    });

    $("#tipo_actividad_filter_turno").on('change', function(event) {
        reloadTurnos();
    });

    $("#estado_filter_turno").on('change', function(event) {
        reloadTurnos();
    });

    $("#id_usuario_filter_turno_table").on('change', function(event) {
        turnos_table.ajax.reload();
    });

    $("#tipo_actividad_filter_turno_table").on('change', function(event) {
        turnos_table.ajax.reload();
    });

    $("#estado_turnos_filter_table").on('change', function(event) {
        turnos_table.ajax.reload();
    });

    $("#fecha_desde_turnos_filter").on('change', function(event) {
        turnos_table.ajax.reload();
    });

    $("#fecha_hasta_turnos_filter").on('change', function(event) {
        turnos_table.ajax.reload();
    });
}

function applyPerfectScrollbar() {
    if ($(".fc-scroller-liquid-absolute").length >= 1) new PerfectScrollbar($(".fc-scroller-liquid-absolute")[0]);
    if ($(".fc-scroller").length >= 1) new PerfectScrollbar($(".fc-scroller")[0]);
    if ($(".fc-scroller").length >= 2) new PerfectScrollbar($(".fc-scroller")[1]);
    if ($(".fc-scroller").length >= 3) new PerfectScrollbar($(".fc-scroller")[2]);
}

$('.input-images-turno').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    // maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

$('.input-images-turno-evento').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    // maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

$(document).on('click', '#createTurno', function () {
    clearFormTurno();
    $("#turnoFormModal").modal('show');
});

$(document).on('click', '#reloadTurnos', function () {
    reloadTurnos();
});

function reloadTurnos() {
    $("#reloadTurnosIconNormal").hide();
    $("#reloadTurnosIconLoading").show();

    setTimeout(function(){
        $("#reloadTurnosIconNormal").show();
        $("#reloadTurnosIconLoading").hide();
    },500);

    calendarioTurnos.removeAllEvents();
    calendarioTurnos.refetchEvents();
    turnos_table.ajax.reload();
}

$(document).on('click', '#deleteTurno', function () {
    var idItem = $("#id_turno_evento").val();

    $("#turnoEventoFormModal").modal('hide');

    Swal.fire({
        title: 'Eliminar evento ?',
        text: "No se podrá revertir!",
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Borrar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){
            $.ajax({
                url: base_url + 'turnos',
                method: 'DELETE',
                data: JSON.stringify({id: idItem}),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    calendarioTurnos.removeAllEvents();
                    calendarioTurnos.refetchEvents();
                    agregarToast('exito', 'Eliminación exitosa', 'Evento eliminada con exito!', true );
                } else {
                    agregarToast('error', 'Eliminación errada', res.message);
                }
            }).fail((res) => {
                agregarToast('error', 'Eliminación errada', res.message);
            });
        }
    })
});

$(document).on('click', '#detalleTurno', function () {
    turnos_table.ajax.reload();
    $('#tabla_turnos').show();
    $('#volverTurnos').show();
    $('#detalleTurno').hide();
    $('#calendar_turnos').hide();
});

$(document).on('click', '#volverTurnos', function () {
    $('#tabla_turnos').hide();
    $('#volverTurnos').hide();
    $('#detalleTurno').show();
    $('#calendar_turnos').show();
});

$("#multiple_tarea_turno").on('change', function(event) {
    if ($("input[type='checkbox']#multiple_tarea_turno").is(':checked')) {
        $("#input_dias_turno").show();
    } else {
        $("#input_dias_turno").hide();
    }
});

function clearFormTurno () {
    
    $("#id_usuario_turno").val('').change();
    $("#id_proyecto_turno").val('').change();
    $("#fecha_inicio_turno").val("");
    $("#fecha_fin_turno").val("");
    $("#hora_inicio_turno").val("");
    $("#hora_fin_turno").val("");
    $("#asunto_turno").val("");
    $("#mensaje_turno").val("");
    $("#asunto_turno").val("");
    $("#area_turno").val(1);
    
    $('.input-images-turno').imageUploader('destroy');
    // Eliminar todo el contenido del contenedor
    $('.input-images-turno').empty();
    // Re-inicializar el componente imageUploader
    $('.input-images-turno').imageUploader({
        imagesInputName: 'photos',
        preloadedInputName: 'old',
        // maxSize: 2 * 1024 * 1024,
        maxFiles: 10
    });

    const today = new Date().toISOString().split('T')[0];
    
    // Asignar la fecha mínima al campo de fecha
    document.getElementById("fecha_inicio_turno").setAttribute("min", today);
    document.getElementById("fecha_fin_turno").setAttribute("min", today);
    
    diaTurno.forEach(dia => {
        $('#'+dia).prop('checked', false);
    });
}

$comboUsuarioTurno = $('#id_usuario_turno').select2({
    theme: 'bootstrap-5',
    dropdownParent: $('#turnoFormModal'),
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

$('#id_usuario_filter_turno_table').select2({
    theme: 'bootstrap-5',
    delay: 250,
    placeholder: "Seleccione un usuario",
    allowClear: true,
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

$comboTurnoUsuarioFilter = $('#id_usuario_filter_turno').select2({
    theme: 'bootstrap-5',
    delay: 250,
    placeholder: "Seleccione un usuario",
    allowClear: true,
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

$comboTurno  = $('#id_proyecto_turno').select2({
    theme: 'bootstrap-5',
    dropdownParent: $('#turnoFormModal'),
    delay: 250,
    placeholder: "Seleccione un proyecto",
    allowClear: true,
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
        url: base_url + 'proyectos-combo',
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


$('#id_proyecto_filter_turno').select2({
    theme: 'bootstrap-5',
    delay: 250,
    placeholder: "Seleccione un proyecto",
    allowClear: true,
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
        url: base_url + 'proyectos-combo',
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

$('#id_proyecto_filter_turno_table').select2({
    theme: 'bootstrap-5',
    delay: 250,
    placeholder: "Seleccione un proyecto",
    allowClear: true,
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
        url: base_url + 'proyectos-combo',
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

$("#form-turno").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-turno');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveTurno").hide();
    $("#saveTurnoLoading").show();

    var ajxForm = document.getElementById("form-turno");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "turnos");
    xhr.send(data);
    xhr.onload = function(res) {
        var data = res.currentTarget;
        if (data.responseURL == 'https://maximoph.com/login') {
            caduqueSession();
        }

        var responseData = JSON.parse(res.currentTarget.response);
        $('#saveTurno').show();
        $('#saveTurnoLoading').hide();

        $("#reloadTurnosIconNormal").hide();
        $("#reloadTurnosIconLoading").show();

        setTimeout(function(){
            $("#reloadTurnosIconNormal").show();
            $("#reloadTurnosIconLoading").hide();
        },500);

        calendarioTurnos.removeAllEvents();
        calendarioTurnos.refetchEvents();

        if (responseData.success) {
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
            turnos_table.ajax.reload();
            $("#turnoFormModal").modal('hide');
        } else {

            var errorsMsg = "";
            var mensaje = responseData.message;
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
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#saveTurno').show();
        $('#saveTurnoLoading').hide();
    };
});

$("#form-turno-evento").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-turno-evento');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveEventoTurno").hide();
    $("#saveEventoTurnoLoading").show();

    var ajxForm = document.getElementById("form-turno-evento");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "turnos-evento");
    xhr.send(data);
    xhr.onload = function(res) {
        var data = res.currentTarget;
        if (data.responseURL == 'https://maximoph.com/login') {
            caduqueSession();
        }
        if (data.status > 299) {
            agregarToast('error', 'Ha ocurrido un error', 'Error '+data.status);
        }

        var responseData = JSON.parse(res.currentTarget.response);
        $('#saveEventoTurno').show();
        $('#saveEventoTurnoLoading').hide();

        calendarioTurnos.addEvent(responseData.data);

        if (responseData.success) {
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
        }

        $("#turnoFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#saveEventoTurno').show();
        $('#saveEventoTurnoLoading').hide();
    };
});

function seleccionarRangoDeTurnos(info) {
    clearFormTurno();

    var [fechaInicio, horaInicio] = armarFecha(info.start);
    var [fechaFin, horaFin] = armarFecha(info.end);

    $('#fecha_inicio_turno').val(fechaInicio);
    $('#fecha_fin_turno').val(fechaFin);

    $('#hora_inicio_turno').val(horaInicio);
    $('#hora_fin_turno').val(horaFin);

    recorrerFechas(fechaInicio, fechaFin);

    $("#turnoFormModal").modal('show');
}

function cambiarRangoDeTurno(info) {
    var [fechaInicio, horaInicio] = armarFecha(info.start);
    var [fechaFin, horaFin] = armarFecha(info.end);

    let data = {
        id: info.id,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
    }

    $.ajax({
        url: base_url + 'turnos',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            agregarToast('exito', 'Actualización exitosa', 'Evento actualizado con exito!', true);
        }
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

function agregarEventoPrincipal (data) {
    $("#div-contenido-eventos").html("");

    var htmlImagen = '';
    var imagen = data.creador && data.creador.avatar ? data.creador.avatar : "/img/no-photo.jpg";

    if (data.archivos.length) {
        for (let index = 0; index < data.archivos.length; index++) {
            const element = data.archivos[index];
            htmlImagen+= `<img style="height: 180px;" src="${bucketUrl+element.url_archivo}">`;
        }
    }

    var html = `<div class="row" style="padding: 5px; background-color: #defaff; border-radius: 10px;">
            <div id="text-usuario-evento" class="col-10" style="place-self: center; color: black;">
                
                ${htmlImagen}<br/>
                <h3 style="font-size: 15px; font-weight: 600;">${data.descripcion}</h3>
                
            </div>
        </div>`;

    var mentaje = document.createElement('div');
    mentaje.setAttribute("id", "view-contenido-eventos");
    mentaje.setAttribute("style", "padding: 10px 25px 10px 25px; border-radius: 5px; border: solid 1px #d3d3d3;");

    mentaje.innerHTML = [
        html
    ].join('');

    document.getElementById('div-contenido-eventos').insertBefore(mentaje, null);
}

function agregarEventoSecundario (eventos) {
    eventos.forEach(evento => {
        var htmlImagen = '';
        var imagen = evento.creador.avatar ? evento.creador.avatar : "/img/no-photo.jpg";

        if (evento.archivos.length) {
            for (let index = 0; index < evento.archivos.length; index++) {
                const element = evento.archivos[index];
                htmlImagen+= `<img style="height: 180px;" src="${bucketUrl+element.url_archivo}">`;
            }
        }

        var html = `<div id="imagen-usuario-evento" class="col-2">
                <img src="${imagen}" alt="profile_image" style="width: 50px; border-radius: 50%;">
            </div>
            <div id="text-usuario-evento" class="col-10" style="place-self: center; color: black;">
                
                ${htmlImagen}<br/>
                <h3 style="font-size: 15px; font-weight: 600;">${evento.descripcion}</h3>
                
            </div>`;

        var mentaje = document.createElement('div');
        mentaje.setAttribute("class", "row");
        mentaje.setAttribute("style", "padding: 5px; background-color: #f6ffde; border-radius: 10px; margin-top: 10px;");
    
        mentaje.innerHTML = [
            html
        ].join('');
    
        document.getElementById('view-contenido-eventos').insertBefore(mentaje, null);
    });
}

function armarFecha (fecha) {

    fecha = new Date(fecha);

    const fechaFormateada = fecha.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).split('/').reverse().join('-');
    
    const horaFormateada = fecha.toLocaleTimeString('es-CO', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    return [fechaFormateada, horaFormateada];
}

function recorrerFechas(fechaInicio, fechaFin) {
    fechaFin = new Date(fechaFin);
    let currentDate = new Date(fechaInicio);

    diaTurno.forEach(dia => {
        $('#'+dia).prop('checked', false);
    });

    while (currentDate.getTime() <= fechaFin.getTime()) {
        let diaSemana = currentDate.getDay();
        diaSemana = diaSemana == 7 ? 0 : diaSemana;
        $('#'+diaTurno[diaSemana]).prop('checked', true);
        currentDate.setDate(currentDate.getDate() + 1);
    }
}
