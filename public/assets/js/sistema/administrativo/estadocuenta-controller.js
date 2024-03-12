var totalCuentasPagar = 0;
var comprobanteFile = null;
var estado_cuenta_table = null;
var estado_cuenta_pagos_table = null;
var estado_cuenta_facturas_table = null;
var adjuntar_pagar_estado_cuenta = false

function estadocuentaInit() {

    cuotas_multas_table = $('#estadoCuentaTable').DataTable({
        pageLength: 100,
        dom: 'Brt',
        paging: false,
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
            url: base_url + 'estadocuenta',
        },
        columns: [
            {"data":'concepto'},
            {"data":'fecha_manual'},
            {"data":'documento_referencia'},
            {"data":'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'}
        ],
        'rowCallback': function(row, data, index){
            if (data.concepto == 'SIN CUENTAS POR PAGAR') {
                $('td', row).css('background-color', 'rgb(11 177 158)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (data.concepto == 'TOTALES') {
                $('td', row).css('background-color', '#065664');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        },
    });

    estado_cuenta_pagos_table = $('#estadoCuentaPagosTable').DataTable({
        pageLength: 100,
        dom: 'Brt',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'estadocuenta-pagos',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_estado_cuenta_pagos').val();
                d.fecha_hasta = $('#fecha_hasta_estado_cuenta_pagos').val();
                d.estado = $('#estado_estado_cuenta_pagos').val();
            }
        },
        columns: [
            {"data":'fecha_manual'},
            {"data":'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if (row.estado == 1) {
                        return '<span class="badge rounded-pill bg-success">Confirmado</span>';
                    }
                    if (row.estado == 2) {
                        return '<span class="badge rounded-pill bg-warning">Pendiente</span>';
                    }
                    if (row.estado == 0) {
                        return '<span class="badge rounded-pill bg-danger">Rechazado</span>';
                    }
                    return;
                }
            },
            {
                "data": function (row, type, set){
                    if (row.pagos.length) {
                        var pagosTexto = '';
                        for (let index = 0; index < row.pagos.length; index++) {
                            var pago = row.pagos[index];
                            pagosTexto+= ' '+pago.forma_pago.nombre
                        }
                        return pagosTexto;
                    }
                    return '';
                }
            },
            {"data":'observacion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (row.estado == 1) {
                        html+= '<span id="imprimirrecibo_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-recibo" style="margin-bottom: 0rem !important; color: black; background-color: white !important;">Imprimir</span>';
                    }
                    if (row.estado == 2) {
                        html+= '<span id="editpagoestado_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-pago-estado" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                        html+= '<span id="deletepagoestado_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-pago-estado" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    }
                    return html;
                }
            },
        ],
        'rowCallback': function(row, data, index){
            if (data.total) {
                $('td', row).css('background-color', '#065664');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (data.estado == 2) {
                $('td', row).css('background-color', '#fffaac');
                return;
            }
        },
    });

    estado_cuenta_facturas_table = $('#estadoCuentaFacturasTable').DataTable({
        pageLength: 100,
        dom: 'Brt',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'estadocuenta-facturas',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_estado_cuenta_facturas').val();
                d.fecha_hasta = $('#fecha_hasta_estado_cuenta_facturas').val();
            }
        },
        columns: [
            {"data":'documento_referencia', width: '140px'},
            {"data":'valor', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', width: '140px'},
            {"data":'fecha_manual',  width: '80px'},
            {"data":'concepto'},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (row.total) {
                        html+= '<span id="imprimirfactra_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-factura disabled" style="margin-bottom: 0rem !important; color: black; background-color: white !important;">Imprimir</span>';
                    }
                    return html;
                }, width: '60px'
            },
        ],
        'rowCallback': function(row, data, index){
            if (data.total) {
                $('td', row).css('background-color', '#065664');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        },
    });

    if (estado_cuenta_pagos_table) {
        estado_cuenta_pagos_table.on('click', '.edit-pago-estado', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, estado_cuenta_pagos_table);

            $('#saveEstadoCuentaPago').hide();
            $('#updateEstadoCuentaPago').show();

            $('#input_valor_pago_estado_cuenta').hide();
            $('#input_fecha_pago_estado_cuenta').show();
            $('#input_valor_comprobante_estado_cuenta').show();
            $('#input_imagen_comprobante_estado_cuenta').show();    
            $('#valor_pago_estado_cuenta').prop('required',false);
            $('#fecha_pago_estado_cuenta').prop('required',true);
            $('#valor_comprobante_estado_cuenta').prop('required',false);
            $('#imagen_comprobante_estado_cuenta').prop('required',false);

            $('#id_recibo_estado_cuenta_up').val(data.id);
            $('#fecha_pago_estado_cuenta').val(data.fecha_manual);
            $('#valor_comprobante_estado_cuenta').val(new Intl.NumberFormat("ja-JP").format(data.total_abono));

            $("#estadoCuentaPagoFormModal").modal('show');
        });

        estado_cuenta_pagos_table.on('click', '.drop-pago-estado', function() {
            var trPagoEstadoCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, estado_cuenta_pagos_table);

            Swal.fire({
                title: 'Eliminar recibo de: '+new Intl.NumberFormat("ja-JP").format(data.total_abono)+'?',
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url_erp + 'recibos-comprobante',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headersERP,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            estado_cuenta_pagos_table.row(trPagoEstadoCuenta).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Recibo eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    $('#fecha_desde_estado_cuenta_pagos').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_estado_cuenta_pagos').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));
    $('#fecha_desde_estado_cuenta_facturas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_estado_cuenta_facturas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));

    $('.water').hide();
    showViewEstadoCuenta(1);
    getTotalesEstadoCuenta();

    cuotas_multas_table.ajax.reload();
}

$(document).on('click', '.imprimir-recibo', function () {
    var id = this.id.split('_')[1];
    var url = base_web_erp + 'recibos-print';
    url+= '?token_db='+localStorage.getItem("token_db_portafolio");
    url+= '&id='+id;

    window.open(url, '_blank');
});

$(document).on('click', '#generatePagoEstadoCuenta', function () {
    adjuntar_pagar_estado_cuenta = true;
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    clearFormEstadoCuenta();
    $("#textEstadoCuentaPagoCreate").text('Deuda total: ' + new Intl.NumberFormat("ja-JP").format(totalCuentasPagar));
    
    $('#valor_pago_estado_cuenta').val(new Intl.NumberFormat("ja-JP").format(totalCuentasPagar));
    $('#fecha_pago_estado_cuenta').val(fecha);
    $("#saveEstadoCuentaPago").show();
    $("#updateEstadoCuentaPago").hide();
    $("#saveEstadoCuentaPagoLoading").hide();
    $('#input_valor_pago_estado_cuenta').hide();
    $('#input_fecha_pago_estado_cuenta').show();
    $('#input_valor_comprobante_estado_cuenta').show();
    $('#input_imagen_comprobante_estado_cuenta').show();

    $('#input_valor_pago_estado_cuenta').show();
    $('#input_fecha_pago_estado_cuenta').hide();        
    $('#input_valor_comprobante_estado_cuenta').hide();
    $('#input_imagen_comprobante_estado_cuenta').hide();
    $('#valor_pago_estado_cuenta').prop('required',true);
    $('#fecha_pago_estado_cuenta').prop('required',false);
    $('#valor_comprobante_estado_cuenta').prop('required',false);
    $('#imagen_comprobante_estado_cuenta').prop('required',false);

    $("#estadoCuentaPagoFormModal").modal('show');
});

$(document).on('click', '#generateComprobanteEstadoCuenta', function () {
    adjuntar_pagar_estado_cuenta = false;
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    clearFormEstadoCuenta();
    $("#textEstadoCuentaPagoCreate").text('Deuda total: ' + new Intl.NumberFormat("ja-JP").format(totalCuentasPagar));
    $('#valor_comprobante_estado_cuenta').val(new Intl.NumberFormat("ja-JP").format(totalCuentasPagar));

    $('#fecha_pago_estado_cuenta').val(fecha);
    $("#saveEstadoCuentaPago").show();
    $("#updateEstadoCuentaPago").hide();
    $("#saveEstadoCuentaPagoLoading").hide();
    $('#input_valor_pago_estado_cuenta').hide();
    $('#input_fecha_pago_estado_cuenta').show();
    $('#input_valor_comprobante_estado_cuenta').show();
    $('#input_imagen_comprobante_estado_cuenta').show();

    $('#input_valor_pago_estado_cuenta').hide();
    $('#input_fecha_pago_estado_cuenta').show();
    $('#input_valor_comprobante_estado_cuenta').show();
    $('#input_imagen_comprobante_estado_cuenta').show();    
    $('#valor_pago_estado_cuenta').prop('required',false);
    $('#fecha_pago_estado_cuenta').prop('required',true);
    $('#valor_comprobante_estado_cuenta').prop('required',false);
    $('#imagen_comprobante_estado_cuenta').prop('required',false);

    $("#estadoCuentaPagoFormModal").modal('show');
});

$(document).on('click', '#saveEstadoCuentaPago', function () {
    var form = document.querySelector('#estadoCuentaPagoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    var data = {
        'id_nit': $idNitEstadoCuenta,
        'id_comprobante': $idComprobante,
        'id_cuenta_ingreso': $idCuentaIngreso,
        'numero_documento': $numeroDocumentoEstadoCuenta,
        'fecha_pago': '',
        'valor_comprobante': 0,
        'valor_pago': 0,
        'comprobante': null,
    };

    if (adjuntar_pagar_estado_cuenta) {
        if (stringToNumberFloat($('#valor_pago_estado_cuenta').val()) > totalCuentasPagar) {
            setTimeout(function(){
                $('#valor_pago_estado_cuenta').focus();
                $('#valor_pago_estado_cuenta').select();
            },10);
            return;
        }
        if (!stringToNumberFloat($('#valor_pago_estado_cuenta').val())) {
            setTimeout(function(){
                $('#valor_pago_estado_cuenta').focus();
                $('#valor_pago_estado_cuenta').select();
            },10);
            return;
        }
        data.fecha_pago = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
        data.valor_pago = stringToNumberFloat($('#valor_pago_estado_cuenta').val());
    } else {
        if (stringToNumberFloat($('#valor_comprobante_estado_cuenta').val()) > totalCuentasPagar) {
            setTimeout(function(){
                $('#valor_comprobante_estado_cuenta').focus();
                $('#valor_comprobante_estado_cuenta').select();
            },10);
            return;
        }
        if (!stringToNumberFloat($('#valor_comprobante_estado_cuenta').val())) {
            setTimeout(function(){
                $('#valor_comprobante_estado_cuenta').focus();
                $('#valor_comprobante_estado_cuenta').select();
            },10);
            return;
        }
        data.fecha_pago = $('#fecha_pago_estado_cuenta').val();
        data.valor_comprobante = stringToNumberFloat($('#valor_comprobante_estado_cuenta').val());
        data.comprobante = comprobanteFile;
    }

    $('#saveEstadoCuentaPago').hide();
    $('#updateEstadoCuentaPago').hide();
    $('#saveEstadoCuentaPagoLoading').show();

    $.ajax({
        url: base_url_erp + 'recibos-comprobante',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headersERP,
        dataType: 'json',
    }).done((res) => {
        $('#saveEstadoCuentaPago').show();
        $('#saveEstadoCuentaPagoLoading').hide();
        if(res.success){
            $("#estadoCuentaPagoFormModal").modal('hide');
            getTotalesEstadoCuenta(false);
            showViewEstadoCuenta(2);
            estado_cuenta_pagos_table.ajax.reload();
            if (data.valor_comprobante) {
                agregarToast('exito', 'Comprobante adjunto', 'Comprobante adjuntado con exito!', true);
                return;
            }
            agregarToast('exito', 'Pago realizado', 'Pago realizado con exito!', true);
            return;
        }
    }).fail((err) => {
        $('#saveEstadoCuentaPago').show();
        $('#saveEstadoCuentaPagoLoading').hide();
        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        if (typeof mensaje === 'object') {
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
                agregarToast('error', 'Creación errada', errorsMsg);
            };
        } else {
            agregarToast('error', 'Creación errada', mensaje);
        }
    });
});

$(document).on('click', '#updateEstadoCuentaPago', function () {
    var form = document.querySelector('#estadoCuentaPagoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (stringToNumberFloat($('#valor_comprobante_estado_cuenta').val()) > totalCuentasPagar) {
        setTimeout(function(){
            $('#valor_comprobante_estado_cuenta').focus();
            $('#valor_comprobante_estado_cuenta').select();
        },10);
        return;
    }
    if (!stringToNumberFloat($('#valor_comprobante_estado_cuenta').val())) {
        setTimeout(function(){
            $('#valor_comprobante_estado_cuenta').focus();
            $('#valor_comprobante_estado_cuenta').select();
        },10);
        return;
    }

    var data = {
        'id': $('#id_recibo_estado_cuenta_up').val(),
        'fecha_pago': $('#fecha_pago_estado_cuenta').val(),
        'valor_comprobante': stringToNumberFloat($('#valor_comprobante_estado_cuenta').val()),
        'comprobante': comprobanteFile,
    };

    $('#saveEstadoCuentaPago').hide();
    $('#updateEstadoCuentaPago').hide();
    $('#saveEstadoCuentaPagoLoading').show();

    $.ajax({
        url: base_url_erp + 'recibos-comprobante',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headersERP,
        dataType: 'json',
    }).done((res) => {
        $('#saveEstadoCuentaPago').show();
        $('#saveEstadoCuentaPagoLoading').hide();
        if(res.success){
            $("#estadoCuentaPagoFormModal").modal('hide');
            getTotalesEstadoCuenta(false);
            showViewEstadoCuenta(2);
            estado_cuenta_pagos_table.ajax.reload();
            agregarToast('exito', 'Comprobante adjunto', 'Comprobante adjuntado con exito!', true);
        }
    }).fail((err) => {
        $('#updateEstadoCuentaPago').show();
        $('#saveEstadoCuentaPagoLoading').hide();
        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        if (typeof mensaje === 'object') {
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
                agregarToast('error', 'Actuailización errada', errorsMsg);
            };
        } else {
            agregarToast('error', 'Actuailización errada', mensaje);
        }
    });

    
});

$(document).on('change', '#fecha_desde_estado_cuenta_pagos', function () {
    estado_cuenta_pagos_table.ajax.reload();
});

$(document).on('change', '#fecha_hasta_estado_cuenta_pagos', function () {
    estado_cuenta_pagos_table.ajax.reload();
});

$(document).on('change', '#estado_estado_cuenta_pagos', function () {
    estado_cuenta_pagos_table.ajax.reload();
});

$(document).on('change', '#fecha_desde_estado_cuenta_facturas', function () {
    estado_cuenta_facturas_table.ajax.reload();
});

$(document).on('change', '#fecha_hasta_estado_cuenta_facturas', function () {
    estado_cuenta_facturas_table.ajax.reload();
});

function showViewEstadoCuenta(tipo) {
    $('#button_estado_cuenta').removeClass("button-totals-selected").addClass('button-totals');
    $('#button_historico_pagos').removeClass("button-totals-selected").addClass('button-totals');
    $('#button_historico_cxc').removeClass("button-totals-selected").addClass('button-totals');

    $('#table_estado_cuenta').hide();
    $('#table_estado_cuenta').hide();
    $('#generatePagoEstadoCuenta').hide();
    $('#generatePagoEstadoCuentaDisabled').hide();
    $('#generateComprobanteEstadoCuenta').hide();
    $('#generateComprobanteEstadoCuentaDisabled').hide();
    
    switch (tipo) {
        case 1:
            $('#table_estado_cuenta').show();
            $('#table_pagos_estado_cuenta').hide();
            $('#table_facturas_estado_cuenta').hide();
            if (totalCuentasPagar) {
                $('#generatePagoEstadoCuenta').show();
                $('#generateComprobanteEstadoCuenta').show();
            } else {
                $('#generatePagoEstadoCuentaDisabled').show();
                $('#generateComprobanteEstadoCuentaDisabled').show();
            }
            $('#button_estado_cuenta').removeClass("button-totals").addClass("button-totals-selected");
            break;
        case 2:
            $('#table_estado_cuenta').hide();
            $('#generatePagoEstadoCuenta').hide();
            $('#table_pagos_estado_cuenta').show();
            $('#table_facturas_estado_cuenta').hide();
            estado_cuenta_pagos_table.ajax.reload();
            $('#button_historico_pagos').removeClass("button-totals").addClass("button-totals-selected");
            break;
        case 3:
            $('#table_estado_cuenta').hide();
            $('#table_pagos_estado_cuenta').hide();
            $('#table_facturas_estado_cuenta').show();
            estado_cuenta_facturas_table.ajax.reload();
            $('#button_historico_cxc').removeClass("button-totals").addClass("button-totals-selected");
            break;
        default:
            break;
    }
}

function getTotalesEstadoCuenta(showButtonPay = true)  {
    $('#generatePagoEstadoCuenta').hide();
    $('#generatePagoEstadoCuentaDisabled').show();
    $('#generateComprobanteEstadoCuenta').hide();
    $('#generateComprobanteEstadoCuentaDisabled').show();

    $.ajax({
        url: base_url + 'estadocuenta-total',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            totalCuentasPagar = res.data.total_cuentas_pagar;
            
            if (res.data.total_cuentas_pagar) {
                if (showButtonPay) {
                    $('#generatePagoEstadoCuenta').show();
                    $('#generatePagoEstadoCuentaDisabled').hide();
                    $('#generateComprobanteEstadoCuenta').show();
                    $('#generateComprobanteEstadoCuentaDisabled').hide();
                }
            }

            var countA = new CountUp('total_estado_cuenta', 0, res.data.total_cuentas_pagar);
                countA.start();

            var countB = new CountUp('cuenta_cobro_estado_cuenta', 0, res.data.total_cuentas_cobro);
                countB.start();

            var countC = new CountUp('pagos_estado_cuenta', 0, res.data.total_pagos);
                countC.start();

                
        }
    }).fail((err) => {
    });
}

function clearFormEstadoCuenta() {
    $('#fecha_pago_estado_cuenta').val('');
    $('#valor_comprobante_estado_cuenta').val('');
    $('#valor_pago_estado_cuenta').val('');
    $('#imagen_comprobante_estado_cuenta').val('');
}

function readFileEstadoCuenta(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            comprobanteFile = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

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