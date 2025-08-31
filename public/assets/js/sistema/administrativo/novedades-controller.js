let uploadedFilesNovedades = [];
let pondNovedades = null;
let $comboPorteriaNovedades;
let $comboPorteriaNovedadesFiltros;
let novedades_table = null;
let limpiarInputFileNovedades = false; 

function novedadesInit() {
    initFilePondNovedades();
    initCombosNovedades();
    initTablesNovedades();
    initFilterNovedades();
    $('.water').hide();
}

function initFilterNovedades() {
    $(document).on('change', '#id_porteria_novedad_filter', function () {
        novedades_table.ajax.reload();
    });
    $(document).on('change', '#tipo_novedades_filter', function () {
        novedades_table.ajax.reload();
    });
    $(document).on('change', '#area_novedades_filter', function () {
        novedades_table.ajax.reload();
    });
    $(document).on('change', '#fecha_desde_novedades', function () {
        novedades_table.ajax.reload();
    });
    $(document).on('change', '#id_porteria_nofecha_hasta_novedadesvedad_filter', function () {
        novedades_table.ajax.reload();
    });
}

function initFilePondNovedades() {
    pondNovedades = FilePond.create(document.querySelector('#novedades-files'), {
        allowImagePreview: true,
        imagePreviewUpscale: true,
        allowMultiple: true,
        instantUpload: true,
    });

    $('.filepond--credits').remove();

    pondNovedades.setOptions({
        server: {
            process: {
                url: '/archivos-cache',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onload: (response) => {
                    const uploadedImagePath = JSON.parse(response);
                    uploadedFilesNovedades.push({
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
                ondata: (uniqueFileId) => {
                    // Aquí puedes construir los datos que se enviarán
                    return JSON.stringify({ url: uniqueFileId });
                },
            }
        },
        imagePreviewHeight: 150,
        allowImagePreview: true,
        imageCropAspectRatio: '1:1',
    });

    pondNovedades.on('addfile', actualizarEstadoPondNovedad);
    pondNovedades.on('processfile', actualizarEstadoPondNovedad);
    pondNovedades.on('processfileprogress', actualizarEstadoPondNovedad);
    pondNovedades.on('removefile', actualizarEstadoPondNovedad);

    clearFilesInputNovedades();
}

function initTablesNovedades() {
    novedades_table = $('#novedadesTable').DataTable({
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
            url: base_url + 'novedades',
            data: function ( d ) {
                d.id_responsable = $('#id_porteria_novedad_filter').val();
                d.tipo = $('#tipo_novedades_filter').val();
                d.area = $('#area_novedades_filter').val();
                d.fecha_desde = $('#fecha_desde_novedades').val();
                d.fecha_hasta = $('#fecha_hasta_novedades').val();
            }
        },
        columns: [
            {
                "data": null,
                "render": function (data, type, row) {
                    var responable = data.responsable;
                    var imagen = responable.archivos.length ? bucketUrl+responable.archivos[0].url_archivo : bucketUrl+'logos_empresas/no-photo.jpg';
                    var nombrePorteria = responable.tipo_porteria == 3 ? responable.placa : responable.nombre;
                    return `<img
                        id="eventoporteriaimagen_${row.id}"
                        class="detalle-imagen-porteria"
                        style="height: 50px; width: 50px; border-radius: 10%; cursor: pointer; object-fit: contain;"
                        href="javascript:void(0)"
                        src="${imagen}"
                        alt="${nombrePorteria}"
                    />`;
                },
                "orderable": false,
                className: 'dt-body-center'
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var responable = data.responsable;
                    var nombrePorteria = responable.tipo_porteria == 3 ? responable.placa : responable.nombre;
                    return nombrePorteria;
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var responable = data.responsable;
                    if (responable.nit) {
                        return responable.nit.nombre_completo
                    }
                    return '';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var responable = data.responsable;
                    if (responable.inmueble) {
                        var inmueble = responable.inmueble;
                        if (inmueble) {
                            return inmueble.zona.nombre+' - '+inmueble.nombre
                        }
                    }
                    return '';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    if (data.tipo == 1) {
                        return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">MULTA</span>'
                    }
                    return '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4cd361;">NOVEDAD</span>';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var tipoArea = 'ADMINISTRACIÓN';
                    if (data.area == 2) tipoArea = 'SEGURIDAD';
                    if (data.area == 3) tipoArea = 'ASEO';
                    if (data.area == 4) tipoArea = 'MANTENIMIENTO';
                    if (data.area == 5) tipoArea = 'ZONAS COMUNES';
                    return tipoArea;
                }
            },
            {"data":'fecha'},
            {"data":'asunto'},
            {"data": function (row, type, set){
                return `<div  class="text-wrap width-300">${row.mensaje}</div >`;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (row.archivos.length) html+= '<span id="archivosnovedad_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info archivos-novedad" style="margin-bottom: 0rem !important; min-width: 50px;">Ver archivos</span>';
                    return html;
                }
            },
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    let chats = null;
                    if (row.chats.length) chats = row.chats[0];

                    let html = '';
                    if (chats) html+= '<span id="editnovedad_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info read-novedad" style="margin-bottom: 0rem !important; min-width: 50px;">Chat</span>&nbsp;';
                    if (updateNovedades) html+= '<span id="editnovedad_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-novedad" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (deleteNovedades) html+= '<span id="deletenovedad_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-novedad" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (novedades_table) {
        //EDITAR NOVEDAD
        novedades_table.on('click', '.edit-novedad', function() {
            clearFormNovedades();
            $("#textNovedadesCreate").hide();
            $("#textNovedadesUpdate").show();
            $("#saveNovedadesLoading").hide();
            $("#updateNovedades").show();
            $("#saveNovedades").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, novedades_table);

            if(data.responsable) {
                var nombreResponsable = data.responsable.tipo_porteria == 3 ? data.responsable.placa : data.responsable.nombre;
                var dataResponsable = {
                    id: data.responsable.id,
                    text: nombreResponsable
                };
                var newOption = new Option(dataResponsable.text, dataResponsable.id, false, false);
                $comboPorteriaNovedades.append(newOption).trigger('change');
                $comboPorteriaNovedades.val(dataResponsable.id).trigger('change');
            }
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
                pondNovedades.addFiles(archivosExistentes);
            }

            $("#id_novedades_up").val(data.id);
            $("#tipo_novedades").val(data.tipo).change();
            $("#area_novedades").val(data.area).change();
            $("#fecha_novedades").val(data.fecha);
            $("#asunto_novedades").val(data.asunto);
            $("#mensaje_novedades").val(data.mensaje);

            $("#novedadesFormModal").modal('show');
        });
        //BORRAR NOVEDAD
        novedades_table.on('click', '.drop-novedad', function() {
            var trNovedad = $(this).closest('tr');
            var id = this.id.split('_')[1];

            Swal.fire({
                title: 'Eliminar novedad #'+id+' ?',
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
                        url: base_url + 'novedades',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            novedades_table.row(trNovedad).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Novedad eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //ARCHIVOS NOVEDAD
        novedades_table.on('click', '.archivos-novedad', function() {
            const id = this.id.split('_')[1];

            const data = getDataById(id, novedades_table);
            const archivos = data.archivos;
            const container = document.getElementById('novedades-preview-container');
            container.innerHTML = '';

            for (let index = 0; index < archivos.length; index++) {
                const file = archivos[index];
                const tipoArchivo = file.tipo_archivo.split('/')[0];
                if (tipoArchivo == 'image') {
                    container.innerHTML+= `<div class="col-12 col-sm-6 col-md-6"><img src="${bucketUrl+file.url_archivo}" alt="Imagen" style="max-width: 100%; height: auto;"></div><br/><br/>`;
                }
                if (tipoArchivo == 'video') {
                    container.innerHTML+= `<video class="col-12 col-sm-6 col-md-6" src="${bucketUrl+file.url_archivo}" controls style="max-width: 100%; height: auto;"></video><br/><br/>`;
                }
                if (tipoArchivo == 'application') {
                    if (file.url_archivo.endsWith('.xlsx') || file.url_archivo.endsWith('.xls')) {
                        container.innerHTML += `
                            <iframe class="col-12 col-sm-12 col-md-12" src="https://docs.google.com/gview?url=${encodeURIComponent(bucketUrl+file.url_archivo)}&embedded=true" 
                                    style="width: 100%; height: 400px;"></iframe><br/><br/>`;
                    } else {
                        container.innerHTML+= `<iframe src="${bucketUrl+file.url_archivo}" style="width: 100%; height: 400px;"></iframe><br/><br/>`;
                    }
                }
            }

            $("#novedadesPreviewModal").modal('show');
        });
        //CHAT NOVEDAD
        novedades_table.on('click', '.read-novedad', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, novedades_table);
            if (data.chats.length) {
                Livewire.dispatch('cargarMensajes', {chatId: data.chats[0].id, observador: false});
                const chatMaximo = document.getElementById('chatMaximo');
                if (!chatMaximo.classList.contains('show')) document.getElementById('iconNavbarChat').click();
            }
        });
    }
    
    novedades_table.ajax.reload();
}

function initCombosNovedades(){
    $comboPorteriaNovedades = $('#id_porteria_novedad').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#novedadesFormModal'),
        delay: 250,
        placeholder: "Seleccione un responsable",
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
            url: base_url + 'porteria-combo',
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
        },
        templateResult: formatSelect2Novedades,
        templateSelection: formatNovedadesSelection
    });

    $comboPorteriaNovedadesFiltros = $('#id_porteria_novedad_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un responsable",
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
            url: base_url + 'porteria-combo',
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
        },
        templateResult: formatSelect2Novedades,
        templateSelection: formatNovedadesSelection
    });
}

function formatSelect2Novedades (porteria) {
    if (porteria.loading) return porteria.text;
    var urlImagen = porteria.archivos.length ?
        bucketUrl+porteria.archivos[0].url_archivo :
        'logos_empresas/no-photo.jpg';

    var tipoPorteria = '';
    var nombrePorteria = porteria.text;
    if (porteria.tipo_porteria == 1) tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">Residente</span>';
    else if (porteria.tipo_porteria == 2) tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4cd361;">Mascota</span>';
    else if (porteria.tipo_porteria == 3) {
        var textoVehiculo = 'CARRO';
        nombrePorteria = porteria.placa
        if (porteria.tipo_vehiculo == 1) textoVehiculo = 'MOTO';
        else if (porteria.tipo_vehiculo == 2) textoVehiculo = 'MOTO ELECTRICA';
        else if (porteria.tipo_vehiculo == 2) textoVehiculo = 'BICICLETA ELECTRICA';
        else if (porteria.tipo_vehiculo == 4) textoVehiculo = 'OTROS';
        tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #c0bb12;">'+textoVehiculo+'</span>';
    }
    else if (porteria.tipo_porteria == 4) tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #d000a4;">Visitante</span>';
    else if (porteria.tipo_porteria == 5) tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #198c51;">Paquete</span>';
    else if (porteria.tipo_porteria == 6) tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #479295; color: white;">Domicilio</span>';
    else tipoPorteria = '<span  class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #82198c; color: white;">Propietario</span>';

    var $container = $(`
        <div class="row">
            <div class="col-4" style="width: 50px; height: 50px; background-position-x: center; margin-left: 10px; background-origin: content-box; background-size: cover; background-repeat: no-repeat; background-image: url(${urlImagen})">
            </div>
            <div class="col-8" style="padding-left: 0px !important; place-self: center !important;">
                <div class="row" style="text-align: center;">
                    <div class="col-12" style="padding-left: 0px !important">
                        <h6 style="font-size: 12px; margin-bottom: 0px; color: black;">${nombrePorteria}</h6>
                    </div>
                    <div class="col-12" style="padding-left: 0px !important">
                        ${tipoPorteria}
                    </div>
                </div>
            </div>
        </div>
    `);
    
    return $container;
}

function formatNovedadesSelection (porteria) {
    return porteria.full_name || porteria.text;
}

function actualizarEstadoPondNovedad() {
    const algunArchivoCargando = pondNovedades.getFiles().some(file => file.status === 5);
    $("#saveNovedades").prop("disabled", !algunArchivoCargando);
}

$(document).on('click', '#generateNovedadesNueva', function () {
    clearFormNovedades();
    $("#textNovedadesCreate").show();
    $("#textNovedadesUpdate").hide();
    $("#novedadesFormModal").modal('show');
    $("#saveNovedades").show();
    $("#updateNovedades").hide();
    $("#saveNovedadesLoading").hide();
});

function clearFormNovedades() {
    var now = new Date();
    var formattedDate = now.toISOString().slice(0, 16);
    limpiarInputFileNovedades = true;
    clearFilesInputNovedades();
    $("#id_porteria_novedad").val(null);
    $("#area_novedades").val(1).change();
    $("#tipo_novedades").val(1).change();
    $("#fecha_novedades").val(formattedDate);
    $("#asunto_novedades").val(null);
    $("#mensaje_novedades").val(null);
}

function clearFilesInputNovedades() {
    uploadedFilesNovedades = [];
    pondNovedades.off('removefile');
    pondNovedades.removeFiles();
    pondNovedades.on('removefile', (error, file) => {
        if (error) {
            console.error('Error al eliminar archivo:', error);
            return;
        }

        const id = file.getMetadata('id');
        const relationType = file.getMetadata('relation_type');

        if (limpiarInputFileNovedades) {
            limpiarInputFileNovedades = false;
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

$(document).on('click', '#saveNovedades', function () {
    var form = document.querySelector('#novedadesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id_porteria: $("#id_porteria_novedad").val(),
        area: $("#area_novedades").val(),
        tipo: $("#tipo_novedades").val(),
        fecha: $("#fecha_novedades").val(),
        asunto: $("#asunto_novedades").val(),
        mensaje: $("#mensaje_novedades").val(),
        archivos: uploadedFilesNovedades
    }

    $("#saveNovedades").hide();
    $("#saveNovedadesLoading").show();

    $.ajax({
        url: base_url + 'novedades',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormNovedades();
            $("#saveNovedades").show();
            $("#saveNovedadesLoading").hide();
            $("#novedadesFormModal").modal('hide');
            novedades_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Novedad creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveNovedades').show();
        $('#saveNovedadesLoading').hide();
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

$(document).on('click', '#updateNovedades', function () {
    var form = document.querySelector('#novedadesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id: $("#id_novedades_up").val(),
        id_porteria: $("#id_porteria_novedad").val(),
        area: $("#area_novedades").val(),
        tipo: $("#tipo_novedades").val(),
        fecha: $("#fecha_novedades").val(),
        asunto: $("#asunto_novedades").val(),
        mensaje: $("#mensaje_novedades").val(),
        archivos: uploadedFilesNovedades
    }

    $("#updateNovedades").hide();
    $("#saveNovedadesLoading").show();

    $.ajax({
        url: base_url + 'novedades',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormNovedades();
            $("#saveNovedades").show();
            $("#updateNovedades").hide();
            $("#saveNovedadesLoading").hide();
            $("#novedadesFormModal").modal('hide');
            novedades_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Novedad actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateNovedades').show();
        $('#saveNovedadesLoading').hide();
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
});

$(document).on('click', '#reloadNovedades', function () {
    $("#reloadNovedadesIconNormal").hide();
    $("#reloadNovedadesIconLoading").show();
    novedades_table.ajax.reload(function (res) {
        $("#reloadNovedadesIconNormal").show();
        $("#reloadNovedadesIconLoading").hide();
    }); 
});