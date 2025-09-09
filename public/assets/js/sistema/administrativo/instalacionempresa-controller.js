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
            render: function (row, type, data){
                var urlImg = `logos_empresas/no-photo.jpg`;
                var nameImg = 'none-img'
                if (data.logo) {
                    urlImg = data.logo;
                    nameImg = data.logo;
                }
                return `<img
                    style="height: 40px; border-radius: 10%; cursor: pointer;"
                    onclick="mostrarEventoPorteria(${data.id})"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;
            }, className: 'dt-body-center'},
            {"data":'razon_social'},
            {"data":'nit'},
            {"data": function (row, type, set){  
                return row.correo;
            }},
            {"data": function (row, type, set){  
                if (row.usuario) {
                    return row.usuario.firstname;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.usuario) {
                    return row.usuario.email;
                }
                return '';
            }},
            {"data":'valor_suscripcion_mensual', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    var html = '<span id="editempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="selectempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info select-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Seleccionar</span>&nbsp;';
                    return html;
                }
            },
        ]
    });

    if (empresas_table) {
        empresas_table.on('click', '.edit-empresa', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, empresas_table);

            clearFormularioEditEmpresa();

            if (data.logo) {
                $('#new_avatar_empresa_edit').attr('src', data.logo);
                $('#default_avatar_empresa_edit').hide();
                $('#new_avatar_empresa_edit').show();
            } else {
                $('#default_avatar_empresa_edit').show();
                $('#new_avatar_empresa_edit').hide();
            }

            var numero_unidades = parseFloat(data.numero_unidades) ?? 0;
            var total_mensualidad = parseFloat(data.valor_suscripcion_mensual) ?? 0;
            var valor_unidades = numero_unidades && total_mensualidad ? total_mensualidad / numero_unidades : 0;
            
            $("#id_empresa_up").val(data.id);
            $("#razon_social_empresa_edit").val(data.razon_social);
            $("#nombre_completo_empresa_edit").val(data.nombre);
            $("#nit_empresa_edit").val(data.nit);
            $("#telefono_empresa_edit").val(data.telefono);
            $("#direccion_empresa_edit").val(data.direccion);
            $("#correo_empresa_edit").val(data.correo);
            $("#numero_unidades_edit").val(new Intl.NumberFormat("ja-JP").format(numero_unidades));
            $("#valor_unidades_edit").val(new Intl.NumberFormat("ja-JP").format(valor_unidades));
            $("#total_mensualidad_edit").val(new Intl.NumberFormat("ja-JP").format(total_mensualidad));

            $("#empresaEditFormModal").modal('show');
        });

        empresas_table.on('click', '.select-empresa', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, empresas_table);
            console.log('data selected: ',data);
            
            $.ajax({
                url: base_url + 'select-empresa',
                method: 'POST',
                data: JSON.stringify({
                    id_empresa: data.id
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    localStorage.setItem("token_db_portafolio", res.token_db_portafolio);
                    localStorage.setItem("auth_token_erp", res.token_api_portafolio);
                    localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                    localStorage.setItem("notificacion_code", res.notificacion_code);
                    localStorage.setItem("notificacion_code_general", res.notificacion_code_general);
                    localStorage.setItem("fondo_sistema", res.fondo_sistema);
                    localStorage.setItem("empresa_logo", res.empresa.logo);                    

                    var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                    if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
                    } else {
                        localStorage.setItem("item_active_menu", 'contabilidad');
                    }

                    agregarToast('exito', 'Empresa seleccionada', 'Empresa seleccionada con exito!', true);
                    location.reload();
                }
            }).fail((err) => {
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
    }

    empresas_table.ajax.reload();

    document.getElementById('razon_social_empresa_nueva').removeEventListener('input', soloLetras);
    document.getElementById('razon_social_empresa_nueva').addEventListener('input', soloLetras);
}

$(document).on('click', '#generateNuevaEmpresa', function () {
    $("#form-empresa-rut").show();
    $("#form-empresa-create").hide();
    $("#empresaFormModal").modal('show');

    clearFormularioEmpresa();
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

$("#form-empresa-update").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-empresa-update');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $('#updateEmpresa').hide();
    $('#updateEmpresaLoading').show();

    var ajxForm = document.getElementById("form-empresa-update");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "actualizarempresa");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        $('#updateEmpresa').show();
        $('#updateEmpresaLoading').hide();

        agregarToast('exito', 'Actualización completada', 'Actualización completada con exito!');
        
        $("#empresaEditFormModal").modal('hide');

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
    $("#nit_empresa_nueva").val("");
    $("#email_empresa_nueva").val("");
    $("#telefono_empresa_nueva").val("");
    $("#direccion_empresa_nueva").val("");

    $('#new_avatar_empresa_nueva').hide();
    $('#default_avatar_empresa_nueva').show();
}

function clearFormularioEditEmpresa() {
    $("#id_empresa_up").val("");
    $("#razon_social_empresa_edit").val("");
    $("#nit_empresa_edit").val("");
    $("#email_empresa_edit").val("");
    $("#telefono_empresa_edit").val("");
    $("#direccion_empresa_edit").val("");

    $('#new_avatar_empresa_edit').hide();
    $('#default_avatar_empresa_edit').show();
}

function changePrecioSuscripcion() {
    var valorUnidad = stringToNumberFloat($('#valor_unidades').val());
    var numeroUnidad = stringToNumberFloat($('#numero_unidades').val());

    $("#total_mensualidad").val(new Intl.NumberFormat("ja-JP").format(parseFloat(valorUnidad) * parseFloat(numeroUnidad)));
}

function changePrecioSuscripcionEdit() {
    var valorUnidad = stringToNumberFloat($('#valor_unidades_edit').val());
    var numeroUnidad = stringToNumberFloat($('#numero_unidades_edit').val());

    $("#total_mensualidad_edit").val(new Intl.NumberFormat("ja-JP").format(parseFloat(valorUnidad) * parseFloat(numeroUnidad)));
}

function readURLEmpresaNueva(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_empresa_nueva').attr('src', e.target.result);
            $('#new_avatar_empresa').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_empresa').hide();
        $('#new_avatar_empresa').show();
    }
}

function readURLEmpresaEdit(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_empresa_edit').attr('src', e.target.result);
            $('#new_avatar_empresa_edit').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_empresa_edit').hide();
        $('#new_avatar_empresa_edit').show();
    }
}