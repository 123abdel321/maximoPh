var swiper = null;
var pqrsf_table = null;
var $comboUsuarioPqrsf = null;
var mostrarAgregarImagenes = false;

function pqrsfInit() {
    dateNow = new Date();
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