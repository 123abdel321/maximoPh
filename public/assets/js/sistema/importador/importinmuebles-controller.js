var import_inmuebles_table = null;
var btnImportRecibo = document.getElementById('actualizarPlantillaInmuebles');

function importinmueblesInit() {
    import_inmuebles_table = $('#importInmuebles').DataTable({
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
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'inmuebles-cache-import',
        },
        'rowCallback': function(row, data, index){
            if (parseInt(data.estado)) {
                $('td', row).css('background-color', '#ffe5e5');
                return;
            }
        },
        columns: [
            {"data":'id', visible: false},
            {"data": function (row, type, set){
                if (!row.estado) {
                    return `<i class="fas fa-check-circle" style="color: #03b403; font-size: 14px;"></i>&nbsp;${row.id}`;
                }
                return `<i class="fas fa-minus-circle" style="color: red; font-size: 14px;"></i>&nbsp;${row.id}`;
            }},
            {"data":'nombre_inmueble'},
            {"data":'nombre_zona'},
            {"data":'area'},
            {"data":'coheficiente'},
            {"data":'nombre_concepto_facturacion'},
            {"data":'porcentaje_aumento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'valor_aumento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'tipo_nit'},
            {"data":'porcentaje_administracion'},
            {"data":'valor_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'}
        ]
    });

    import_inmuebles_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            totalesInmueblesImport();
        }
    });
}

function totalesInmueblesImport() {
    $.ajax({
        url: base_url + 'inmuebles-totales-import',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        if (res.data.errores + res.data.buenos > 0) {
            $('#totales_import_inmuebles').show();
        } else {
            $('#totales_import_inmuebles').hide();
        }
        if (res.data.errores <= 0 &&  res.data.buenos > 0) {
            $('#actualizarPlantillaInmuebles').show();
        }

        var countA = new CountUp('errores_inmuebles_import', 0, res.data.errores);
            countA.start();

        var countB = new CountUp('buenos_inmuebles_import', 0, res.data.buenos);
            countB.start();

        var countC = new CountUp('valores_inmuebles_import', 0, res.data.valores);
            countC.start();

    }).fail((err) => {
    });
}

$(document).on('click', '#descargarPlantillaInmuebles', function () {
    $.ajax({
        url: 'importinmuebles-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$("#form-importador-inmuebles").submit(function(event) {
    event.preventDefault();

    $('#cargarPlantillaInmuebles').hide();
    $('#actualizarPlantillaInmuebles').hide();
    $('#cargarPlantillaInmueblesLoagind').show();

    import_inmuebles_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-inmuebles");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "importinmuebles-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaInmuebles').show();
        $('#cargarPlantillaInmueblesLoagind').hide();

        if (responseData.success) {
            import_inmuebles_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesInmueblesImport();
                }
            });
            agregarToast('exito', 'Datos cargados', 'Inmuebles cargados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaInmuebles').hide();
        $('#cargarPlantillaInmueblesLoagind').show();
    };
    return false;
});

btnImportRecibo.addEventListener('click', event => {
    event.preventDefault();

    $('#cargarPlantillaInmuebles').hide();
    $('#actualizarPlantillaInmuebles').hide();
    $('#cargarPlantillaInmueblesLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'inmuebles-cargar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantillaInmuebles').show();
        $('#actualizarPlantillaInmuebles').hide();
        $('#cargarPlantillaInmueblesLoagind').hide();
        import_inmuebles_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                totalesInmueblesImport();
            }
        });
        agregarToast('exito', 'Inmuebles importadas', 'Inmuebles importadas con exito!', true);
    }).fail((err) => {

        $('#cargarPlantillaInmuebles').show();
        $('#cargarPlantillaInmueblesLoagind').hide();
        import_inmuebles_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                totalesInmueblesImport();
            }
        });
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        if (typeof mensaje === 'object') {
            for (field in mensaje) {

                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
            };
            agregarToast('error', 'Actuailización errada', errorsMsg);
        } else {
            agregarToast('error', 'Actuailización errada', mensaje);
        }
    });
});