var import_recibos_table = null;
var channelImportadorRecibos = pusher.subscribe('importador-recibos-'+localStorage.getItem("notificacion_code"));

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
            {"data":'numero_concepto_facturacion'},
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'email'},
            {"data":'fecha_manual'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'pago', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'descuento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                var totalNuevoSaldo = row.saldo_nuevo - row.descuento
                return totalNuevoSaldo < 0 ? 0 : totalNuevoSaldo;
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'anticipos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'}
        ]
    });

    import_recibos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            totalesRecibosImport();
        }
    });

    var btnImportRecibo = document.getElementById('actualizarPlantillaRecibos');
    btnImportRecibo.removeEventListener('click', handleReciboClick);
    btnImportRecibo.addEventListener('click', handleReciboClick);
}

channelImportadorRecibos.bind('notificaciones', function(data) {

    if (data.success) {
        $('#cargarPlantillaRecibosLoagind').hide();

        if (data.accion == 1) {
            agregarToast(data.tipo, data.titulo, data.mensaje);
            $('#cargarPlantillaRecibos').show();
            $('#actualizarPlantillaRecibos').show();
            import_recibos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesRecibosImport();
                }
            });
        }

        if (data.accion == 2) {
            agregarToast(data.tipo, data.titulo, data.mensaje);
            $('#cargarPlantillaRecibos').show();
            import_recibos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    totalesRecibosImport();
                }
            });
        }
        
    } else {
        $('#cargarPlantillaRecibos').show();
        $('#actualizarPlantillaRecibos').hide();
        $('#cargarPlantillaRecibosLoagind').hide();
        agregarToast(data.tipo, data.titulo, data.mensaje);
    }
});

function handleReciboClick() {
    
    $('#cargarPlantillaRecibos').hide();
    $('#actualizarPlantillaRecibos').hide();
    $('#cargarPlantillaRecibosLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'recibos-cargar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        agregarToast('info', 'Importando pagos', 'Se le notificará cuando la importación haya terminado!', true);
    }).fail((err) => {
        $('#cargarPlantillaRecibos').show();
        $('#cargarPlantillaRecibosLoagind').hide();

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
        
        var data = res.currentTarget;
        if (data.responseURL == 'https://maximoph.com/login') {
            caduqueSession();
        }
        if (data.status > 299) {
            return;
        }
        var responseData = JSON.parse(res.currentTarget.response);
        
        if (responseData.success) {
            agregarToast('info', 'Cargando recibos', 'Se le notificará cuando la importación haya terminado!', true);
        } else {
            $('#cargarPlantillaRecibos').show();
            $('#cargarPlantillaRecibosLoagind').hide();
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaRecibos').show();
        $('#cargarPlantillaRecibosLoagind').hide();
    };
    return false;
});