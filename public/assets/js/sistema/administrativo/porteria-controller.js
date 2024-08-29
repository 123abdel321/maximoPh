var $comboInmuebleEventosFilter = null;
var $comboNitPorteriaFilter = null;
var porteria_evento_table = null;
var $comboPorteriaEventos = null;
var $comboInmuebleEventos = null;
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

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_porteria_filter').val(fecha);
    $('#fecha_porteria_evento_filter').val(fecha);

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
        'rowCallback': function(row, data, index){
            if (data.eventos.length) {
                $('td', row).css('background-color', '#41b14140');
                return;
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
                    id="eventoporteriaimagen_${row.id}"
                    class="evento-porteria"
                    style="height: 40px; border-radius: 10%; cursor: pointer;"
                    href="javascript:void(0)"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;

            }, className: 'dt-body-center'},
            {"data":'nombre'},
            {"data":'documento'},
            {"data": function (row, type, set){
                if (row.tipo_porteria == 1) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">Residente</span>';
                }
                if (row.tipo_porteria == 2) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4cd361;">Mascota</span>';
                }
                if (row.tipo_porteria == 3) {
                    var texto = 'CARRO';
                    if (row.tipo_vehiculo == 1) texto = 'MOTO';
                    if (row.tipo_vehiculo == 2) texto = 'MOTO ELECTRICA';
                    if (row.tipo_vehiculo == 2) texto = 'BICICLETA ELECTRICA';
                    if (row.tipo_vehiculo == 4) texto = 'OTROS';
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #c0bb12;">'+texto+'</span>';
                }
                if (row.tipo_porteria == 4) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #d000a4;">Visitante</span>';
                }
                if (row.tipo_porteria == 5) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #198c51;">Paquete</span>';
                }
                if (row.tipo_porteria == 6) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #479295; color: white;">Domicilio</span>';
                }
                return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #82198c; color: white;">Propietario</span>';
            }},
            {"data":'placa'},
            {"data": function (row, type, set){  
                const porteria = row;
                
                if (porteria.tipo_porteria == 4 || porteria.tipo_porteria == 0 || porteria.tipo_porteria == 5 || porteria.tipo_porteria == 6) {
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
                }
            
                return ``;
            }},
            {"data": function (row, type, set){  
                const porteria = row;
                if (porteria.tipo_porteria == 1 || porteria.tipo_porteria == 3) {
                    return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                }
                if (porteria.tipo_porteria == 4 || porteria.tipo_porteria == 0 || porteria.tipo_porteria == 5 || porteria.tipo_porteria == 6) {
                    var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
                    var numeroDia = new Date(dayNow).getDay();
            
                    if (porteria.dias) {
                        var diasArray = porteria.dias.split(",");
                        if (diasArray.includes((numeroDia)+"")) {
                            return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                        }
                    }
                    if (porteria.hoy && numeroDia == new Date(porteria.hoy).getDay()) {
                        return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                    }
                    return `<span class="badge badge-sm bg-gradient-danger">NO AUTORIZADO</span>`;
                }
                return ``;
            }},
            {"data":'observacion'},
            {"data": function (row, type, set){  
                if (row.propietario) {
                    return row.propietario.nombre_completo;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.propietario) {
                    return row.propietario.apartamentos;
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
                    if (eventoPorteria) html+= '<span id="eventoporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-dark evento-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Confirmar</span>&nbsp;';
                    if (usuario_rol != 4) html+= '<span id="editporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (usuario_rol != 4) html+= '<span id="deleteporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

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
            data: function ( d ) {
                d.tipo = $("#tipo_evento_porteria_filter").val(),
                d.id_inmueble = $("#inmueble_porteria_evento_filter").val(),
                d.fecha = $("#fecha_porteria_evento_filter").val(),
                d.search = $("#searchInputPorteriaEvento").val()
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
            {"data": function (row, type, set){
                if (row.tipo == '1') {
                    return 'Paquete';
                }
                if (row.tipo == '2') {
                    return 'Minuta';
                }
                if (row.tipo == '3') {
                    return 'Paquete';
                }
                if (row.tipo == '4') {
                    return 'Otros';
                }
                return 'Visita';
            }},
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
                $("#new_avatar_porteria").hide();
                $("#default_avatar_porteria").show();
                $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');
            }

            var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
            var numeroDia = new Date(dayNow).getDay();
            if (data.hoy && numeroDia == new Date(data.hoy).getDay()) {
                $('#diaPorteria0').prop('checked', true);
            }
            
            changeTipoPorteria(data.tipo_porteria);
            
            console.log('data.tipo_vehiculo', data);
            var tipoVehiculo = data.tipo_vehiculo;
            if (!tipoVehiculo && tipoVehiculo!=0) $("#input_placa_persona_porteria").hide();
            else $("#input_placa_persona_porteria").show();

            $("#id_porteria_up").val(id);
            $("#tipo_porteria_create").val(data.tipo_porteria);
            $("#nombre_persona_porteria").val(data.nombre);
            $("#documento_persona_porteria").val(data.documento);
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
        //EVENTO PORTERIA
        porteria_table.on('click', '.evento-porteria', function() {
            var id = this.id.split('_')[1];
            
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
                agregarToast('error', 'Error al cargar evento', errorsMsg);
            });

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
        }
    });

    $comboNitPorteriaFilter = $('#id_nit_porteria_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una persona",
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

    $comboInmuebleEventosFilter = $('#inmueble_porteria_evento_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un inmueble",
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

    porteria_table.ajax.reload();

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
    
            porteria_table.ajax.reload();
            porteria_evento_table.ajax.reload();
            
            //AGREGAR VER EVENTOS
            agregarToast('exito', 'Evento creado', 'Evento creado con exito!', true);
    
            $("#porteriaEventoFormModal").modal('hide');
        };
        xhr.onerror = function (res) {
            agregarToast('error', 'Evento errada', 'Error al crear evento');
            $('#savePorteria').show();
            $('#savePorteriaLoading').hide();
        };
    });
}

$(document).on('click', '#generatePorteriaNueva', function () {
    clearFormPorteria();
    $("#porteriaFormModal").modal('show');
});

$(document).on('click', '#verEventoPorteria', function () {
    var fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $("#tabla-porteria").hide();
    $("#items-tabla-porteria").show();

    $("#verEventoPorteria").hide();
    $("#volverEventoPorteria").show();
    $("#generatePorteriaNueva").hide();
    $("#generateEventoPorteria").show();

    $("#tipo_evento_porteria_filter").val('');
    
    $('#fecha_porteria_evento_filter').val(fecha);
    $("#inmueble_porteria_evento_filter").val('').change();;
    $("#searchInputPorteriaEvento").val('');
    
    porteria_evento_table.ajax.reload();
});

$(document).on('click', '#volverEventoPorteria', function () {
    $("#tabla-porteria").show();
    $("#items-tabla-porteria").hide();
    
    $("#verEventoPorteria").show();
    $("#volverEventoPorteria").hide();
    $("#generatePorteriaNueva").show();
    $("#generateEventoPorteria").hide();

    porteria_table.ajax.reload();
});

$(document).on('change', '#tipo_porteria_create', function () {
    var tipoPorteria = $("#tipo_porteria_create").val();
    hideInputPorteria();
    changeTipoPorteria(tipoPorteria);
});

function changeTipoPorteria(tipoPorteria) {
    if(parseInt(tipoPorteria) == 1 || parseInt(tipoPorteria) == 0) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
    } else if (parseInt(tipoPorteria) == 4 || parseInt(tipoPorteria) == 5 || parseInt(tipoPorteria) == 6) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
    }
}

$(document).on('change', '#tipo_vehiculo_porteria', function () {
    var tipoVehiculo = $("#tipo_vehiculo_porteria").val();

    if (tipoVehiculo == '') $("#input_placa_persona_porteria").hide();
    else $("#input_placa_persona_porteria").show();
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

$(document).on('change', '#tipo_evento_porteria_filter', function () {
    porteria_evento_table.ajax.reload();
});

$(document).on('change', '#fecha_porteria_evento_filter', function () {
    porteria_evento_table.ajax.reload();
});

$(document).on('change', '#inmueble_porteria_evento_filter', function () {
    porteria_evento_table.ajax.reload();
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
        fecha_ingreso: $("#fecha_ingreso_evento_valor").val(),
        fecha_salida: $("#fecha_salida_evento_valor").val(),
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

function clearFormPorteria() {
    $('#imagen_porteria').val('');
    $('#new_avatar_porteria').hide();
    $('#default_avatar_porteria').show();
    $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');

    $("#textPorteriaCreate").show();
    $("#textPorteriaUpdate").hide();

    $("#id_porteria_up").val("");
    $("#tipo_porteria_create").val(1);
    $("#nombre_persona_porteria").val("");
    $("#documento_persona_porteria").val("");
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
    $("#input_documento_persona_porteria").show();
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
                if (persona.archivos.length) imgPersona = bucketUrl+persona.archivos[0].url_archivo;

                $("#div-porteria-persona").show();
                $("#persona_evento_nombre").text(nombre);
                $("#persona_evento_imagen").attr('src', imgPersona);
            }

            if (eventoPersona.inmueble) {
                var inmueble = eventoPersona.inmueble;

                $("#div-porteria-inmueble").show();
                $("#inmueble_evento_nombre").text(inmueble.zona.nombre+' - '+inmueble.nombre);
            }
            var valorFechaIngreso = eventoPersona.fecha_ingreso;
            var valorFechaSalida = eventoPersona.fecha_salida;

            if (eventoPersona.fecha_ingreso) {
                $("#fecha_ingreso_portafolio").text(valorFechaIngreso);
                $("#div-fecha-ingreso-porteria").show();
                $("#div-porteria-fecha-ingreso").hide();
                $("#div-fecha-ingreso-porteria").prop('display', 'flex');
            } else {
                $("#div-porteria-fecha-ingreso").show();
                $("#div-fecha-ingreso-porteria").hide();
            }

            if (eventoPersona.fecha_salida) {
                $("#fecha_salida_portafolio").text(valorFechaSalida);
                $("#div-fecha-salida-porteria").show();
                $("#div-porteria-fecha-salida").hide();
                $("#div-fecha-salida-porteria").prop('display', 'flex');
            } else {
                $("#div-porteria-fecha-salida").show();
                $("#div-fecha-salida-porteria").hide();
            }

            $("#observacion_evento_valor").val(eventoPersona.observacion);
        }
        $("#porteriaEventoShowFormModal").modal('show');
    }).fail((err) => {
    });
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
    $("#input_documento_persona_porteria").hide();
}

function clearFormEventoPorteria() {
    $('#imagen_evento').val('');
    $('#imagen_porteria').val('');
    
    $('#new_avatar_evento').hide();
    $('#default_avatar_evento').show();
    $('#default_avatar_evento').attr('src', '/img/add-imagen.png');

    // $comboInmuebleEventos.prop('disabled', false);
    $("#tipo_evento").val(0).trigger('change');
    $("#persona_porteria_evento").val("").trigger('change');
    $("#inmueble_porteria_evento").val("").trigger('change');
    $("#fecha_ingreso_porteria_evento").val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2)+'T'+("0" + (dateNow.getHours())).slice(-2)+':'+("0" + (dateNow.getMinutes())).slice(-2));
    $("#fecha_salida_porteria_evento").val("");
    $("#observacion_porteria_evento").val("");
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

function searchPorteriaEvento (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePorteria = $('#searchInputPorteriaEvento').val();
    searchValuePorteria = searchValuePorteria+botonPrecionado;
    if(event.key == 'Backspace') searchValuePorteria = searchValuePorteria.slice(0, -1);

    porteria_evento_table.context[0].jqXHR.abort();
    porteria_evento_table.ajax.reload();
} 

function formatNitPorteria (nit) {
    if (nit.loading) return nit.text;

    if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
    else return nit.text;
}

function formatRepoNitPorteria (nit) {
    return nit.full_name || nit.text;
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