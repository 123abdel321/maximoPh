var dataPorteria = [];
var abrirEditarItemPorteria = true;
var diaPorteria = [
    "diaPorteria0",
    "diaPorteria1",
    "diaPorteria2",
    "diaPorteria3",
    "diaPorteria4",
    "diaPorteria5",
    "diaPorteria6",
    "diaPorteria7"
];
var semanaPorteria = [
    'none',
    'lun',
    'mar',
    'mie',
    'jue',
    'vie',
    'sab',
    'dom'
];
var weekGoalkeeper = [
    'none',
    'Mon',
    'Tue',
    'Wed',
    'Thu',
    'Fri',
    'Sat',
    'Sun'
];
var searchValuePorteria = null;
var buscarDatosPorteria = false;
var porteria_evento_table = null;
var $comboPorteriaEventos = null;
var $comboInmuebleEventos = null;

function porteriaInit() {
    if (crearPorteria) {
        $("#items-tabla-porteria").hide();
        loadItemsPorteria();
    } 

    if (eventoPorteria) {
        $("#loading-porteria").hide();
        porteria_evento_table = $('#eventoPorteriaTable').DataTable({
            pageLength: 100,
            dom: 'Brt',
            paging: false,
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
                url: base_url + 'porteriaevento',
            },
            columns: [
                {"data": function (row, type, set){
                    var urlImg = `logos_empresas/no-photo.jpg`;
                    var nameImg = 'none-img'
                    if (row.archivos.length) {
                        urlImg = row.archivos[0].url_archivo;
                        nameImg = row.archivos[0].created_at;
                    }
                    return `<img
                        style="height: 40px; border-radius: 10%; cursor: pointer;"
                        onclick="mostrarEventoPorteria(${row.id})"
                        src="${bucketUrl}${urlImg}"
                        alt="${nameImg}"
                    />`;

                }, className: 'dt-body-center'},
                {"data": function (row, type, set){
                    if (row.inmueble) {
                        return row.inmueble.zona.nombre+' - '+row.inmueble.nombre
                    }
                    return '';
                }},
                {"data": function (row, type, set){
                    if (row.persona) {
                        if (row.persona.tipo_porteria == 3) {
                            return row.persona.placa;
                        }
                        return row.persona.nombre;
                    }
                    return '';
                }},
                {"data":'fecha_ingreso'},
                {"data":'fecha_salida'},
                {"data":'observacion'},
                {"data": function (row, type, set){  
                    var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                    if(!row.created_by && !row.fecha_creacion) return '';
                    if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                    return html;
                }},
            ],
        });

        $comboInmuebleEventos = $('#inmueble_porteria_evento').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#porteriaEventoFormModal'),
            delay: 250,
            placeholder: "Seleccione un inmueble",
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
                url: 'api/inmueble-combo',
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
                },
            },
            templateResult: formatInmueblePorteriaCombo,
            templateSelection: formatInmueblePorteriaSelection
        });

        $comboPorteriaEventos = $('#persona_porteria_evento').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#porteriaEventoFormModal'),
            delay: 250,
            placeholder: "Seleccione un item",
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
                url: 'api/porteria-combo',
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
                },
            },
            templateResult: formatPorteriaCombo,
            templateSelection: formatPorteriaSelection
        });
    }

    $('.water').hide();
    if (porteria_evento_table) {
        porteria_evento_table.ajax.reload();
    }
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});


$(document).on('click', '#generatePorteriaNueva', function () {
    clearFormPorteria();
    $("#porteriaFormModal").modal('show');
});

$(document).on('click', '#generateEventoPorteria', function () {
    clearFormEventoPorteria();
    $("#porteriaEventoFormModal").modal('show');
});

$(document).on('click', '#updatePorteriaEvento', function () {

    $("#updatePorteriaEvento").hide();
    $("#updatePorteriaEventoLoading").show();

    let data = {
        id: $("#id_evento_porteria_up").val(),
        observacion: $("#observacion_evento_valor").val(),
    };

    $.ajax({
        url: base_url + 'porteriaevento',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#saveEstadoCuentaPago').show();
        $('#updatePorteriaEventoLoading').hide();
        if (porteria_evento_table) {
            porteria_evento_table.ajax.reload();
        }
        $("#porteriaEventoShowFormModal").modal('hide');
        agregarToast('exito', 'Evento actualziado', 'Evento actualizado con exito!', true);
    }).fail((err) => {
        agregarToast('error', 'Actualización errada', 'Actualización con errores');
    });
});

$(document).on('change', '#tipo_porteria_create', function () {
    var tipoPorteria = $("#tipo_porteria_create").val();
    hideInputPorteria();
    if(parseInt(tipoPorteria) == 1) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
    }
});

$(document).on('change', '#fecha_salida_porteria_evento', function () {
    $("#fecha_ingreso_porteria_evento").val("");
});

$(document).on('change', '#fecha_ingreso_porteria_evento', function () {
    $("#fecha_salida_porteria_evento").val("");
});

$(document).on('change', '#tipo_vehiculo_porteria', function () {
    var tipoVehiculo = $("#tipo_vehiculo_porteria").val();
    if(tipoVehiculo) {
        $("#input_placa_persona_porteria").show();
    } else {
        $("#input_placa_persona_porteria").hide();
    }
});

$("#form-porteria").submit(function(e) {
    e.preventDefault();

    var update = false;
    $("#savePorteria").hide();
    $("#savePorteriaLoading").show();

    if ($("#id_porteria_up").val()) update = true;

    var ajxForm = document.getElementById("form-porteria");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "porteria");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();

        if (update) {
            var id = $("#id_porteria_up").val();
            document.getElementById('item_card_porteria_'+id).remove();
            var indexPorteria = dataPorteria.findIndex(item => item.id == id);
            dataPorteria.splice(indexPorteria,1);
        }

        dataPorteria.push(responseData.data);
        if (responseData.success) {
            createItemPorteria(responseData.data);
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }

        $("#porteriaFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();
    };
});

$("#form-porteria-evento").submit(function(e) {
    e.preventDefault();

    $("#savePorteriaEvento").hide();
    $("#savePorteriaEventoLoading").show();
    $comboInmuebleEventos.prop('disabled', false);

    var ajxForm = document.getElementById("form-porteria-evento");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "porteriaevento");
    xhr.send(data);
    xhr.onload = function(res) {
        $('#savePorteriaEvento').show();
        $('#savePorteriaEventoLoading').hide();

        $("#items-card-porteria").hide();
        $("#items-tabla-porteria").show();
        if (porteria_evento_table) {
            porteria_evento_table.ajax.reload();
        }
        agregarToast('exito', 'Evento creado', 'Evento creado con exito!', true);

        $("#porteriaEventoFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Evento errada', 'Error al crear evento');
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();
    };
});

function searchPorteria (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        if (!$('#searchInputPorteria').val()) {
            $("#items-card-porteria").show();
            $("#items-tabla-porteria").hide();
            loadItemsPorteria();
        }
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePorteria = $('#searchInputPorteria').val();
    searchValuePorteria = searchValuePorteria+botonPrecionado;
    if(event.key == 'Backspace') searchValuePorteria = searchValuePorteria.slice(0, -1);

    if (eventoPorteria) {
        if (searchValuePorteria) {
            $("#items-card-porteria").show();
            $("#items-tabla-porteria").hide();
            loadItemsPorteria();
        } else {
            $("#items-card-porteria").hide();
            $("#items-tabla-porteria").show();
        }
    }

    if (crearPorteria) {
        loadItemsPorteria();
    }

}

function loadItemsPorteria() {
    $("#loading-porteria").show();
    if (document.getElementById('items-card-porteria')) {
        document.getElementById('items-card-porteria').innerHTML = "";
    }

    if (buscarDatosPorteria) {
        buscarDatosPorteria.abort();
    }

    buscarDatosPorteria = $.ajax({
        url: base_url + 'porteria',
        method: 'GET',
        data: {
            search: searchValuePorteria
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#loading-porteria").hide();
        buscarDatosPorteria = false;
        if(res.success){
            res.data.forEach(data => {
                createItemPorteria(data);
            });
        }
    }).fail((err) => {
        buscarDatosPorteria = false;
    });
}

function readURLPorteria(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_porteria').attr('src', e.target.result);
            $('#new_avatar_porteria').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_porteria').hide();
        $('#new_avatar_porteria').show();
    }
}

function readURLEvento(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_evento').attr('src', e.target.result);
            $('#new_avatar_evento').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_evento').hide();
        $('#new_avatar_evento').show();
    }
}

function createItemPorteria(porteria) {
    var imagen = bucketUrl + 'logos_empresas/no-photo.jpg';

    var tipoPorteria = 'Propietario';
    var colorTipo = '#7859ed';
    if (porteria.tipo_porteria == 1) {
        tipoPorteria = 'Persona';
        colorTipo = '#59bded';
    } else if (porteria.tipo_porteria == 2) {
        tipoPorteria = 'Mascota';
        colorTipo = '#4cd361';
    } else if (porteria.tipo_porteria == 3) {
        tipoPorteria = 'Vehiculo';
        colorTipo = '#e4b040';
    }

    if (porteria.archivos.length > 0) {
        imagen = bucketUrl + porteria.archivos[0].url_archivo;
    }

    var action = ``;

    if (crearPorteria) {
        action = `onclick="editarItemPorteria(${porteria.id})"`;
    }

    if (eventoPorteria) {
        action = `onclick="agregarEventoPorteria(${porteria.id})"`;
    }    

    var html = `
        <div class="card card-item-porteria" style="margin-bottom: 10px; height: 100%; overflow: hidden;" ${action}>
            <i id="loading-card-porteria-${porteria.id}" class="fa fa-spinner fa-pulse fa-4x fa-fw" style="position: absolute; top: 45%; left: 35%; color: lightseagreen; display: none;"></i>
            <img style="height: 160px; object-fit: cover; object-position: top;" class="card-img-top img-porteria" src="${imagen}" alt="name_unique">
            <div class="ribbon" style="background-color: ${colorTipo};">${tipoPorteria}</div>
            <div class="card-body" style="align-content: center; ">
                ${autorizado(porteria)}
                ${nombrePorteria(porteria)}
                ${placaVehiculo(porteria)}
                ${observacionPorteria(porteria)}
                ${diasPermiso(porteria)}
            </div>
            ${footerPorteria(porteria)}
        </div>
    `;

    var item = document.createElement('div');
    item.setAttribute("class", "col-12 col-sm-4 col-md-4 col-lg-3 col-xl-2");
    item.setAttribute("style", "padding-bottom: 20px;");
    item.setAttribute("id", "item_card_porteria_"+porteria.id);
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('items-card-porteria').insertBefore(item, null);
}

function autorizado(porteria) {
    if (porteria.tipo_porteria == 1) {
        var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
        var numeroDia = new Date(dayNow).getDay();
        var diasArray = porteria.dias.split(",");

        if (numeroDia == new Date(porteria.hoy).getDay()) {
            return `<span class="badge badge-sm bg-gradient-success status-autorizado-position">AUTORIZADO</span>`;
        }
        if (diasArray.includes((numeroDia)+"")) {
            return `<span class="badge badge-sm bg-gradient-success status-autorizado-position">AUTORIZADO</span>`;
        }
        return `<span class="badge badge-sm bg-gradient-danger status-autorizado-position">NO AUTORIZADO</span>`;
    }
    return ``;
}

function nombrePorteria(porteria) {
    if (porteria.nombre) {
        return `<p class="text-max-line-2" style="font-size: 12px; color: black; text-align: -webkit-center; font-weight: 600; margin-bottom: 0px;">${porteria.nombre}</p>`
    }
    return ``;
}

function placaVehiculo(porteria) {
    if (porteria.placa) {
        return `<p style="font-size: 12px; color: black; text-align: -webkit-center; font-weight: 600; margin-bottom: 0px;">${porteria.placa}</p>`
    }
    return ``;
}

function diasPermiso(porteria) {
    if (porteria.dias) {
        var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
        var numeroDia = new Date(dayNow).getDay();
        var diasText = '';
        var dias = porteria.dias.split(',');
        dias.forEach(dia => {
            if (diasText) {
                if (numeroDia == dia) diasText+=', <b style="color: #59bded;">'+semanaPorteria[dia]+'</b>';
                else diasText+=', '+semanaPorteria[dia];
                
            } else { 
                if (numeroDia == dia) diasText+= '<b style="color: #59bded;">'+semanaPorteria[dia]+'</b>';
                else diasText+= semanaPorteria[dia];
            }
        });
        return `<p style="font-size: 11px; text-align: -webkit-center; margin-bottom: 0px; margin-top: 5px;">${diasText}</p>`
    }

    if (porteria.hoy) {
        return `<p style="font-size: 11px; text-align: -webkit-center; margin-bottom: 0px; margin-top: 5px;">${porteria.hoy}</p>`;
    }

    return ``;
}

function footerPorteria(porteria) {
    if (eventoPorteria) {
        var lastName = porteria.propietario ? porteria.propietario.lastname ? porteria.propietario.lastname : '' : '';
        return `<div class="modal-footer" style="justify-content: center; padding: 0;">
            <p class="text-max-line-1" style="font-size: 9px; color: #085361; font-weight: bold;">${porteria.propietario.firstname} ${lastName}</p>
        </div>`;
    }

    if (crearPorteria) {
        return `<div class="modal-footer" style="justify-content: center; padding: 0; background-color: #ff0700c2;" onclick="borrarItemPorteria(${porteria.id})">
            <p class="text-max-line-1" style="font-size: 9px; color: white; font-weight: bold;"><i class="fas fa-trash-alt" style="font-size: 11px;"></i>&nbsp;ELIMINAR</p>
        </div>`;
    }
}

function observacionPorteria(porteria) {
    if (porteria.observacion) {
        return `<p class="text-max-line-2" style="margin-top: 5px; font-size: 11px; color: black; text-align: -webkit-center; margin-bottom: 0px;">${porteria.observacion}</p>`
    }
    return ``;
}

function clearFormPorteria() {
    $('#imagen_porteria').val('');
    $('#new_avatar_porteria').hide();
    $('#default_avatar_porteria').show();
    $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');

    $("id_porteria_up").val("");
    $("#tipo_porteria_create").val(1);
    $("#nombre_persona_porteria").val("");
    $("#tipo_vehiculo_porteria").val("");
    $("#tipo_mascota_porteria").val(0);
    $("#placa_persona_porteria").val("");
    $("#observacion_persona_porteria").val("");

    diaPorteria.forEach(dia => {
        $('#'+dia).prop('checked', false);
    });

    $("#input_dias_porteria").show();
    $("#input_tipo_vehiculo_porteria").show();
    $("#input_tipo_mascota_porteria").hide();
}

function clearFormEventoPorteria() {
    $('#imagen_evento').val('');
    $('#imagen_porteria').val('');
    $('#new_avatar_evento').hide();
    $('#default_avatar_evento').show();
    $('#default_avatar_evento').attr('src', '/img/add-imagen.png');

    $comboInmuebleEventos.prop('disabled', false);
    $("#persona_porteria_evento").val("").trigger('change');
    $("#inmueble_porteria_evento").val("").trigger('change');
    $("#fecha_ingreso_porteria_evento").val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2)+'T'+("0" + (dateNow.getHours())).slice(-2)+':'+("0" + (dateNow.getMinutes())).slice(-2));
    $("#fecha_salida_porteria_evento").val("");
    $("#observacion_porteria_evento").val("");
}

function clearEventoPreview() {
    $("#div-porteria-imagen").hide();
    $("#div-porteria-persona").hide();
    $("#div-porteria-inmueble").hide();

    $('#persona_evento_imagen').attr('src', '/img/no-photo.jpg');
    $('#porteria_evento_imagen').attr('src', '/img/no-photo.jpg');

    $("#id_evento_porteria_up").val("");
    $("#observacion_evento_valor").val("");

    $("#fecha_evento_text").text("");
    $("#fecha_evento_valor").text("");
    $("#persona_evento_nombre").text("");
    $("#inmueble_evento_nombre").text("");
}

function hideInputPorteria() {
    $("#input_dias_porteria").hide();
    $("#input_tipo_mascota_porteria").hide();
    $("#input_tipo_vehiculo_porteria").hide();
    $("#input_placa_persona_porteria").hide();
    $("#input_nombre_persona_porteria").hide();
}

function editarItemPorteria(id) {
    if (abrirEditarItemPorteria) {
        $("#loading-card-porteria-"+id).show();
        $.ajax({
            url: base_url + 'porteria-find',
            method: 'GET',
            data: {
                id: id
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            itemPorteria = res.data;
            hideInputPorteria();
            $("#loading-card-porteria-"+id).hide();
            if (itemPorteria.archivos.length) {
                $('#default_avatar_porteria').attr('src', bucketUrl+itemPorteria.archivos[0].url_archivo);
            } else {
                $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');
            }
            
            if(parseInt(itemPorteria.tipo_porteria) == 1) {
                $("#input_dias_porteria").show();
                $("#input_tipo_vehiculo_porteria").show();
                $("#input_placa_persona_porteria").show();
                $("#input_nombre_persona_porteria").show();
            } else if (parseInt(itemPorteria.tipo_porteria) == 2) {
                $("#input_tipo_mascota_porteria").show();
                $("#input_nombre_persona_porteria").show();
            } else if (parseInt(itemPorteria.tipo_porteria) == 3) {
                $("#input_tipo_vehiculo_porteria").show();
                $("#input_placa_persona_porteria").show();
            }
        
            $("#id_porteria_up").val(id);
            $("#tipo_porteria_create").val(itemPorteria.tipo_porteria);
            $("#nombre_persona_porteria").val(itemPorteria.nombre);
            $("#tipo_vehiculo_porteria").val(itemPorteria.tipo_vehiculo);
            $("#tipo_mascota_porteria").val(itemPorteria.tipo_mascota);
            $("#placa_persona_porteria").val(itemPorteria.placa);
            $("#observacion_persona_porteria").val(itemPorteria.observacion);
        
            var diasSeleccionado = itemPorteria.dias.split(",");
        
            diasSeleccionado.forEach(dia => {
                $('#diaPorteria'+dia).prop('checked', true);
            });
            $("#porteriaFormModal").modal('show');
    
        }).fail((err) => {
            $("#loading-card-porteria-"+id).hide();
        });
    } else {
        abrirEditarItemPorteria = true;
    }
}

function borrarItemPorteria(id) {
    abrirEditarItemPorteria = false;
    Swal.fire({
        title: 'Eliminar item?',
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
                url: base_url + 'porteria',
                method: 'DELETE',
                data: JSON.stringify({id: id}),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    loadItemsPorteria();
                    agregarToast('exito', 'Eliminación exitosa', 'Item eliminado con exito!', true );
                } else {
                    agregarToast('error', 'Eliminación errada', res.message);
                }
            }).fail((res) => {
                agregarToast('error', 'Eliminación errada', res.message);
            });
        }
    });
}

function mostrarEventoPorteria (id) {
    clearEventoPreview();
    $.ajax({
        url: base_url + 'porteriaevento-find',
        method: 'GET',
        data: {
            id: id
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            var eventoPersona = res.data;

            $("#id_evento_porteria_up").val(eventoPersona.id);

            if (eventoPersona.archivos.length > 0) {
                $("#div-porteria-imagen").show();
                $("#porteria_evento_imagen").attr('src', bucketUrl+eventoPersona.archivos[0].url_archivo);
            }

            if (eventoPersona.persona) {
                var persona = eventoPersona.persona;
                var nombre = persona.nombre;
                var imgPersona = 'img/no-photo.jpg';
                if (persona.tipo_porteria == 3) nombre = persona.placa;
                if (persona.archivos.length) imgPersona = persona.archivos[0].url_archivo;

                $("#div-porteria-persona").show();
                $("#persona_evento_nombre").text(nombre);
                $("#persona_evento_imagen").attr('src', bucketUrl+imgPersona);
            }

            if (eventoPersona.inmueble) {
                var inmueble = eventoPersona.inmueble;

                $("#div-porteria-inmueble").show();
                $("#inmueble_evento_nombre").text(inmueble.zona.nombre+' - '+inmueble.nombre);
            }
            var textoFecha = "FECHA INGRESO:";
            var valorFecha = eventoPersona.fecha_ingreso;
            if (eventoPersona.fecha_salida) {
                textoFecha = "FECHA SALIDA:";
                valorFecha = eventoPersona.fecha_salida;
            }

            $("#fecha_evento_text").text(textoFecha);
            $("#fecha_evento_valor").text(valorFecha);
            $("#observacion_evento_valor").val(eventoPersona.observacion);
            
        }
        $("#porteriaEventoShowFormModal").modal('show');
    }).fail((err) => {
    });
}

function agregarEventoPorteria (id) {
    $("#loading-card-porteria-"+id).show();
    $.ajax({
        url: base_url + 'porteria-find',
        method: 'GET',
        data: {
            id: id
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        itemPorteria = res.data;
        clearFormEventoPorteria();
        $("#loading-card-porteria-"+id).hide();

        var dataPersona = {
            id: itemPorteria.id,
            text: itemPorteria.nombre
        };

        if (itemPorteria.tipo_porteria == 3) {
            dataPersona = {
                id: itemPorteria.id,
                text: itemPorteria.placa
            };
        }
    
        var newOption = new Option(dataPersona.text, dataPersona.id, false, false);
        $comboPorteriaEventos.append(newOption).trigger('change');
        $comboPorteriaEventos.val(itemPorteria.id).trigger('change');

        if (itemPorteria.inmueble_nit) {
            var dataInmuebles = {
                id: itemPorteria.inmueble_nit.inmueble.id,
                text: itemPorteria.inmueble_nit.inmueble.nombre+' - '+itemPorteria.nit.nombre_completo,
            }
            var newOptionInmueble = new Option(dataInmuebles.text, dataInmuebles.id, false, false);

            $comboInmuebleEventos.append(newOptionInmueble).trigger('change');
            $comboInmuebleEventos.val(dataInmuebles.id).trigger('change');
            $comboInmuebleEventos.prop('disabled', true);
        } else {
            $comboInmuebleEventos.prop('disabled', false);
        }

        $("#porteriaEventoFormModal").modal('show');

    }).fail((err) => {
        $("#loading-card-porteria-"+id).hide();
    });
}

function formatInmueblePorteriaSelection (inmueble) {
    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

function formatInmueblePorteriaCombo (inmueble) {

    if (inmueble.loading) return inmueble.text;

    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

function formatPorteriaCombo (porteria) {
    if (porteria.loading) return porteria.text;

    var urlImagen = porteria.archivos.length > 0 ?
        bucketUrl+porteria.archivos[0].url_archivo :
        bucketUrl+'img/no-photo.jpg';

    var textoPorteria = porteria.tipo_porteria == 3 ? 
        porteria.placa : 
        porteria.text;

    return $(`
        <div class="row">
            <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                <img
                    style="width: 40px; height: 40px; border-radius: 10%; object-fit: cover;"
                    src="${urlImagen}" />
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col-12" style="padding-left: 0px !important">
                        <h6 class="text-max-line-2" style="font-size: 12px; margin-bottom: 0px; color: black; margin-left: 10px;">${textoPorteria}</h6>
                    </div>
                </div>
            </div>
        </div>
    `);
}

function formatPorteriaSelection (producto) {
    return producto.text;
}