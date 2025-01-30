let uploadedFilesPorteriaNovedades = [];
let $comboInmuebleEventosFilter = null;
let limpiarInputFilePorteria = false;
let $comboNitPorteriaFilter = null;
let $comboNitPorteriaEvento = null;
let $comboInmueblePorteria = null;
let porteria_evento_table = null;
let $comboPorteriaEventos = null;
let $comboInmuebleEventos = null;
let pondPorteriaNovedades = null;
let uploadedFilesPorteria = [];
let searchValuePorteria = null;
let $comboNitPorteria = null;
let porteria_table = null;
let pondPorteria = null;
let searchTimeout;
let diaPorteria = [
    "diaPorteria1",
    "diaPorteria2",
    "diaPorteria3",
    "diaPorteria4",
    "diaPorteria5",
    "diaPorteria6",
    "diaPorteria7"
];
let semanaPorteria = [
    'none',
    'lun',
    'mar',
    'mie',
    'jue',
    'vie',
    'sab',
    'dom'
];
let weekGoalkeeper = [
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
    $('#fecha_desde_porteria_evento_filter').val(fecha);
    $('#fecha_hasta_porteria_evento_filter').val(fecha);

    initFilePondPorteria();
    initTablesPorteria();
    initCombosPorteria();
    initFilterPorteria();
    
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

            var data = res.currentTarget;
            $('#savePorteriaEvento').show();
            $('#savePorteriaEventoLoading').hide();
            
            if (data.responseURL == 'https://maximoph.com/login') {
                caduqueSession();
            }
            if (data.status > 299) {
                agregarToast('error', 'Ha ocurrido un error', 'Error '+data.status);
            }

            var responseData = JSON.parse(res.currentTarget.response);

            if (responseData.success) {
                agregarToast('exito', 'Evento creado', 'Evento creado con exito!', true);
                porteria_table.ajax.reload();
                porteria_evento_table.ajax.reload();
                $("#porteriaEventoFormModal").modal('hide');
            } else {
                agregarToast('error', 'Carga errada', responseData.message);
            }
        };
        xhr.onerror = function (res) {
            agregarToast('error', 'Evento errada', 'Error al crear evento');
            $('#savePorteria').show();
            $('#savePorteriaLoading').hide();
        };
    });
}

function initFilePondPorteria() {
    pondPorteria = FilePond.create(document.querySelector('#porteria-files'), {
        allowImagePreview: true,
        imagePreviewUpscale: true,
        allowMultiple: true,
        instantUpload: true,
    });

    pondPorteriaNovedades = FilePond.create(document.querySelector('#porteria-eventos-files'), {
        allowImagePreview: true,
        imagePreviewUpscale: true,
        allowMultiple: true,
        instantUpload: true,
    });

    $('.filepond--credits').remove();

    pondPorteria.setOptions({
        server: {
            process: {
                url: '/archivos-cache',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onload: (response) => {
                    const uploadedImagePath = JSON.parse(response);
                    uploadedFilesPorteria.push({
                        'id': uploadedImagePath.id,
                        'url': uploadedImagePath.path
                    });
                    return uploadedImagePath.path;
                },
                onerror: (response) => {
                    console.error('Error al subir la imagen: ', response);
                }
            },
            revert: {
                url: '/archivos-cache',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            }
        },
        maxFileSize: '100MB'
    });

    pondPorteria.on('addfile', actualizarEstadoPondPorteria);
    pondPorteria.on('processfile', actualizarEstadoPondPorteria);
    pondPorteria.on('processfileprogress', actualizarEstadoPondPorteria);
    pondPorteria.on('removefile', actualizarEstadoPondPorteria);

    pondPorteriaNovedades.setOptions({
        server: {
            process: {
                url: '/archivos-cache',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onload: (response) => {
                    const uploadedImagePath = JSON.parse(response);
                    uploadedFilesPorteria.push({
                        'id': uploadedImagePath.id,
                        'url': uploadedImagePath.path
                    });
                    return uploadedImagePath.path;
                },
                onerror: (response) => {
                    console.error('Error al subir la imagen: ', response);
                }
            },
            revert: {
                url: '/archivos-cache',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            }
        },
        chunkUploads: false,
        allowMultiple: true,
    });

    pondPorteriaNovedades.on('addfile', actualizarEstadoPondPorteriaNovedad);
    pondPorteriaNovedades.on('processfile', actualizarEstadoPondPorteriaNovedad);
    pondPorteriaNovedades.on('processfileprogress', actualizarEstadoPondPorteriaNovedad);
    pondPorteriaNovedades.on('removefile', actualizarEstadoPondPorteriaNovedad);

    clearFilesInputPorteria();
}

function initTablesPorteria() {
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
                $('td', row).css('background-color', '#cfebcf');
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
                    class="detalle-imagen-porteria"
                    style="height: 50px; width: 50px; border-radius: 10%; cursor: pointer; object-fit: contain;"
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
                        var numeroDia = new Date(dayNow).getDay() + 1;
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
                    
                    var numeroDia = new Date(dayNow).getDay() + 1;
                    var hoyDia = new Date(porteria.hoy).getDay();
                    
                    if (porteria.hoy == dayNow) {
                        return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                    }
                    
                    if (porteria.dias) {
                        var diasArray = porteria.dias.split(",");
                        if (diasArray.includes((numeroDia)+"")) {
                            return `<span class="badge badge-sm bg-gradient-success">AUTORIZADO</span>`;
                        }
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
                    if (row.eventos.length) html+= '<span class="btn disabled badge bg-gradient-success evento-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Confirmado</span>&nbsp;';
                    // if (eventoPorteria && row.eventos.length) html+= '<span id="eventoporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary evento-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Confirmar</span>&nbsp;';
                    if (eventoPorteria && !row.eventos.length) html+= '<span id="eventoporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-dark evento-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Confirmar</span>&nbsp;';
                    if (eventoPorteria && row.eventos.length) html+= '<span id="eventoporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-dark evento-porteria-salida" style="margin-bottom: 0rem !important; min-width: 50px;">Confimar Salida</span>&nbsp;';
                    if (updatePorteria && !row.eventos.length) html+= '<span id="editporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-warning edit-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (deletePorteria && !row.eventos.length) html+= '<span id="deleteporteria_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
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
            url: base_url + 'porteriaevento',
            data: function ( d ) {
                d.tipo = $("#tipo_evento_porteria_filter").val(),
                d.id_inmueble = $("#inmueble_porteria_evento_filter").val(),
                d.fecha_desde = $("#fecha_desde_porteria_evento_filter").val(),
                d.fecha_hasta = $("#fecha_hasta_porteria_evento_filter").val(),
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
                    id="imagendetalle_${row.id}"
                    style="height: 40px; width: 140px; border-radius: 10%; cursor: pointer; object-fit: contain;"
                    class="detalleevento-porteria"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;

            }, className: 'dt-body-center'},
            {"data": function (row, type, set){
                if (row.tipo == 4) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #d000a4;">Visitante</span>';
                }
                if (row.tipo == 5) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #198c51;">Paquete</span>';
                }
                if (row.tipo == 6) {
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #479295; color: white;">Domicilio</span>';
                }
                return '';
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
            {"data":'fecha_creacion'},
            {"data": function (row, type, set){  
                return `<span id="eventodetalle_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success detalleevento-porteria" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>&nbsp;`;
            }},
        ],
    });

    if (porteria_table) {
        //EDITAR PORTERIA
        porteria_table.on('click', '.edit-porteria', function() {
            clearFormPorteria();

            $("#savePorteria").hide();
            $("#updatePorteria").show();
            $("#textPorteriaCreate").hide();
            $("#textPorteriaUpdate").show();
            $("#savePorteriaLoading").hide();
            $("#savePorteriaLoading").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, porteria_table);

            if (data.archivos.length) {
                const archivosExistentes = data.archivos.map((archivo) => ({
                    source: bucketUrl+archivo.url_archivo,
                    options: {
                        type: 'local',
                        metadata: {
                            id: archivo.id,
                            relation_type: archivo.relation_type,
                            base_path: archivo.url_archivo,
                        },
                        file: {
                            name: archivo.url_archivo.split('/').pop(),
                            size: 0,
                            type: archivo.tipo_archivo,
                        },
                    },
                }));
                pondPorteria.addFiles(archivosExistentes);
            }

            if(data.propietario) {
                var dataPropietario = {
                    id: data.propietario.id,
                    text: data.propietario.nombre_completo
                };
                var newOption = new Option(dataPropietario.text, dataPropietario.id, false, false);
                $comboNitPorteria.append(newOption).trigger('change');
                $comboNitPorteria.val(dataPropietario.id).trigger('change');
            }

            if (data.inmueble) {
                var dataInmueble = {
                    id: data.inmueble.id,
                    text: data.inmueble.nombre
                };
                var newOption = new Option(dataInmueble.text, dataInmueble.id, false, false);
                $comboInmueblePorteria.append(newOption).trigger('change');
                $comboInmueblePorteria.val(dataInmueble.id).trigger('change');
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
            
            var tipoVehiculo = data.tipo_vehiculo;
            if (!tipoVehiculo && tipoVehiculo!=0) $("#input_placa_persona_porteria").hide();
            else $("#input_placa_persona_porteria").show();
            $("#id_porteria_up").val(id);
            
            $("#email_porteria").val(data.email);
            $("#genero_porteria").val(data.genero).change();
            $("#telefono_porteria").val(data.telefono);
            $("#fecha_nacimiento_porteria").val(data.fecha_nacimiento);
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
            $("#savePorteria").hide();
            $("#updatePorteria").show();
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
                            agregarToast('exito', 'Eliminación exitosa', 'Porteria eliminada con exito!', true );
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
            var itemPorteria = getDataById(id, porteria_table);

            $("#id_porteria_evento").val(itemPorteria.id);

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

            if (itemPorteria.nit) {
                var dataNit = {
                    id: itemPorteria.nit.id,
                    text: itemPorteria.nit.nombre_completo
                };

                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitPorteriaEvento.append(newOption).trigger('change');
                $comboNitPorteriaEvento.val(itemPorteria.nit.id).trigger('change');
            }

            if (itemPorteria.inmueble) {
                var dataInmueble = {
                    id: itemPorteria.inmueble.id,
                    text: itemPorteria.inmueble.nombre
                };

                var newOption = new Option(dataInmueble.text, dataInmueble.id, false, false);
                $comboInmuebleEventos.append(newOption).trigger('change');
                $comboInmuebleEventos.val(itemPorteria.inmueble.id).trigger('change');
            }

            $("#tipo_evento").val(itemPorteria.tipo_porteria).trigger('change');

            if (itemPorteria.archivos.length) {
                $("#img_porteria_evento").attr("src",bucketUrl + itemPorteria.archivos[0].url_archivo);
            } else {
                $("#img_porteria_evento").attr("src", "/img/no-photo.jpg");
            }

            $("#fecha_ingreso_porteria_evento").val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2)+'T'+("0" + (dateNow.getHours())).slice(-2)+':'+("0" + (dateNow.getMinutes())).slice(-2));

            $('#id_nit_porteria_evento').prop('disabled', true);
            $('#persona_porteria_evento').prop('disabled', true);
            $('#inmueble_porteria_evento').prop('disabled', true);

            $("#porteriaEventoFormModal").modal('show');
        });
        //EVENTO SALIDA PORTERIA
        porteria_table.on('click', '.evento-porteria-salida', function() {
            var id = this.id.split('_')[1];
            var itemPorteria = getDataById(id, porteria_table);

            $("#id_porteria_evento").val(itemPorteria.id);

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

            if (itemPorteria.nit) {
                var dataNit = {
                    id: itemPorteria.nit.id,
                    text: itemPorteria.nit.nombre_completo
                };

                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitPorteriaEvento.append(newOption).trigger('change');
                $comboNitPorteriaEvento.val(itemPorteria.nit.id).trigger('change');
            }

            if (itemPorteria.inmueble) {
                var dataInmueble = {
                    id: itemPorteria.inmueble.id,
                    text: itemPorteria.inmueble.nombre
                };

                var newOption = new Option(dataInmueble.text, dataInmueble.id, false, false);
                $comboInmuebleEventos.append(newOption).trigger('change');
                $comboInmuebleEventos.val(itemPorteria.inmueble.id).trigger('change');
            }

            $("#tipo_evento").val(itemPorteria.tipo_porteria).trigger('change');

            if (itemPorteria.archivos.length) {
                $("#img_porteria_evento").attr("src",bucketUrl + itemPorteria.archivos[0].url_archivo);
            }

            $("#fecha_salida_porteria_evento").val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2)+'T'+("0" + (dateNow.getHours())).slice(-2)+':'+("0" + (dateNow.getMinutes())).slice(-2));

            $('#id_nit_porteria_evento').prop('disabled', true);
            $('#persona_porteria_evento').prop('disabled', true);
            $('#inmueble_porteria_evento').prop('disabled', true);

            $("#porteriaEventoFormModal").modal('show');
        });
        //DETALLE PORTERIA
        porteria_table.on('click', '.detalle-imagen-porteria', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, porteria_table);

            if (data.propietario.logo_nit) {
                $("#preview_header_img_porteria").attr("src",bucketUrl + data.propietario.logo_nit);
            } else if (data.usuario && data.usuario.avatar) {
                $("#preview_header_img_porteria").attr("src",bucketUrl + data.usuario.avatar);
            } else {
                $("#preview_header_img_porteria").attr("src", "/img/no-photo.jpg");
            }

            $("#textPorteriaPreview").text(data.propietario.nombre_completo);
            $("#porteria-preview-ubicacion").text(data.propietario.apartamentos);

            var texto = 'CARRO';
            
            if (data.tipo_porteria == 0) texto = 'PROPIETARIO';
            if (data.tipo_porteria == 1) texto = 'INQUILINO';
            if (data.tipo_porteria == 2) {
                texto = 'CANINO';
                if (data.tipo_mascota == 1) texto = 'FELINO';
                if (data.tipo_mascota == 2) texto = 'OTROS';
            };
            if (data.tipo_porteria == 3) {
                if (data.tipo_vehiculo == 1) texto = 'MOTO';
                if (data.tipo_vehiculo == 2) texto = 'MOTO ELECTRICA';
                if (data.tipo_vehiculo == 2) texto = 'BICICLETA ELECTRICA';
                if (data.tipo_vehiculo == 4) texto = 'OTROS';
                
            }
            if (data.tipo_porteria == 4) texto = 'VISITANTE';
            if (data.tipo_porteria == 5) texto = 'PAQUETE';
            if (data.tipo_porteria == 6) texto = 'DOMICILIO';

            $("#porteria-preview-tipo").text(texto);
            $("#porteria-preview-nombre").text(data.nombre ? data.nombre : data.placa);
            
            if (data.tipo_porteria == 1 || data.tipo_porteria == 2 || data.tipo_porteria == 3) {
                $("#porteria-preview-autorizado").show();
                $("#porteria-preview-noautorizado").hide();
            } else if (data.tipo_porteria == 4 || data.tipo_porteria == 0 || data.tipo_porteria == 5 || data.tipo_porteria == 6) {

                var dayNow = (dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
                var numeroDia = new Date(dayNow).getDay() + 1;
                var hoyDia = new Date(data.hoy).getDay();

                $("#porteria-preview-autorizado").hide();
                $("#porteria-preview-noautorizado").show();
                
                if (numeroDia == hoyDia) {
                    $("#porteria-preview-autorizado").show();
                    $("#porteria-preview-noautorizado").hide();
                }
        
                if (data.dias) {
                    var diasArray = data.dias.split(",");
                    if (diasArray.includes((numeroDia)+"")) {
                        $("#porteria-preview-autorizado").show();
                        $("#porteria-preview-noautorizado").hide();
                    }
                }
            }else {
                $("#porteria-preview-autorizado").hide();
                $("#porteria-preview-noautorizado").show();
            }
            
            if (data.archivos.length) {
                var img = `${bucketUrl}${data.archivos[0].url_archivo}`;
                $("#imagen-porteria-preview").css("background-image", `url("${img}")`);

            } else {
                $("#imagen-porteria-preview").css("background-image", "url(/img/no-photo.jpg)");
            }

            $("#porteriaPreviewModal").modal('show');
        });
    }

    if (porteria_evento_table) {
        porteria_evento_table.on('click', '.detalleevento-porteria', function() {
            var id = this.id.split('_')[1];
            var eventoPorteria = getDataById(id, porteria_evento_table);
            console.log('eventoPorteria: ',eventoPorteria);

            $("#id_evento_porteria_up").val(eventoPorteria.id);

            if (eventoPorteria.archivos.length > 0) {
                $("#div-porteria-imagen").show();
                $("#porteria_evento_imagen").attr('src', bucketUrl+eventoPorteria.archivos[0].url_archivo);
            } else {
                $("#div-porteria-imagen").hide();
            }

            if (eventoPorteria.persona) {
                var persona = eventoPorteria.persona;
                var nombre = persona.nombre;
                var imgPersona = 'img/no-photo.jpg';
                if (persona.tipo_porteria == 3) nombre = persona.placa;
                if (persona.archivos.length) imgPersona = bucketUrl+persona.archivos[0].url_archivo;

                $("#div-porteria-persona").show();
                $("#persona_evento_nombre").text(nombre);
                $("#persona_evento_imagen").attr('src', imgPersona);
            }

            if (eventoPorteria.inmueble) {
                var inmueble = eventoPorteria.inmueble;

                $("#div-porteria-inmueble").show();
                $("#inmueble_evento_nombre").text(inmueble.zona.nombre+' - '+inmueble.nombre);
            }
            var valorFechaIngreso = eventoPorteria.fecha_ingreso;
            var valorFechaSalida = eventoPorteria.fecha_salida;

            if (eventoPorteria.fecha_ingreso) {
                $("#fecha_ingreso_portafolio").text(valorFechaIngreso);
                $("#div-fecha-ingreso-porteria").show();
                $("#div-porteria-fecha-ingreso").hide();
                $("#div-fecha-ingreso-porteria").prop('display', 'flex');
            } else {
                $("#div-porteria-fecha-ingreso").show();
                $("#div-fecha-ingreso-porteria").hide();
            }

            if (eventoPorteria.fecha_salida) {
                $("#fecha_salida_portafolio").text(valorFechaSalida);
                $("#div-fecha-salida-porteria").show();
                $("#div-porteria-fecha-salida").hide();
                $("#div-fecha-salida-porteria").prop('display', 'flex');
            } else {
                $("#div-porteria-fecha-salida").show();
                $("#div-fecha-salida-porteria").hide();
            }

            $("#observacion_evento_valor").val(eventoPorteria.observacion);
            $("#porteriaEventoShowFormModal").modal('show');
        });
    }

    porteria_table.ajax.reload();
}

function initCombosPorteria() {
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

    $comboNitPorteriaEvento = $('#id_nit_porteria_evento').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#porteriaEventoFormModal'),
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
        }
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
            url: 'api/inmueble-combo-normal',
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

    $comboInmueblePorteria = $('#id_inmueble_porteria').select2({
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
            url: 'api/inmueble-combo-normal',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_nit: $('#id_nit_porteria').val()
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            },
        }
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
}

function initFilterPorteria() {
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
    
    $(document).on('change', '#fecha_desde_porteria_evento_filter', function () {
        porteria_evento_table.ajax.reload();
    });

    $(document).on('change', '#fecha_hasta_porteria_evento_filter', function () {
        porteria_evento_table.ajax.reload();
    });
    
    $(document).on('change', '#inmueble_porteria_evento_filter', function () {
        porteria_evento_table.ajax.reload();
    });
}

function clearFilesInputPorteria() {
    uploadedFilesPorteria = [];
    pondPorteria.off('removefile');
    pondPorteria.removeFiles();
    pondPorteria.on('removefile', (error, file) => {
        if (error) {
            console.error('Error al eliminar archivo:', error);
            return;
        }

        const id = file.getMetadata('id');
        const relationType = file.getMetadata('relation_type');

        if (limpiarInputFilePorteria) {
            limpiarInputFilePorteria = false;
            return;
        }

        $.ajax({
            url: base_url + 'archivo-general',
            method: 'DELETE',
            data: JSON.stringify({
                id: id,
                relationType: relationType
            }),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
        }).fail((res) => {
            agregarToast('error', 'Eliminación errada', res.message);
        });
    });

    uploadedFilesPorteriaNovedades = [];
    pondPorteriaNovedades.off('removefile');
    pondPorteriaNovedades.removeFiles();
    pondPorteriaNovedades.on('removefile', (error, file) => {
        if (error) {
            console.error('Error al eliminar archivo:', error);
            return;
        }

        const id = file.getMetadata('id');
        const relationType = file.getMetadata('relation_type');

        if (limpiarInputFilePorteria) {
            limpiarInputFilePorteria = false;
            return;
        }

        $.ajax({
            url: base_url + 'archivo-general',
            method: 'DELETE',
            data: JSON.stringify({
                id: id,
                relationType: relationType
            }),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
        }).fail((res) => {
            agregarToast('error', 'Eliminación errada', res.message);
        });
    });
}

$(document).on('click', '#savePorteria', function () {
    var form = document.querySelector('#form-porteria');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id_nit_porteria: $("#id_nit_porteria").val(),
        id_inmueble_porteria: $("#id_inmueble_porteria").val(),
        tipo_porteria_create: $("#tipo_porteria_create").val(),
        documento_persona_porteria: $("#documento_persona_porteria").val(),
        nombre_persona_porteria: $("#nombre_persona_porteria").val(),
        genero_porteria: $("#genero_porteria").val(),
        tipo_vehiculo_porteria: $("#tipo_vehiculo_porteria").val(),
        placa_persona_porteria: $("#placa_persona_porteria").val(),
        observacion_persona_porteria: $("#observacion_persona_porteria").val(),
        diaPorteria1: $("#diaPorteria1").is(":checked"),
        diaPorteria2: $("#diaPorteria2").is(":checked"),
        diaPorteria3: $("#diaPorteria3").is(":checked"),
        diaPorteria4: $("#diaPorteria4").is(":checked"),
        diaPorteria5: $("#diaPorteria5").is(":checked"),
        diaPorteria6: $("#diaPorteria6").is(":checked"),
        diaPorteria7: $("#diaPorteria7").is(":checked"),
        archivos: uploadedFilesPorteria
    }

    $("#savePorteria").hide();
    $("#savePorteriaLoading").show();

    $.ajax({
        url: base_url + 'porteria',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPorteria();

            $("#savePorteria").show();
            $("#savePorteriaLoading").hide();
            $("#porteriaFormModal").modal('hide');
            
            porteria_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Porteria creado con exito!', true);
        }
    }).fail((err) => {
        $('#savePorteria').show();
        $('#savePorteriaLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updatePorteria', function () {
    var form = document.querySelector('#form-porteria');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id_porteria_up: $("#id_porteria_up").val(),
        id_nit_porteria: $("#id_nit_porteria").val(),
        id_inmueble_porteria: $("#id_inmueble_porteria").val(),
        tipo_porteria_create: $("#tipo_porteria_create").val(),
        documento_persona_porteria: $("#documento_persona_porteria").val(),
        nombre_persona_porteria: $("#nombre_persona_porteria").val(),
        genero_porteria: $("#genero_porteria").val(),
        tipo_vehiculo_porteria: $("#tipo_vehiculo_porteria").val(),
        placa_persona_porteria: $("#placa_persona_porteria").val(),
        observacion_persona_porteria: $("#observacion_persona_porteria").val(),
        diaPorteria1: $("#diaPorteria1").is(":checked"),
        diaPorteria2: $("#diaPorteria2").is(":checked"),
        diaPorteria3: $("#diaPorteria3").is(":checked"),
        diaPorteria4: $("#diaPorteria4").is(":checked"),
        diaPorteria5: $("#diaPorteria5").is(":checked"),
        diaPorteria6: $("#diaPorteria6").is(":checked"),
        diaPorteria7: $("#diaPorteria7").is(":checked"),
        archivos: uploadedFilesPorteria
    }

    $("#updatePorteria").hide();
    $("#savePorteriaLoading").show();

    $.ajax({
        url: base_url + 'porteria',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPorteria();

            $("#updatePorteria").show();
            $("#savePorteriaLoading").hide();
            $("#porteriaFormModal").modal('hide');
            
            porteria_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Porteria actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updatePorteria').show();
        $('#savePorteriaLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#savePorteriaEvento', function () {
    var form = document.querySelector('#form-porteria-evento');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        tipo_evento: $("#tipo_evento").val(),
        id_porteria_evento: $("#id_porteria_evento").val(),
        id_nit_porteria_evento: $("#id_nit_porteria_evento").val(),
        persona_porteria_evento: $("#persona_porteria_evento").val(),
        inmueble_porteria_evento: $("#inmueble_porteria_evento").val(),
        fecha_ingreso_porteria_evento: $("#fecha_ingreso_porteria_evento").val(),
        fecha_salida_porteria_evento: $("#fecha_salida_porteria_evento").val(),
        observacion_porteria_evento: $("#observacion_porteria_evento").val(),
        archivos: uploadedFilesPorteriaNovedades
    }

    $("#savePorteriaEvento").hide();
    $("#savePorteriaEventoLoading").show();

    $.ajax({
        url: base_url + 'porteriaevento',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPorteria();

            $("#savePorteriaEvento").show();
            $("#savePorteriaEventoLoading").hide();
            $("#porteriaEventoFormModal").modal('hide');
            
            porteria_evento_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Porteria creado con exito!', true);
        }
    }).fail((err) => {
        $('#savePorteriaEvento').show();
        $('#savePorteriaEventoLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#generatePorteriaNueva', function () {
    clearFormPorteria();
    $("#savePorteria").show();
    $("#updatePorteria").hide();
    $("#savePorteriaLoading").hide();
    $("#porteriaFormModal").modal('show');
});

$(document).on('click', '#verEventoPorteria', function () {
    var fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $("#tabla-porteria").hide();
    $("#items-tabla-porteria").show();

    $("#reloadPorteria").hide();
    $("#reloadPorteriaEvento").show();

    $("#verEventoPorteria").hide();
    $("#volverEventoPorteria").show();
    $("#generatePorteriaNueva").hide();
    $("#generateEventoPorteria").show();

    $("#tipo_evento_porteria_filter").val('');
    
    $('#fecha_desde_porteria_evento_filter').val(fecha);
    $('#fecha_hasta_porteria_evento_filter').val(fecha);
    $("#inmueble_porteria_evento_filter").val('').change();;
    $("#searchInputPorteriaEvento").val('');
    
    porteria_evento_table.ajax.reload();
});

$(document).on('click', '#volverEventoPorteria', function () {
    $("#tabla-porteria").show();
    $("#items-tabla-porteria").hide();

    $("#reloadPorteria").show();
    $("#reloadPorteriaEvento").hide();

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

var dataImagenes = $('.input-images-porteria').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    // maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

function changeTipoPorteria(tipoPorteria) {
    if(parseInt(tipoPorteria) == 1 || parseInt(tipoPorteria) == 0) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
        $("#input_genero_porteria").show();
        $("#input_fecha_inicio_porteria").show();
        $("#input_telefono_porteria").show();
        $("#input_email_porteria").show();
    } else if (parseInt(tipoPorteria) == 2) {
        $("#input_tipo_mascota_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
        $("#input_dias_porteria").hide();
        $("#input_genero_porteria").hide();
        $("#input_fecha_inicio_porteria").hide();
        $("#input_telefono_porteria").hide();
        $("#input_email_porteria").hide();
        $("#input_tipo_vehiculo_porteria").hide();
    } else if (parseInt(tipoPorteria) == 3) {
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_placa_persona_porteria").show();
        $("#input_dias_porteria").hide();
    } else if (parseInt(tipoPorteria) == 4 || parseInt(tipoPorteria) == 5 || parseInt(tipoPorteria) == 6) {
        $("#input_dias_porteria").show();
        $("#input_tipo_vehiculo_porteria").show();
        $("#input_nombre_persona_porteria").show();
        $("#input_documento_persona_porteria").show();
    }

    if (parseInt(tipoPorteria) == 4) {
        $("#input_genero_porteria").show();
        $("#input_fecha_inicio_porteria").show();
        $("#input_telefono_porteria").show();
        $("#input_email_porteria").show();
    }
}

function clearFormPorteria() {
    limpiarInputFilePorteria = true;
    clearFilesInputPorteria();
    $('#imagen_porteria').val('');
    $('#new_avatar_porteria').hide();
    $('#default_avatar_porteria').show();
    $('#default_avatar_porteria').attr('src', '/img/add-imagen.png');

    $("#textPorteriaCreate").show();
    $("#textPorteriaUpdate").hide();

    $("#id_porteria_up").val("");
    $("#tipo_porteria_create").val(6);
    $("#genero_porteria").val("").change();
    $("#nombre_persona_porteria").val("");
    $("#fecha_nacimiento_porteria").val("");
    $("#telefono_porteria").val("");
    $("#email_porteria").val("");
    $("#documento_persona_porteria").val("");
    $("#tipo_vehiculo_porteria").val("");
    $("#tipo_mascota_porteria").val(0);
    $("#placa_persona_porteria").val("");
    $("#id_nit_porteria").val('').change();
    $('#id_inmueble_porteria').val('').change();

    $('#id_inmueble_porteria').prop('disabled', true);

    $("#observacion_persona_porteria").val("");

    diaPorteria.forEach((dia, index) => {
        var numerDate = new Date().getDay();
        numerDate-=1;
        if (numerDate == index) {
            $('#'+dia).prop('checked', true);
        } else {
            $('#'+dia).prop('checked', false);
        }
    });

    changeTipoPorteria(6);
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
            } else {
                $("#div-porteria-imagen").hide();
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

    $("#input_genero_porteria").hide();
    $("#input_fecha_inicio_porteria").hide();
    $("#input_telefono_porteria").hide();
    $("#input_email_porteria").hide();
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
    $("#fecha_ingreso_porteria_evento").val("");
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

function actualizarEstadoPondPorteria() {
    const algunArchivoCargando = pondPorteria.getFiles().some(file => file.status === 5);
    $("#savePorteria").prop("disabled", !algunArchivoCargando);
}

function actualizarEstadoPondPorteriaNovedad() {
    const algunArchivoCargando = pondPorteriaNovedades.getFiles().some(file => file.status === 5);
    $("#savePorteriaEvento").prop("disabled", !algunArchivoCargando);
}

$(document).on('click', '#reloadPorteria', function () {
    $("#reloadPorteriaIconNormal").hide();
    $("#reloadPorteriaIconLoading").show();
    porteria_table.ajax.reload(function (res) {
        $("#reloadPorteriaIconNormal").show();
        $("#reloadPorteriaIconLoading").hide();
    }); 
});

$(document).on('click', '#reloadPorteriaEvento', function () {
    $("#reloadPorteriaEventoIconNormal").hide();
    $("#reloadPorteriaEventoIconLoading").show();
    porteria_evento_table.ajax.reload(function (res) {
        $("#reloadPorteriaEventoIconNormal").show();
        $("#reloadPorteriaEventoIconLoading").hide();
    }); 
});

$(document).on('change', '#tipo_vehiculo_porteria', function () {
    var tipoVehiculo = $("#tipo_vehiculo_porteria").val();

    if (tipoVehiculo == '') $("#input_placa_persona_porteria").hide();
    else $("#input_placa_persona_porteria").show();
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

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputPorteria").on("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
        porteria_table.ajax.reload();
    }, 300);
});

$("#searchInputPorteriaEvento").on("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
        porteria_evento_table.ajax.reload();
    }, 300);
});

$('#id_nit_porteria').on('change', function(e) {
    var selectedValue = $(this).val();
    if (selectedValue) $('#id_inmueble_porteria').prop('disabled', false);
    else $('#id_inmueble_porteria').prop('disabled', true);
});