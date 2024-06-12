var import_recibos_table = null;
var btnImportRecibo = document.getElementById('actualizarPlantillaRecibos');

function importrecibosInit() {
    import_recibos_table = $('#importRecibos').DataTable({
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
            url: base_url + 'recibos-cache-import',
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
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'fecha_manual'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'pago', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'saldo_nuevo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'anticipos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'}
        ]
    });

    import_recibos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            totalesRecibosImport();
        }
    });
}

function totalesRecibosImport() {
    $.ajax({
        url: base_url + 'recibos-totales-import',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        if (res.data.errores + res.data.buenos > 0) {
            $('#totales_import_recibos').show();
        } else {
            $('#totales_import_recibos').hide();
        }
        if (res.data.errores <= 0 &&  res.data.buenos > 0) {
            $('#actualizarPlantillaRecibos').show();
        }

        var countA = new CountUp('errores_recibos_import', 0, res.data.errores);
            countA.start();

        var countB = new CountUp('buenos_recibos_import', 0, res.data.buenos);
            countB.start();

        var countC = new CountUp('pagos_recibos_import', 0, res.data.pagos);
            countC.start();

        var countD = new CountUp('anticipos_recibos_import', 0, res.data.anticipos);
            countD.start();

    }).fail((err) => {
    });
}

$(document).on('click', '#descargarPlantillaRecibos', function () {
    $.ajax({
        url: 'importrecibos-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$("#form-importador-recibos").submit(function(event) {
    event.preventDefault();

    $('#cargarPlantillaRecibos').hide();
    $('#actualizarPlantillaRecibos').hide();
    $('#cargarPlantillaRecibosLoagind').show();

    import_recibos_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-recibos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "importrecibos-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaRecibos').show();
        $('#cargarPlantillaRecibosLoagind').hide();

        if (responseData.success) {
            import_recibos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesRecibosImport();
                }
            });
            agregarToast('exito', 'Datos cargados', 'Recibos cargados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaRecibos').hide();
        $('#cargarPlantillaRecibosLoagind').show();
    };
    return false;
});

btnImportRecibo.addEventListener('click', event => {
    event.preventDefault();

    $('#cargarPlantillaRecibos').hide();
    $('#actualizarPlantillaRecibos').hide();
    $('#cargarPlantillaRecibosLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'recibos-cargar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantillaRecibos').show();
        $('#actualizarPlantillaRecibos').hide();
        $('#cargarPlantillaRecibosLoagind').hide();
        import_recibos_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                totalesRecibosImport();
            }
        });
        agregarToast('exito', 'Recibos importadas', 'Recibos importadas con exito!', true);
    }).fail((err) => {
        $('#cargarPlantillaRecibos').show();
        $('#cargarPlantillaRecibosLoagind').hide();
        import_recibos_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                totalesRecibosImport();
            }
        });
        agregarToast('error', 'Importación de Recibos errado', '');
    });
});