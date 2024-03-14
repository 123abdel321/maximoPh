var pago_transferencia_table = null;
var searchValuePagoComprobante = null;

function pagotransferenciaInit() {

    $('#fecha_desde_estado_pago_comprobante').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_estado_pago_comprobante').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));

    pago_transferencia_table = $('#pagoTransferenciaTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        stateSave: true,
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
            headers: headersERP,
            url: base_url_erp + 'recibos-comprobante',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_estado_pago_comprobante').val();
                d.fecha_hasta = $('#fecha_hasta_estado_pago_comprobante').val();
                d.search = searchValuePagoComprobante;
                d.estado = $('#estado_pago_comprobante').val();
            }
        },
        columns: [
            {
                data: 'id_nit',
                render: function (row, type, data){
                    if (data.nit) {
                        return data.nit.numero_documento;
                    }
                    return '';
                }
            },
            {
                data: 'id_nit',
                render: function (row, type, data){
                    if (data.nit) {
                        return data.nit.nombre_completo;
                    }
                    return '';
                }
            },
            {"data":'fecha_manual'},
            {"data":'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                data: 'id',
                render: function (row, type, data){
                    if (data.archivos && data.archivos.length) {
                        var comprobante = data.archivos[0];
                        return `<img
                        style="height: 35px; border-radius: 10%; cursor: pointer;"
                        onclick="showImagen('${comprobante.url_archivo}', ${data.estado}, ${data.id})"
                        src="${bucketUrl}${comprobante.url_archivo}"
                        alt="${comprobante.id}" />`;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    if (row.estado == 1) {
                        return '<span class="badge rounded-pill bg-success">APROBADO</span>';
                    }
                    if (row.estado == 0) {
                        return '<span class="badge rounded-pill bg-danger">RECHAZADO</span>';
                    }
                    html+= '<span id="aprobarpago_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success aprobar-pago" style="margin-bottom: 0rem !important; min-width: 50px;">Aprobar</span>&nbsp;';
                    html+= '<span id="anularpago_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger anular-pago" style="margin-bottom: 0rem !important; min-width: 50px;">Rechazar</span>';
                    return html;
                }
            },
        ],
        'rowCallback': function(row, data, index){
            // if (data.total) {
            //     $('td', row).css('background-color', '#065664');
            //     $('td', row).css('font-weight', 'bold');
            //     $('td', row).css('color', 'white');
            //     return;
            // }
            if (data.estado == 2) {
                $('td', row).css('background-color', '#fffaac');
                return;
            }
        },
    });

    if (pago_transferencia_table) {
        pago_transferencia_table.on('click', '.aprobar-pago', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, pago_transferencia_table);

            Swal.fire({
                title: 'Aprobar pago: '+new Intl.NumberFormat("ja-JP").format(data.total_abono)+'?',
                text: "No se podrá revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aprobar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url_erp + 'recibos-comprobante',
                        method: 'PUT',
                        data: JSON.stringify({
                            id: id,
                            estado: 1
                        }),
                        headers: headersERP,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            pago_transferencia_table.ajax.reload(null, false);
                            agregarToast('exito', 'Pago exitoso', 'Pago tranferencia aprobado con exito!', true );
                        } else {
                            agregarToast('error', 'Pago errado', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Pago errado', res.message);
                    });
                }
            })
        });

        pago_transferencia_table.on('click', '.anular-pago', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, pago_transferencia_table);

            Swal.fire({
                title: 'Rechazar pago: '+new Intl.NumberFormat("ja-JP").format(data.total_abono)+'?',
                text: "No se podrá revertir!",
                input: "text",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Rechazar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed){
                    $.ajax({
                        url: base_url_erp + 'recibos-comprobante',
                        method: 'PUT',
                        data: JSON.stringify({
                            id: id,
                            estado: 0,
                            observacion: result.value
                        }),
                        headers: headersERP,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            pago_transferencia_table.ajax.reload(null, false);
                            agregarToast('exito', 'Pago exitoso', 'Pago tranferencia anulado con exito!', true );
                        } else {
                            agregarToast('error', 'Pago errado', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Pago errado', res.message);
                    });
                }
            })
        });
    }

    $('.water').hide();
    pago_transferencia_table.ajax.reload();
    console.log('pagotransferenciaInit');
}

$(document).on('click', '#aprobarPagoTransferencia', function () {
    $('#rechazarPagoTransferencia').hide();
    $('#aprobarPagoTransferencia').hide();
    $('#PagoTransferenciaLoading').show();

    let data = {
        id: $("#id_pago_tranferencia").val(),
        estado: 1,
        observacion: $("#observacion_pago_transferencia").val(),
    }

    $.ajax({
        url: base_url_erp + 'recibos-comprobante',
        method: 'PUT',
        headers: headersERP,
        data: JSON.stringify(data),
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#pagoTranferenciaFormModal').modal("hide");
            pago_transferencia_table.ajax.reload(null, false);
            agregarToast('exito', 'Pago exitoso', 'Pago tranferencia anulado con exito!', true );
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
        agregarToast('error', 'Pago errada', errorsMsg);
    });
});

$(document).on('click', '#rechazarPagoTransferencia', function () {
    $('#rechazarPagoTransferencia').hide();
    $('#aprobarPagoTransferencia').hide();
    $('#PagoTransferenciaLoading').show();

    let data = {
        id: $("#id_pago_tranferencia").val(),
        estado: 0,
        observacion: $("#observacion_pago_transferencia").val(),
    }

    $.ajax({
        url: base_url_erp + 'recibos-comprobante',
        method: 'PUT',
        headers: headersERP,
        data: JSON.stringify(data),
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#pagoTranferenciaFormModal').modal("hide");
            pago_transferencia_table.ajax.reload(null, false);
            agregarToast('exito', 'Pago exitoso', 'Pago tranferencia anulado con exito!', true );
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
        agregarToast('error', 'Pago errada', errorsMsg);
    });
});

$(document).on('change', '#estado_pago_comprobante', function () {
    pago_transferencia_table.ajax.reload();
});

$(document).on('change', '#fecha_desde_estado_pago_comprobante', function () {
    pago_transferencia_table.ajax.reload();
});

$(document).on('change', '#fecha_hasta_estado_pago_comprobante', function () {
    pago_transferencia_table.ajax.reload();
});

function showImagen(urlImagen, estado, id) {
    $("#cancelarPagoTransferencia").show();
    $('#PagoTransferenciaLoading').hide();
    $('#aprobarPagoTransferencia').hide();
    $('#rechazarPagoTransferencia').hide();
    $("#input_observacion_pago_transferencia").hide();

    if (estado == 2) {

        $("#id_pago_tranferencia").val(id);
        $("#observacion_pago_transferencia").val("");
        
        $("#cancelarPagoTransferencia").hide();
        $('#aprobarPagoTransferencia').show();
        $('#rechazarPagoTransferencia').show();
        $("#input_observacion_pago_transferencia").show();
    }

    $('#imagen_pago_transferencia').attr('src', bucketUrl+urlImagen);
    $('#pagoTranferenciaFormModal').modal('show');
}

function searchPagoTranferencia (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePagoComprobante = $('#searchInputPagosTranferencia').val();
    searchValuePagoComprobante = searchValuePagoComprobante+botonPrecionado;
    if(event.key == 'Backspace') searchValuePagoComprobante = searchValuePagoComprobante.slice(0, -1);

    pago_transferencia_table.context[0].jqXHR.abort();
    pago_transferencia_table.ajax.reload(function () {
        
    });
}