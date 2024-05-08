var dataNit = null;
var empresas_table = null;

function instalacionempresaInit() {
    empresas_table = $('#empresasTable').DataTable({
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
            url: base_url + 'empresas',
        },
        columns: [
            {"data": 'id',
            render: function (row, type, set){
                var urlImg = `logos_empresas/no-photo.jpg`;
                var nameImg = 'none-img'
                if (row.logo) {
                    urlImg = row.logo;
                    nameImg = row.logo;
                }
                return `<img
                    style="height: 40px; border-radius: 10%; cursor: pointer;"
                    onclick="mostrarEventoPorteria(${row.id})"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;
            }, className: 'dt-body-center'},
            {"data":'razon_social'},
            {"data":'nit'},
            {"data": function (row, type, set){  
                if (row.usuario) {
                    return row.usuario.firstname;
                }
                return '';
            }},
            {"data":'valor_suscripcion_mensual', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    var html = '<span id="editempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    return html;
                }
            },
        ]
    });

    empresas_table.ajax.reload();
}

$(document).on('click', '#generateNuevaEmpresa', function () {
    $("#form-empresa-rut").show();
    $("#form-empresa-create").hide();
    $("#empresaFormModal").modal('show');
});

$(document).on('click', '#omitirEmpresa', function () {
    $("#form-empresa-rut").hide();
    $("#form-empresa-create").show();
});

$(document).on('change', '#file_rut_empresa', function () {
    
    $("#omitirEmpresa").hide();
    $("#omitirEmpresaLoading").show();

    var ajxForm = document.getElementById("form-empresa-rut");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "loadrut");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        
        dataNit = responseData.data;

        if (dataNit.razon_social && dataNit.razon_social != " ") $("#razon_social_empresa_nueva").val(dataNit.razon_social);
        if (dataNit.nombre_completo && dataNit.nombre_completo != " ") $("#nombre_completo_empresa_nueva").val(dataNit.nombre_completo);
        if (dataNit.nit) $("#nit_empresa_nueva").val(dataNit.nit);
        if (dataNit.email) $("#email_empresa_nueva").val(dataNit.email);
        if (dataNit.telefono) $("#telefono_empresa_nueva").val(dataNit.telefono);
        if (dataNit.direccion) $("#direccion_empresa_nueva").val(dataNit.direccion);

        $("#form-empresa-rut").hide();
        $("#form-empresa-create").show();
        $('#omitirEmpresa').show();
        $('#omitirEmpresaLoading').hide();
    };
    xhr.onerror = function (res) {
        var res = JSON.parse(res.currentTarget.response);

        agregarToast('error', 'Carga errada', res.message);
    };
});

$("#form-empresa-create").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-empresa-create');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $('#saveEmpresa').hide();
    $('#saveEmpresaLoading').show();

    var ajxForm = document.getElementById("form-empresa-create");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "instalacionempresa");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        $('#saveEmpresa').show();
        $('#saveEmpresaLoading').hide();

        agregarToast('exito', 'Instalacion completada', 'Instalacion completada con exito!');
        
        $("#empresaFormModal").modal('hide');

        empresas_table.ajax.reload();
    };
    xhr.onerror = function (res) {
        var responseData = JSON.parse(res.currentTarget.response);
        agregarToast('error', 'Carga errada', responseData.message);
    };

});

$("input[data-type='currency']").on({
    keyup: function(event) {
        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
            formatCurrency($(this));
        }
    },
    blur: function() {
        formatCurrency($(this), "blur");
    }
});

function clearFormularioEmpresa() {
    $("#razon_social_empresa_nueva").val("");
    $("#nombre_completo_empresa_nueva").val("");
    $("#nit_empresa_nueva").val("");
    $("#email_empresa_nueva").val("");
    $("#telefono_empresa_nueva").val("");
    $("#direccion_empresa_nueva").val("");
}

function changePrecioSuscripcion() {
    var valorUnidad = stringToNumberFloat($('#valor_unidades').val());
    var numeroUnidad = stringToNumberFloat($('#numero_unidades').val());

    $("#total_mensualidad").val(new Intl.NumberFormat("ja-JP").format(parseFloat(valorUnidad) * parseFloat(numeroUnidad)));
}