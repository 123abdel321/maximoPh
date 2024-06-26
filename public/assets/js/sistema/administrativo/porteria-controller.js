var $comboNitPorteriaFilter = null;
var searchValuePorteria = null;
var $comboNitPorteria = null;
var porteria_table = null;
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

function porteriaInit() {

    porteria_table = $('#porteriaTable').DataTable({
        pageLength: 15,
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
            url: base_url + 'porteria',
            data: function ( d ) {
                d.tipo = $("#tipo_porteria_filter").val(),
                d.fecha = $("#fecha_porteria_filter").val(),
                d.id_nit = $("#id_nit_porteria_filter").val(),
                d.search = $("#searchInputPorteria").val()
            }
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
            {"data":'nombre'},
            {"data": function (row, type, set){
                if (row.tipo_porteria == 1) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">Residente</span>';
                }
                if (row.tipo_porteria == 2) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4cd361;">Mascota</span>';
                }
                if (row.tipo_porteria == 3) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #c0bb12;">Vehiculo</span>';
                }
                if (row.tipo_porteria == 4) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #d000a4;">Visitante</span>';
                }
                return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #e44040;">Propietario</span>';
            }},
            {"data":'placa'},
            {"data":'dias'},
            {"data":'observacion'},
            {"data": function (row, type, set){  
                if (row.propietario) {
                    return row.propietario.nombre_completo;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="editporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deleteporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (porteria_table) {
        //EDITAR PORTERIA
        porteria_table.on('click', '.edit-porteria', function() {
            clearFormPorteria();
            $("#textPorteriaCreate").hide();
            $("#textPorteriaUpdate").show();
            $("#savePorteriaLoading").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, porteria_table);

            if(data.propietario) {
                var dataPropietario = {
                    id: data.propietario.id,
                    text: data.propietario.nombre_completo
                };
                var newOption = new Option(dataPropietario.text, dataPropietario.id, false, false);
                $comboNitPorteria.append(newOption).trigger('change');
                $comboNitPorteria.val(dataPropietario.id).trigger('change');
            }

            if (data.archivos.length) {
                $("#new_avatar_porteria").hide();
                $("#default_avatar_porteria").show();
                $('#default_avatar_porteria').attr('src', bucketUrl+data.archivos[0].url_archivo);
            } else {
                $("#new_avatar_porteria").show();
                $("#default_avatar_porteria").show();
                $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');
            }

            if(parseInt(data.tipoPorteria) == 1) {
                $("#input_tipo_vehiculo_porteria").hide();
                $("#input_nombre_persona_porteria").show();
            } else if (parseInt(data.tipoPorteria) == 2) {
                $("#input_tipo_mascota_porteria").show();
                $("#input_nombre_persona_porteria").show();
            } else if (parseInt(data.tipoPorteria) == 3) {
                $("#input_tipo_vehiculo_porteria").show();
                $("#input_placa_persona_porteria").show();
            } else if (parseInt(data.tipoPorteria) == 4) {
                $("#input_dias_porteria").show();
                $("#input_tipo_vehiculo_porteria").show();
                $("#input_nombre_persona_porteria").show();
            }

            $("#id_porteria_up").val(id);
            $("#tipo_porteria_create").val(data.tipo_porteria);
            $("#nombre_persona_porteria").val(data.nombre);
            $("#tipo_vehiculo_porteria").val(data.tipo_vehiculo);
            $("#tipo_mascota_porteria").val(data.tipo_mascota);
            $("#placa_persona_porteria").val(data.placa);
            $("#observacion_persona_porteria").val(data.observacion);
        
            if (data.dias) {
                var diasSeleccionado = data.dias.split(",");
            
                diasSeleccionado.forEach(dia => {
                    $('#diaPorteria'+dia).prop('checked', true);
                });
            }
            $("#porteriaFormModal").modal('show');
        });
        //ELIMINAR PORTERIA
        porteria_table.on('click', '.drop-porteria', function() {
            var trPorteria = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, porteria_table);

            Swal.fire({
                title: 'Eliminar item de porteria: '+data.nombre+'?',
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
                            porteria_table.row(trPorteria).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Zona eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    $comboNitPorteria = $('#id_nit_porteria').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#porteriaFormModal'),
        delay: 250,
        placeholder: "Seleccione una persona",
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
            url: base_url_erp + 'nit/combo-nit',
            headers: headersERP,
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
        },
        templateResult: formatNitPorteria,
        templateSelection: formatRepoNitPorteria
    });

    $comboNitPorteriaFilter = $('#id_nit_porteria_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una persona",
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
            url: base_url_erp + 'nit/combo-nit',
            headers: headersERP,
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
        },
        templateResult: formatNitPorteria,
        templateSelection: formatRepoNitPorteria
    });

    porteria_table.ajax.reload();
}

$(document).on('click', '#generatePorteriaNueva', function () {
    clearFormPorteria();
    $("#porteriaFormModal").modal('show');
});

function clearFormPorteria() {
    $('#imagen_porteria').val('');
    $('#new_avatar_porteria').hide();
    $('#default_avatar_porteria').show();
    $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');

    $("#id_porteria_up").val("");
    $("#tipo_porteria_create").val(1);
    $("#nombre_persona_porteria").val("");
    $("#tipo_vehiculo_porteria").val("");
    $("#tipo_mascota_porteria").val(0);
    $("#placa_persona_porteria").val("");
    $("#id_nit_porteria").val('').change();

    $("#observacion_persona_porteria").val("");

    diaPorteria.forEach(dia => {
        $('#'+dia).prop('checked', false);
    });
    
    $("#input_dias_porteria").hide();
    $("#input_tipo_vehiculo_porteria").hide();
    $("#input_tipo_mascota_porteria").hide();
    $("#input_placa_persona_porteria").hide();
    $("#input_nombre_persona_porteria").show();
}

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

        if (responseData.success) {
            agregarToast('exito', 'Datos cargados', 'Datos creados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
        }
        
        porteria_table.ajax.reload();
        $("#porteriaFormModal").modal('hide');
    };
    xhr.onerror = function (res) {
        agregarToast('error', 'Carga errada', 'errorsMsg');
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();
    };
});

$(document).on('change', '#tipo_porteria_create', function () {
    var tipoPorteria = $("#tipo_porteria_create").val();
    $("#tipo_vehiculo_porteria").val('');
    console.log('tipoPorteria: ',tipoPorteria);
    hideInputPorteria();
    if(parseInt(tipoPorteria) == 1 || parseInt(tipoPorteria) == 0) {
        $("#input_tipo_vehiculo_porteria").hide();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 4) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_nombre_persona_porteria").show();
    }
});

$(document).on('change', '#tipo_porteria_filter', function () {
    porteria_table.ajax.reload();
});

$(document).on('change', '#id_nit_porteria_filter', function () {
    porteria_table.ajax.reload();
});

$(document).on('change', '#fecha_porteria_filter', function () {
    porteria_table.ajax.reload();
});

function hideInputPorteria() {
    $("#input_dias_porteria").hide();
    $("#input_tipo_mascota_porteria").hide();
    $("#input_tipo_vehiculo_porteria").hide();
    $("#input_placa_persona_porteria").hide();
    $("#input_nombre_persona_porteria").hide();
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

function searchPorteria (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePorteria = $('#searchInputPorteria').val();
    searchValuePorteria = searchValuePorteria+botonPrecionado;
    if(event.key == 'Backspace') searchValuePorteria = searchValuePorteria.slice(0, -1);

    porteria_table.context[0].jqXHR.abort();
    porteria_table.ajax.reload();
}

function formatNitPorteria (nit) {
    if (nit.loading) return nit.text;

    if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
    else return nit.text;
}

function formatRepoNitPorteria (nit) {
    return nit.full_name || nit.text;
}