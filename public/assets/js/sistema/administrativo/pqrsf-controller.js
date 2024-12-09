let uploadedFilesPqrsf = [];
let limpiarInputFilePqrsf = false;
let swiper = null;
let pqrsf_table = null;
let $comboUsuarioPqrsf = null;

function pqrsfInit() {
    dateNow = new Date();
    var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_pqrsf_filter').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_pqrsf_filter').val(fechaDesde);

    initFilePondPqrsf();
    initCombosPqrsf();
    initTablesPqrsf();
    initFilterPqrsf();

    $('.water').hide();
}

function initFilePondPqrsf() {
    pondPqrsf = FilePond.create(document.querySelector('#pqrsf-files'), {
        allowImagePreview: true,
        imagePreviewUpscale: true,
        allowMultiple: true,
        instantUpload: true,
    });

    $('.filepond--credits').remove();

    pondPqrsf.setOptions({
        server: {
            process: {
                url: '/archivos-cache',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onload: (response) => {
                    const uploadedImagePath = JSON.parse(response);
                    uploadedFilesPqrsf.push({
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
        }
    });

    clearFilesInputPqrsf();
}

function initFilterPqrsf() {
    $(document).on('change', '#fecha_desde_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
    
    $(document).on('change', '#fecha_hasta_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
    
    $(document).on('change', '#id_nit_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
    
    $(document).on('change', '#tipo_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
    
    $(document).on('change', '#area_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
    
    $(document).on('change', '#estado_pqrsf_filter', function () {
        pqrsf_table.ajax.reload();
    });
}

function initCombosPqrsf() {
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

    $('#id_nit_email_filter').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#emailFormModal'),
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

    $('#id_zona_email_filter').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#emailFormModal'),
        delay: 250,
        ajax: {
            url: 'api/zona-combo',
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

    $('#id_nit_pqrsf_filter').select2({
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
        }
    });
}

function initTablesPqrsf() {
    pqrsf_table = $('#pqrsfTable').DataTable({
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
            url: base_url + 'pqrsf',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_pqrsf_filter').val();
                d.fecha_hasta = $('#fecha_hasta_pqrsf_filter').val();
                d.id_nit = $('#id_nit_pqrsf_filter').val();
                d.tipo = $('#tipo_pqrsf_filter').val();
                d.area = $('#area_pqrsf_filter').val();
                d.estado = $('#estado_pqrsf_filter').val();
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
                    return `PETICIONES`;
                }
                if (row.tipo == 1) {
                    return `QUEJA`;
                }
                if (row.tipo == 2) {
                    return `RECLAMO`;
                }
                if (row.tipo == 3) {
                    return `SOLICITUD`;
                }
                if (row.tipo == 4) {
                    return `FELICITACIONES`;
                }
                return `NINGUNO`;
            }},
            {"data": function (row, type, set){
                if (row.area == 1) {
                    return `ADMINISTRACIÓN`;
                }
                if (row.area == 2) {
                    return `SEGURIDAD`;
                }
                if (row.area == 3) {
                    return `ASEO`;
                }
                if (row.area == 4) {
                    return `MANTENIMIENTO`;
                }
                if (row.area == 5) {
                    return `ZONAS COMUNES`;
                }
                return `NINGUNO`;
            }},
            {"data": function (row, type, set){
                if (row.usuario) {
                    return row.usuario.firstname+' '+row.usuario.lastname;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.creador) {
                    return row.creador.firstname+' '+row.creador.lastname;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.apartamentos;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.nombre_completo;
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
            $("#mensaje_pqrsf_nuevo").val("");
            findDataPqrsf(id);
        });
    }

    pqrsf_table.ajax.reload();
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

$(document).on('click', '#generatePqrsfNuevo', function () {
    clearFormPqrsf();
    $("#pqrsfFormModal").modal('show');
});

$(document).on('click', '#generateEmailNuevo', function () {
    clearEmailSender();
    $("#emailFormModal").modal('show');
});

$(document).on('change', '#tipo_pqrsf', function () {
    $("#input_id_usuario_pqrsf").hide();
});

$(document).on('click', '#savePqrsf', function () {
    var form = document.querySelector('#form-pqrsf');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        tipo_pqrsf: $("#tipo_pqrsf").val(),
        area_pqrsf: $("#area_pqrsf").val(),
        id_usuario_pqrsf: $("#id_usuario_pqrsf").val(),
        hora_fin_pqrsf: $("#hora_fin_pqrsf").val(),
        asunto_pqrsf: $("#asunto_pqrsf").val(),
        mensaje_pqrsf: $("#mensaje_pqrsf").val(),
        archivos: uploadedFilesPqrsf
    }

    $("#savePqrsf").hide();
    $("#savePqrsfLoading").show();

    $.ajax({
        url: base_url + 'pqrsf',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPqrsf();

            $("#savePqrsf").show();
            $("#savePqrsfLoading").hide();
            $("#pqrsfFormModal").modal('hide');
            
            pqrsf_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Pqrsf creado con exito!', true);
        }
    }).fail((err) => {
        $('#savePqrsf').show();
        $('#savePqrsfLoading').hide();
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

function clearFormPqrsf () {
    limpiarInputFilePqrsf = true;
    clearFilesInputPqrsf();
    $("#id_usuario_pqrsf").val('').change();
    $("#asunto_pqrsf").val("");
    $("#mensaje_pqrsf").val("");
    $("#asunto_pqrsf").val("");
    $("#area_pqrsf").val(1);
    $("#tipo_pqrsf").val('0').change();
}

function clearFilesInputPqrsf() {
    uploadedFilesPqrsf = [];
    pondPqrsf.off('removefile');
    pondPqrsf.removeFiles();
    pondPqrsf.on('removefile', (error, file) => {
        if (error) {
            console.error('Error al eliminar archivo:', error);
            return;
        }

        const id = file.getMetadata('id');
        const relationType = file.getMetadata('relation_type');

        if (limpiarInputFilePqrsf) {
            limpiarInputFilePqrsf = false;
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

$(document).on('click', '#reloadPqrsf', function () {
    $("#reloadPqrsfIconNormal").hide();
    $("#reloadPqrsfIconLoading").show();
    pqrsf_table.ajax.reload(function (res) {
        $("#reloadPqrsfIconNormal").show();
        $("#reloadPqrsfIconLoading").hide();
    }); 
});