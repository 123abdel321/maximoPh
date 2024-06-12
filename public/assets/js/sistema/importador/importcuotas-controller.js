var import_cuotas_extras_table = null;
var btnImportCuotasMultas = document.getElementById('actualizarPlantillaCuotasExtras');

function importcuotasInit() {
    import_cuotas_extras_table = $('#importRecibos').DataTable({
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
            url: base_url + 'cuotas-cache-import',
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
            {"data": function (row, type, set){
                if (row.concepto) {
                    return row.concepto.codigo+' '+row.concepto.nombre_concepto;
                }
                return ``;
            }},
            {"data":'nombre_inmueble'},
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {"data":'valor_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'}
        ]
    });

    import_cuotas_extras_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#actualizarPlantillaCuotasExtras').show();
        }
    });
}

$(document).on('click', '#descargarPlantillaCuotasExtras', function () {
    $.ajax({
        url: 'importcuotas-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$("#form-importador-cuotasExtras").submit(function(event) {
    event.preventDefault();

    $('#cargarPlantillaCuotasExtras').hide();
    $('#actualizarPlantillaCuotasExtras').hide();
    $('#cargarPlantillaCuotasExtrasLoagind').show();

    import_cuotas_extras_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-cuotasExtras");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "importcuotas-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaCuotasExtras').show();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();

        if (responseData.success) {
            import_cuotas_extras_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#actualizarPlantillaCuotasExtras').show();
                }
            });
            agregarToast('exito', 'Datos cargados', 'Cuotas & multas cargados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaCuotasExtras').show();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();
    };
    return false;
});

btnImportCuotasMultas.addEventListener('click', event => {
    event.preventDefault();

    $('#cargarPlantillaCuotasExtras').hide();
    $('#actualizarPlantillaCuotasExtras').hide();
    $('#cargarPlantillaCuotasExtrasLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'cuotas-cargar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantillaCuotasExtras').show();
        $('#actualizarPlantillaCuotasExtras').hide();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();
        import_cuotas_extras_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaCuotasExtras').show();
            }
        });
        agregarToast('exito', 'Cuotas extras importadas', 'Recibos importadas con exito!', true);
    }).fail((err) => {
        $('#cargarPlantillaCuotasExtras').show();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();
        import_cuotas_extras_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaCuotasExtras').show();
            }
        });
        agregarToast('error', 'Importación de Cuotas extras errado', '');
    });
});