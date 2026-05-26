var import_recibos_table = null;
var inputImportadorRecibos = document.getElementById('importador_recibos');
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
        columns: [
            {"data":'id', visible: false},
            {"data": function (row, type, set){
                console.log('row: ',row);
                if (row.errores) {
                    return `<span class="badge bg-danger" style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-exclamation-circle"></i> Fila ${row.row}: Errores
                            </span>`;
                }
                
                return `<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fas fa-check"></i> Fila ${row.row}: Listo
                        </span>`;
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
            {"data":'faltante_descuento', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                var totalNuevoSaldo = row.saldo_nuevo - row.descuento
                return totalNuevoSaldo < 0 ? 0 : totalNuevoSaldo;
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'anticipos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'nombre_comprobante'},
            {"data":'consecutivo'},
            {"data":'concepto'},
            {"data":'observacion'}
        ]
    });

    import_recibos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarRecibos').prop('disabled', false);
        } else {
            $('#importarRecibos').prop('disabled', true);
        }
    });

    $("#importador_recibos").on('change', function(event) {
        if ($("#importador_recibos").val()) {
            $('#cargarPlantillaRecibos').prop('disabled', false);
        } else {
            $('#cargarPlantillaRecibos').prop('disabled', true);
        }
    });
}

$(document).on('click', '#cargarPlantillaRecibos', function () {
    $('#cargarPlantillaRecibos').hide();
    $('#cargarPlantillaRecibosLoading').show();
    
    // Mostrar la barra de progreso
    $('#uploadStatusRecibos').show();
    
    var ajxForm = document.getElementById("form-importador-recibos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    
    xhr.open("POST", "importrecibos-importar");
    xhr.send(data);
    
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        if (responseData.success) {
            // La barra ya se mostrará con los eventos de progreso
        } else {
            $('#cargarPlantillaRecibos').show();
            $('#cargarPlantillaRecibosLoading').hide();
            $('#uploadStatusRecibos').hide();
            var mensaje = responseData.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Carga errada', errorsMsg);
        }
    };
    
    xhr.onerror = function (res) {
        $('#cargarPlantillaRecibos').show();
        $('#cargarPlantillaRecibosLoading').hide();
        $('#uploadStatusRecibos').hide();
        var responseData = JSON.parse(res.currentTarget.response);
        var mensaje = responseData.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Carga errada', errorsMsg);
    };
    
    return false;
});

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

$(document).on('click', '#importarRecibos', function () {
    $('#importarRecibos').hide();
    $('#importarRecibosLoading').show();

    // Mostrar la barra de progreso
    $('#uploadStatusRecibos').show();
    // Resetear la barra a 0% y cambiar el mensaje
    $('#uploadProgressRecibos').css('width', '0%').removeClass('bg-success').addClass('progress-bar-striped progress-bar-animated bg-primary');
    $('#progressTextRecibos').text('0%');
    $('#statusMessageRecibos').text('Iniciando carga de productos al sistema...');
    $('#processedRowsRecibos').text('0');

    $.ajax({
        method: 'POST',
        url: base_url + 'recibos-cache-actualizar',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    }).fail((err) => {
        $('#importarRecibos').show();
        $('#uploadStatusRecibos').hide();
        $('#importarRecibosLoading').hide();        

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

channelImportadorRecibos.bind('notificaciones', function(data) {
    console.log('data: ',data);
    // Si es un evento de progreso
    if (data.name === 'progress') {
        // Actualizar la barra de progreso y el mensaje
        $('#uploadProgressRecibos').css('width', data.progress + '%');
        $('#progressTextRecibos').text(data.progress + '%');
        $('#statusMessageRecibos').text(data.mensaje);
        $('#processedRowsRecibos').text(data.processed);
        $('#totalRowsRecibos').text(data.total);
        
        // Cambiar el color de la barra según el stage
        if (data.stage === 'completed') {
            $('#uploadProgressRecibos').removeClass('progress-bar-striped progress-bar-animated').addClass('bg-success');
            
            // Ocultar la barra después de 5 segundos
            setTimeout(() => {
                $('#uploadStatusRecibos').slideUp();
            }, 5000);

            $("#cargarPlantillaRecibos").show();
            $("#cargarPlantillaRecibosLoading").hide();
            $("#importarRecibos").show();
            $("#importarRecibosLoading").hide();
            
            // Recargar la tabla de productos importados
            if (import_recibos_table) {
                import_recibos_table.ajax.reload(function(res) {
                    if (res.success && res.data.length) {
                        $('#importarRecibos').prop('disabled', false);
                    } else {
                        $('#importarRecibos').prop('disabled', true);
                    }
                });
            }
        }
    } 
    // Si es el evento final de importación (el antiguo 'carga' o el nuevo 'import')
    else if (data.name === 'carga' || data.name === 'import') {
        // Recargar la tabla
        if (import_recibos_table) {
            import_recibos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#importarRecibos').prop('disabled', false);
                } else {
                    $('#importarRecibos').prop('disabled', true);
                }
            });
        }
        
        // Mostrar notificación (toast) solo si es el evento 'carga' (para mantener compatibilidad)
        if (data.name === 'carga') {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        }
        
        // Si es el evento 'import', no mostramos toast porque ya se mostró en el progreso
        // Pero si quieres mostrar un toast final, descomenta la siguiente línea:
        // agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        
        // Ocultar el loading del botón de importar
        $('#importarRecibosLoading').hide();
        $('#importarRecibos').show();
    }
});