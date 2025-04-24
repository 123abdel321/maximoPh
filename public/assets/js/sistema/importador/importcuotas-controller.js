var import_cuotas_extras_table = null;
var channelImportadorCuotas = pusher.subscribe('importador-cuotas-'+localStorage.getItem("notificacion_code"));

function importcuotasInit() {
    import_cuotas_extras_table = $('#importCuotasMultas').DataTable({
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
            totalesCuotasMultasImport();
        }
    });

    var btnImportCuotasMultas = document.getElementById('actualizarPlantillaCuotasExtras');
    btnImportCuotasMultas.removeEventListener('click', handleCuotasClick);
    btnImportCuotasMultas.addEventListener('click', handleCuotasClick);

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
            console.log('res: ',res);
            var data = res.currentTarget;
            if (data.responseURL == 'https://maximoph.co/login') {
                caduqueSession();
            }
            if (data.status > 299) {
                agregarToast('error', 'Ha ocurrido un error', 'Error '+data.status);
            }
            var responseData = JSON.parse(res.currentTarget.response);

            if (responseData.success) {
                agregarToast('info', 'Cargando cuotas extras & multas', 'Se le notificará cuando la importación haya terminado!', true);
            } else {
                $('#cargarPlantillaCuotasExtras').show();
                $('#cargarPlantillaCuotasExtrasLoagind').hide();
                agregarToast('error', 'Carga errada', 'errorsMsg');
            }
        };
        xhr.onerror = function (res) {
            $('#cargarPlantillaCuotasExtras').show();
            $('#cargarPlantillaCuotasExtrasLoagind').hide();
        };
        return false;
    });
}

channelImportadorCuotas.bind('notificaciones', function(data) {

    if (data.success) {
        $('#cargarPlantillaCuotasExtrasLoagind').hide();

        if (data.accion == 1) {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
            $('#cargarPlantillaCuotasExtras').show();
            $('#actualizarPlantillaCuotasExtras').show();
            import_cuotas_extras_table.ajax.reload(function(res) {
                if (res.success) {
                    totalesCuotasMultasImport();
                }
            });
        }

        if (data.accion == 2) {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
            $('#cargarPlantillaCuotasExtras').show();
            import_cuotas_extras_table.ajax.reload(function(res) {
                if (res.success) {
                    totalesCuotasMultasImport();
                }
            });
        }
        
    } else {
        $('#cargarPlantillaCuotasExtras').show();
        $('#actualizarPlantillaCuotasExtras').hide();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    }
});

function handleCuotasClick() {
    $('#cargarPlantillaCuotasExtras').hide();
    $('#actualizarPlantillaCuotasExtras').hide();
    $('#cargarPlantillaCuotasExtrasLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'cuotas-cargar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        agregarToast('info', 'Importando cuotas extras & multas', 'Se le notificará cuando la importación haya terminado!', true);
    }).fail((err) => {
        $('#cargarPlantillaCuotasExtras').show();
        $('#cargarPlantillaCuotasExtrasLoagind').hide();
        
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
        agregarToast('error', 'Importación errada', errorsMsg);
    });
}

function totalesCuotasMultasImport() {
    $.ajax({
        url: base_url + 'cuotas-totales-import',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        if (res.data.errores + res.data.buenos > 0) {
            $('#totales_import_cuuotas_multas').show();
        } else {
            $('#totales_import_cuuotas_multas').hide();
        }
        if (res.data.buenos > 0) {
            $('#actualizarPlantillaCuotasExtras').show();
        }

        var countA = new CountUp('errores_cuotas_multas_import', 0, res.data.errores);
            countA.start();

        var countB = new CountUp('buenos_cuotas_multas_import', 0, res.data.buenos);
            countB.start();

        var countC = new CountUp('pagos_cuotas_multas_import', 0, res.data.valores);
            countC.start();

    }).fail((err) => {
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