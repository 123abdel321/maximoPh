var swiper = null;
var pqrsf_table = null;
var $comboUsuarioPqrsf = null
var quill = null;
var diaPqrsf = [
    "diaPqrsf0",
    "diaPqrsf1",
    "diaPqrsf2",
    "diaPqrsf3",
    "diaPqrsf4",
    "diaPqrsf5",
    "diaPqrsf6",
    "diaPqrsf7"
];

function pqrsfInit() {
    var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    dateNow = new Date();

    $('#fecha_desde_pqrsf_filter').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_pqrsf_filter').val(fechaDesde);

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
                return `<span class="badge bg-warning">ACTIVO</span><br/>`;
            }},
            {"data": function (row, type, set){
                if (row.tipo == 0) {
                    return `PREGUNTA`;
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
                if (row.tipo == 5) {
                    return `TAREA`;
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
                    return row.usuario.firstname;
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

    quill = new Quill('#editor', {
        placeholder: 'Redactar texto ...',
        theme: 'snow'
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

var dataImagenes = $('.input-images-pqrsf').imageUploader({
    imagesInputName: 'photos',
    preloadedInputName: 'old',
    maxSize: 2 * 1024 * 1024,
    maxFiles: 10
});

$(document).on('click', '#generatePqrsfNuevo', function () {
    clearFormPqrsf();
    $("#pqrsfFormModal").modal('show');
});

$(document).on('click', '#generateEmailNuevo', function () {
    clearEmailSender();
    $("#emailFormModal").modal('show');
});


$(document).on('click', '#sendEmail', function () {
    var texto = quill.root.innerHTML;

    if (texto == '<p><br></p>') {
        agregarToast('error', 'Email errada', 'el mensaje es obligatorio');
        return;
    }

    let data = {
        id_zona: $("#id_zona_email_filter").val(),
        id_nit: $("#id_nit_email_filter").val(),
        texto: texto
    }

    $.ajax({
        url: base_url + 'pqrsf-email',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            agregarToast('exito', 'Email exitoso', 'Emails enviados con exito!', true);
            $("#emailFormModal").modal('hide');
        }
    }).fail((err) => {
        $('#sendEmail').show();
        $('#sendEmailLoading').hide();
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

function clearEmailSender() {
    quill.deleteText(0, quill.getLength());
}

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
            pqrsf_table.ajax.reload();
        } else {
            agregarToast('error', 'Carga errada', responseData.message);
            pqrsf_table.ajax.reload();
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
    
    $("#id_usuario_pqrsf").val('').change();
    $("#hora_inicio_pqrsf").val("");
    $("#hora_fin_pqrsf").val("");
    $("#asunto_pqrsf").val("");
    $("#mensaje_pqrsf").val("");
    $("#asunto_pqrsf").val("");
    $("#area_pqrsf").val(1);
    
    
    diaPqrsf.forEach(dia => {
        $('#'+dia).prop('checked', false);
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