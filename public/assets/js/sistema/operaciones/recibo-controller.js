var fecha = null;
var recibo_table = null;
var calculandoRow = false;
var guardandoRecibo = false;
var recibo_table_pagos = null;
var validarFacturaRecibo = null;
var totalAnticiposRecibo = 0;
var $comboNitRecibos = null;
var $comboComprobanteRecibos = null;

function reciboInit () {
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_recibo').val(fecha);

    recibo_table = $('#reciboTable').DataTable({
        dom: '',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
        ordering: false,
        ajax:  {
            type: "GET",
            headers: headersERP,
            url: base_url_erp + 'recibos',
            data: function ( d ) {
                d.id_nit = $('#id_nit_recibo').val();
                d.fecha_manual = $('#fecha_manual_recibo').val();
            }
        },
        columns: [
            {"data":'codigo_cuenta'},
            {
                "data": function (row, type, set, col){
                    if (!row.cuenta_recibo) {
                        return 'ANTICIPOS: '+row.nombre_cuenta;
                    }
                    return row.nombre_cuenta;
                }, className: 'dt-body-left'
            },
            {"data":'fecha_manual', className: 'dt-body-left'},
            {"data":'plazo', className: 'dt-body-right'},
            {"data":'dias_cumplidos', className: 'dt-body-right'},
            {//DOCUMENTO REFERENCIA
                "data": function (row, type, set, col){
                    if (row.cuenta_recibo) {
                        return row.documento_referencia;
                    }
                    var isValid = row.documento_referencia ? 'is-valid' : '';
                    return `
                        <div class="input-group">
                            <input type="text" class="form-control ${isValid} form-control-sm" style="text-align: right; height: 25px; border-radius: 7px; padding: 5px;" id="recibo_documentorefe_${row.id}" onkeypress="changeDocumentoRefeReciboRow(${row.id}, event)" onfocusout="focusOutDocumentoReferencia(${row.id})" value="${row.documento_referencia}" style="min-width: 100px;">
                            <i class="fa fa-spinner fa-spin fa-fw documento-load" id="documentorecibo_load_${row.id}" style="display: none; position: absolute; color: #76b2b2; margin-left: 2px; margin-top: 5px; z-index: 99;"></i>
                            <div class="valid-feedback info-factura" style="margin-top: -5px;"></div>
                            <div class="invalid-feedback info-factura" style="margin-top: -5px;">Factura existente</div>
                        </div>
                    `;
                }, className: 'dt-body-left'
            },
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//VALOR RECIBIDO
                "data": function (row, type, set, col){
                    if (row.cuenta_recibo == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right; padding: 0.05rem 0.5rem !important;" id="recibo_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_recibido)}" onkeypress="changeValorRecibidoReciboRow(${row.id}, event)" onfocusout="focusOutValorReciboRow(${row.id})" onfocus="focusValorRecibido(${row.id})" style="min-width: 100px;">`;
                }
            },
            {"data":'nuevo_saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//CONCEPTO
                "data": function (row, type, set, col){
                    if (row.cuenta_recibo == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" class="form-control form-control-sm" id="recibo_concepto_${row.id}" placeholder="SIN OBSERVACIÓN" value="${row.concepto}" onkeypress="changeConceptoReciboRow(${row.id}, event)" style="width: 150px !important; padding: 0.05rem 0.5rem !important;" onfocus="this.select();">`;
                }
            }
        ],
        'rowCallback': function(row, data, index){
            if (data.cuenta_recibo == 'sin_deuda') {
                $('td', row).css('background-color', 'rgb(11 177 158)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(!data.cuenta_recibo) {
                $('td', row).css('background-color', 'rgb(64 164 209 / 21%)');
                return;
            }
        },
        initComplete: function () {
            $('#reciboTable').on('draw.dt', function() {
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
            });
        }
    });

    recibo_table_pagos = $('#reciboFormaPago').DataTable({
        dom: '',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
        ordering: false,
        ajax:  {
            type: "GET",
            headers: headersERP,
            data: {
                type: 'ventas'
            },
            url: base_url_erp + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                var anticipos = false;
                var className = '';
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 8) {
                            anticipos = true;
                            className = 'anticipos'
                        }
                    }
                }
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoRecibo(${row.id}, ${anticipos})" onfocusout="calcularRecibosPagos(${row.id})" onkeypress="changeFormaPagoRecibo(${row.id}, event, ${anticipos})" id="recibo_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#reciboFormaPago').on('draw.dt', function() {
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
            });
        }
    });

    $comboNitRecibos = $('#id_nit_recibo').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url_erp + 'nit/combo-nit',
            headers: headersERP,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
    
    $comboComprobanteRecibos= $('#id_comprobante_recibo').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: base_url_erp + 'comprobantes/combo-comprobante',
            headers: headersERP,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 0,
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    if (comprobantesRecibos && comprobantesRecibos.length == 1) {
        var dataComprobante = {
            id: comprobantesRecibos[0].id,
            text: comprobantesRecibos[0].codigo + ' - ' + comprobantesRecibos[0].nombre
        };
        var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
        $comboComprobanteRecibos.append(newOption).trigger('change');
        $comboComprobanteRecibos.val(dataComprobante.id).trigger('change');
    }

    loadFormasPagoRecibos();
}

$(document).on('change', '#id_comprobante_recibo', function () {
    consecutivoSiguienteRecibo();
});

$(document).on('click', '#iniciarCapturaRecibo', function () {
    $('#iniciarCapturaRecibo').hide();
    $('#iniciarCapturaReciboLoading').show();
    recibo_table.ajax.reload(function () {
        $('#iniciarCapturaRecibo').show();
        $('#cancelarCapturaRecibo').show();
        $('#crearCapturaReciboDisabled').show();
        $('#iniciarCapturaReciboLoading').hide();
        loadAnticiposRecibo();
        mostrarValoresRecibos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();
        $("#total_abono_recibo").val(new Intl.NumberFormat("ja-JP").format(totalSaldo));
        setTimeout(function(){
            $("#total_abono_recibo").focus();
            $("#total_abono_recibo").select();
        },80);
    });
});

$(document).on('click', '#cancelarCapturaRecibo', function () {
    cancelarRecibo();
});

$(document).on('click', '#crearCapturaRecibo', function () {
    saveRecibo();
});

function saveRecibo() {
    $('#iniciarCapturaRecibo').hide();
    $('#cancelarCapturaRecibo').hide();
    $('#crearCapturaRecibo').hide();
    $('#iniciarCapturaReciboLoading').show();

    let data = {
        pagos: getRecibosPagos(),
        movimiento: getMovimientoRecibo(),
        id_nit: $("#id_nit_recibo").val(),
        id_comprobante: $("#id_comprobante_recibo").val(),
        fecha_manual: $("#fecha_manual_recibo").val(),
        consecutivo: $("#documento_referencia_recibo").val(),
    }

    disabledFormasPagoRecibo();

    $.ajax({
        url: base_url_erp + 'recibos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headersERP,
        dataType: 'json',
    }).done((res) => {
        cancelarRecibo();
        consecutivoSiguienteRecibo();
        $('#iniciarCapturaRecibo').show();
        $('#iniciarCapturaReciboLoading').hide();
        agregarToast('exito', 'Creación exitosa', 'Recibo creado con exito!', true);

        var url = base_web_erp + 'recibos-print';
        url+= '?token_db='+localStorage.getItem("token_db_portafolio");
        url+= '&id='+res.impresion;

        if(res.impresion) {
            window.open(url, '_blank');
        }
    }).fail((err) => {
        consecutivoSiguienteRecibo();
        $('#iniciarCapturaRecibo').show();
        $('#cancelarCapturaRecibo').show();
        $('#crearCapturaRecibo').show();
        $('#iniciarCapturaReciboLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        for (field in mensaje) {
            var errores = mensaje[field];
            for (campo in errores) {
                errorsMsg += field+": "+errores[campo]+" <br>";
            }
            
        };
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getRecibosPagos() {
    var data = [];

    var dataReciboPagos = recibo_table_pagos.rows().data();

    if(!dataReciboPagos.length > 0) return data;

    for (let index = 0; index < dataReciboPagos.length; index++) {
        const dataPagoCompra = dataReciboPagos[index];
        var pagoRecibo = stringToNumberFloat($('#recibo_forma_pago_'+dataPagoCompra.id).val());
        if (pagoRecibo > 0) {
            data.push({
                id: dataPagoCompra.id,
                valor: pagoRecibo
            });
        }
    }

    return data;
}

function getMovimientoRecibo() {
    var data = [];
    var dataRecibos = recibo_table.rows().data();

    if(!dataRecibos.length) return data;

    for (let index = 0; index < dataRecibos.length; index++) {
        var recibo = dataRecibos[index];

        if (recibo.valor_recibido) {
            data.push(recibo);
        }
    }
    return data;
}

function cancelarRecibo() {
    $comboNitRecibos.val(0).trigger('change');
    var totalRows = recibo_table.rows().data().length;
    totalAnticiposRecibo = 0;

    if(recibo_table.rows().data().length){
        recibo_table.clear([]).draw();
        recibo_table.row(0).remove().draw();
        mostrarValoresRecibos();
        var countE = new CountUp('total_faltante_recibo', 0, 0, 2, 0.5);
            countE.start();
    }

    clearFormasPagoRecibo();
    $('#total_abono_recibo').val('0.00');
    $('#saldo_anticipo_recibo').val('0');
    $('#recibo_anticipo_disp').text('0');
    $('#crearCapturaRecibo').hide();
    $('#cancelarCapturaRecibo').hide();
    $('#input_anticipos_recibo').hide();
    $('#recibo_anticipo_disp_view').hide();
    $('#crearCapturaReciboDisabled').hide();
}

function clearFormasPagoRecibo() {
    var dataFormasPago = recibo_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#recibo_forma_pago_'+formaPago.id).val(0);
        }
    }
}

function changeTotalAbonoRecibo(event) {
    var dataRecibos = recibo_table.rows().data();
    if(event.keyCode == 13 && dataRecibos.length) {
        var totalAbono = stringToNumberFloat($('#total_abono_recibo').val());
        var dataAnticipo = {
            'index': null,
            'recibo': null
        };
        var totalSaldo = 0;

        for (let index = 0; index < dataRecibos.length; index++) {
            var recibo = dataRecibos[index];

            if (!recibo.cuenta_recibo) {
                if (!dataAnticipo.recibo) {
                    dataAnticipo.index = index;
                    dataAnticipo.recibo = recibo;
                }
                continue;
            }

            if (recibo.cuenta_recibo == "sin_deuda") continue;

            if (totalAbono <= 0) {
                recibo.valor_recibido = 0;
                recibo.nuevo_saldo = recibo.saldo;
            } else {
                totalSaldo+= parseFloat(recibo.saldo);
                if (recibo.saldo >= totalAbono) {
                    recibo.valor_recibido = totalAbono;
                    recibo.nuevo_saldo = recibo.saldo - totalAbono;
                    totalAbono = 0;
                } else if (totalAbono >= recibo.saldo) {
                    recibo.valor_recibido = recibo.saldo;
                    recibo.nuevo_saldo = 0;
                    totalAbono-= recibo.saldo;
                }
            }

            if (!recibo.concepto && recibo.valor_recibido > 0) {
                if (recibo.nuevo_saldo == 0) recibo.concepto = 'CANCELO DEUDA';
                else recibo.concepto = 'ABONO DEUDA';
            }

            recibo_table.row(index).data(recibo);
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
        }

        if (totalAbono) {
            
            dataAnticipo.recibo.nuevo_saldo = totalAbono;
            dataAnticipo.recibo.valor_recibido = totalAbono;
            dataAnticipo.recibo.documento_referencia = $('#documento_referencia_recibo').val();
            dataAnticipo.recibo.concepto = "ANTICIPO RECIBO";
            totalSaldo+= parseFloat(totalAbono);
            totalAbono = 0;
            recibo_table.row(dataAnticipo.index).data(dataAnticipo.recibo);
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
        }

        
        mostrarValoresRecibos();
        
        var dataPagoRecibo = recibo_table_pagos.rows().data();
        if(dataPagoRecibo.length) {
            focusFormaPagoRecibo(dataPagoRecibo[0].id);
        }
    }
}

$(document).on('change', '#id_nit_recibo', function () {
    let data = $('#id_nit_recibo').select2('data')[0];
    if (data) {
        document.getElementById('iniciarCapturaRecibo').click();
    }
});

function changeConceptoReciboRow(idRow, event) {
    if (!idRow) return;

    if (event.keyCode == 13) {
        var concepto = $("#recibo_concepto_"+idRow).val();
        var data = getDataById(idRow, recibo_table);
        data.concepto = concepto;
        recibo_table.row(idRow-1).data(data);
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
    }
}

function mostrarValoresRecibos() {
    
    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();

    if (totalAnticipos) {
        $('#recibo_anticipo_view').show();
    } else {
        $('#recibo_anticipo_view').hide();
    }

    if ((totalAbonos+totalAnticipos)) disabledFormasPagoRecibo(false);
    else disabledFormasPagoRecibo();

    var [totalPagos, totalCXP] = totalFormasPagoRecibos();

    var countA = new CountUp('recibo_abono', 0, totalAbonos, 2, 0.5);
        countA.start();

    var countB = new CountUp('recibo_saldo', 0, totalSaldo, 2, 0.5);
        countB.start();

    var countC = new CountUp('recibo_anticipo', 0, totalAnticipos, 2, 0.5);
        countC.start();

    var countD = new CountUp('recibo_total', 0, totalSaldo - totalAbonos, 2, 0.5);
        countD.start();

    if (!totalSaldo && !totalAbonos && !totalAnticipos) {
        var countE = new CountUp('total_faltante_recibo', 0, 0, 2, 0.5);
            countE.start();
    } else {
        var countE = new CountUp('total_faltante_recibo', 0, (totalAbonos + totalAnticipos) - (totalPagos + totalCXP), 2, 0.5);
            countE.start();
    }

    if (!((totalAbonos + totalAnticipos) - (totalPagos + totalCXP))) {
        $('#crearCapturaRecibo').show();
        $('#crearCapturaReciboDisabled').hide();
    } else {
        $('#crearCapturaRecibo').hide();
        $('#crearCapturaReciboDisabled').show();
    }
}

function actualizarTotalAbono() {
    var dataRecibos = recibo_table.rows().data();
    var totalAbonos = 0;

    for (let index = 0; index < dataRecibos.length; index++) {
        var recibo = dataRecibos[index];
     
        if (recibo.cuenta_recibo) {//ABONOS
            totalAbonos+= parseFloat(recibo.valor_recibido);
        }
    }

    $('#total_abono_recibo').val(new Intl.NumberFormat("ja-JP").format(totalAbonos));
}

function focusFormaPagoRecibo(idFormaPago, anticipo = false) {
    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();
    var [totalPagos, totalCXP] = totalFormasPagoRecibos(idFormaPago);
    var totalFactura = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);
    var saldoFormaPago = stringToNumberFloat($('#recibo_forma_pago_'+idFormaPago).val());

    if (anticipo) {
        if ((totalAnticiposRecibo - totalCXP) < totalFactura) {
            $('#recibo_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalAnticiposRecibo - totalCXP));
            $('#recibo_forma_pago_'+idFormaPago).select();
            return;
        }
    }

    if (!saldoFormaPago) {
        $('#recibo_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalFactura < 0 ? 0 : totalFactura));
    }
    $('#recibo_forma_pago_'+idFormaPago).select();
}

function totalValoresRecibos() {
    var totalSaldo = 0;
    var totalAbonos = 0;
    var totalAnticipos = 0;

    var dataRecibos = recibo_table.rows().data();

    for (let index = 0; index < dataRecibos.length; index++) {
        var recibo = dataRecibos[index];
        if (!recibo.cuenta_recibo) {//ANTICIPOS
            totalAnticipos+= recibo.valor_recibido;
        } else if (parseFloat(recibo.valor_recibido)){//PAGOS
            totalAbonos+= parseFloat(recibo.valor_recibido);
        }
        if (parseFloat(recibo.saldo)) {
            totalSaldo+= parseFloat(recibo.saldo);
        }
    }

    return [totalSaldo, totalAbonos, totalAnticipos];
}

function totalFormasPagoRecibos(idFormaPago = null) {

    var totalPagos = 0;
    var totalAnticipos = 0;
    var dataPagoRecibo = recibo_table_pagos.rows().data();

    if(dataPagoRecibo.length > 0) {
        for (let index = 0; index < dataPagoRecibo.length; index++) {
            
            var ventaPago = stringToNumberFloat($('#recibo_forma_pago_'+dataPagoRecibo[index].id).val());

            if (idFormaPago && idFormaPago == dataPagoRecibo[index].id) continue;

            if ($('#recibo_forma_pago_'+dataPagoRecibo[index].id).hasClass("anticipos")) totalAnticipos+= ventaPago;
            else totalPagos+= ventaPago;
        }
    }

    return [totalPagos, totalAnticipos];
}

function calcularRecibosPagos(idFormaPago = null) {

    if (
        parseInt($('#recibo_forma_pago_'+idFormaPago).val()) == '' ||
        $('#recibo_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#recibo_forma_pago_'+idFormaPago).val(0);
    }

    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();
    var [totalPagos, totalCXP] = totalFormasPagoRecibos();
    var totalFaltante = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);

    if (idFormaPago && totalFaltante < 0) {
        var [totalPagos, totalCXP] = totalFormasPagoRecibos(idFormaPago);
        $('#recibo_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalAbonos - (totalPagos + totalCXP)));
        $('#recibo_forma_pago_'+idFormaPago).select();
        return;
    }

    totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countE = new CountUp('total_faltante_recibo', 0, totalFaltante, 2, 0.5);
        countE.start();

    if (!totalFaltante) {
        $('#crearCapturaRecibo').show();
        $('#crearCapturaReciboDisabled').hide();
    } else {
        $('#crearCapturaRecibo').hide();
        $('#crearCapturaReciboDisabled').show();
    }
}

function changeFormaPagoRecibo(idFormaPago, event, anticipo) {
    if(event.keyCode == 13){

        calcularRecibosPagos(idFormaPago);

        var [totalPagos, totalCXP] = totalFormasPagoRecibos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();
        var totalFaltante = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);

        if (anticipo) {

            if (totalCXP > totalAnticiposRecibo) {

                var [totalPagos, totalCXP] = totalFormasPagoRecibos(idFormaPago);

                $('#recibo_forma_pago_'+idFormaPago).val(totalAnticiposRecibo);
                $('#recibo_forma_pago_'+idFormaPago).select();
                calcularRecibosPagos();
                return;
            } else if (totalCXP > (totalFaltante + totalCXP)) {
                $('#recibo_forma_pago_'+idFormaPago).val(totalFaltante);
                $('#recibo_forma_pago_'+idFormaPago).select();
                calcularRecibosPagos();
                return;
            }
        }

        if (totalFaltante == 0) {
            validateSaveRecibos();
            return;
        }
        
        focusNextFormasPagoRecibos(idFormaPago);
    }
}

function validateSaveRecibos() {
    $('#total_faltante_recibo_text').css("color","#484848");
    $('#total_faltante_recibo').css("color","#484848");

    if (!guardandoRecibo) {

        var [totalPagos, totalCXP] = totalFormasPagoRecibos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresRecibos();
        
        if ((totalPagos + totalCXP) >= (totalAbonos + totalAnticipos)) {
            guardandoRecibo = true;
            saveRecibo();
        } else {
            $('#total_faltante_recibo_text').css("color","red");
            $('#total_faltante_recibo').css("color","red");
            return;
        }
    }
}

function loadAnticiposRecibo() {
    totalAnticiposRecibo = 0;
    $('#input_anticipos_recibo').hide();
    $('#recibo_anticipo_disp_view').hide();
    $('#saldo_anticipo_recibo').val(0);
    $('#recibo_anticipo_disp').text('0.00');

    if(!$('#id_nit_recibo').val()) return;
    
    let data = {
        id_nit: $('#id_nit_recibo').val(),
        id_tipo_cuenta: 8
    }

    $.ajax({
        url: base_url_erp + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headersERP,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            var disabled = true;
            if (res.data) {
                var saldo = parseFloat(res.data.saldo);
                if (saldo > 0) {
                    disabled = false;
                    $('#recibo_anticipo_disp_view').show();
                    // $('#input_anticipos_recibo').show();
                    totalAnticiposRecibo = saldo;
                    $('#saldo_anticipo_recibo').val(new Intl.NumberFormat('ja-JP').format(saldo));
                    $('#recibo_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(saldo));
                }
            }
        }
    }).fail((err) => {
    });
}

function changeDocumentoRefeReciboRow(idRow, event) {
    var documentoReferencia = $('#recibo_documentorefe_'+idRow).val();
    var comprobante = $('#id_comprobante_recibo').val();

    $('#recibo_documentorefe_'+idRow).removeClass("is-invalid");
    $('#recibo_documentorefe_'+idRow).removeClass("is-valid");

    if (event.keyCode == 13 && documentoReferencia) {
        $('#documentorecibo_load_'+idRow).show();
        var data = getDataById(idRow, recibo_table);
        
        if (validarFacturaRecibo) {
            validarFacturaRecibo.abort();
        }
        setTimeout(function(){
            validarFacturaRecibo = $.ajax({
                url: base_url_erp + 'existe-factura',
                method: 'GET',
                data: {
                    id_comprobante: comprobante,
                    documento_referencia: documentoReferencia
                },
                headers: headersERP,
                dataType: 'json',
            }).done((res) => {
                data.documento_referencia = documentoReferencia;
                validarFacturaRecibo = null;
                $('#documentorecibo_load_'+idRow).hide();

                if(res.data == 0) $('#recibo_documentorefe_'+idRow).addClass("is-valid");
                else $('#recibo_documentorefe_'+idRow).removeClass("is-valid");

                setTimeout(function(){
                    $('#recibo_valor_'+idRow).focus();
                    $('#recibo_valor_'+idRow).select();
                },10);
                recibo_table.row(idRow-1).data(data);
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
            }).fail((err) => {
                validarFacturaRecibo = null;
                if(err.statusText != "abort") {
                    $('#documentorecibo_load_'+idRow).hide();
                }
            });
        },300);
    }

}

function focusOutDocumentoReferencia(idRow) {
    var data = getDataById(idRow, recibo_table);
    var documentoReferencia = $('#recibo_documentorefe_'+idRow).val();
    var comprobante = $('#id_comprobante_recibo').val();
    $('#recibo_documentorefe_'+idRow).removeClass("is-invalid");
    $('#recibo_documentorefe_'+idRow).removeClass("is-valid");
        
    if (!validarFacturaRecibo && documentoReferencia && comprobante) {
        $('#documentorecibo_load_'+idRow).show();
        setTimeout(function(){
            validarFacturaRecibo = $.ajax({
                url: base_url_erp + 'existe-factura',
                method: 'GET',
                data: {
                    id_comprobante: comprobante,
                    documento_referencia: documentoReferencia
                },
                headers: headersERP,
                dataType: 'json',
            }).done((res) => {
                $('#documentorecibo_load_'+idRow).hide();
                data.documento_referencia = documentoReferencia;
                validarFacturaRecibo = null;
                $('#documentorecibo_load_'+idRow).hide();
                
                if(res.data == 0) $('#recibo_documentorefe_'+idRow).addClass("is-valid");
                else $('#recibo_documentorefe_'+idRow).removeClass("is-valid");

                setTimeout(function(){
                    $('#recibo_valor_'+idRow).focus();
                    $('#recibo_valor_'+idRow).select();
                },10);
                recibo_table.row(idRow-1).data(data);
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
            }).fail((err) => {
                $('#documentorecibo_load_'+idRow).hide();
                validarFacturaRecibo = null;
                if(err.statusText != "abort") {
                    $('#documentorecibo_load_'+idRow).hide();
                }
            });
        },80);
    }
}

function focusNextFormasPagoRecibos(idFormaPago) {
    var dataCompraRecibos = recibo_table_pagos.rows().data();
    var idFormaPagoFocus = dataCompraRecibos[0].id;
    var obtenerFormaPago = false;

    if(!dataCompraRecibos.length > 0) return;

    for (let index = 0; index < dataCompraRecibos.length; index++) {
        const dataPagoRecibo = dataCompraRecibos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoRecibo.id;
            obtenerFormaPago = false;
        } else if (dataPagoRecibo.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoRecibo(idFormaPagoFocus);
}

function focusValorRecibido(idRow) {
    var data = getDataById(idRow, recibo_table);
    var dataAbono = stringToNumberFloat($("#recibo_valor_"+idRow).val());
    if (!dataAbono && data.cuenta_recibo) {
        $('#recibo_valor_'+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
    }
    setTimeout(function(){
        $('#recibo_valor_'+idRow).select();
    },10);
}

function focusOutValorReciboRow(idRow) {
    if (calculandoRow) return;
    var data = getDataById(idRow, recibo_table);
    var valorRecibido = stringToNumberFloat($("#recibo_valor_"+idRow).val());
    if (!valorRecibido) return;

    if (data.cuenta_recibo) {//ABONOS
        var valorRecibido = stringToNumberFloat($("#recibo_valor_"+idRow).val());
    
        if (data.saldo >= valorRecibido) {
            data.valor_recibido = valorRecibido;
            data.nuevo_saldo = data.saldo - valorRecibido;
        } else if (valorRecibido >= data.saldo) {
            data.valor_recibido = data.saldo;
            data.nuevo_saldo = 0;
            $("#recibo_valor_"+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
        }
        
        if (!data.concepto && data.valor_recibido > 0) {
            if (data.nuevo_saldo == 0) data.concepto = 'CANCELO DEUDA';
            else data.concepto = 'ABONO DEUDA';
        }
    } else {//ANTICIPOS
        var anticipoRecibido = stringToNumberFloat($("#recibo_valor_"+idRow).val());
        data.valor_recibido = anticipoRecibido;
        data.nuevo_saldo = anticipoRecibido;

        if (!data.concepto) data.concepto = 'ANTICIPO RECIBO';
    }

    recibo_table.row(idRow-1).data(data);
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
    mostrarValoresRecibos();
    actualizarTotalAbono();
}

function changeValorRecibidoReciboRow(idRow, event) {
    if (!idRow) return;
    if(event.keyCode == 13) {
        calculandoRow = true;
        var data = getDataById(idRow, recibo_table);

        if (data.cuenta_recibo) {//ABONOS
            var valorRecibido = stringToNumberFloat($("#recibo_valor_"+idRow).val());
            
            if (data.saldo >= valorRecibido) {
                data.valor_recibido = valorRecibido;
                data.nuevo_saldo = data.saldo - valorRecibido;
            } else if (valorRecibido >= data.saldo) {
                data.valor_recibido = data.saldo;
                data.nuevo_saldo = 0;
                $("#recibo_valor_"+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
            }
            
            if (!data.concepto && data.valor_recibido > 0) {
                if (data.nuevo_saldo == 0) data.concepto = 'CANCELO DEUDA';
                else data.concepto = 'ABONO DEUDA';
            }
        } else {//ANTICIPOS
            var anticipoRecibido = stringToNumberFloat($("#recibo_valor_"+idRow).val());
            data.valor_recibido = anticipoRecibido;
            data.nuevo_saldo = anticipoRecibido;

            if (!data.concepto) data.concepto = 'ANTICIPO RECIBIO';
        }

        recibo_table.row(idRow-1).data(data);
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
        mostrarValoresRecibos();
        actualizarTotalAbono();
        setTimeout(function(){
            $('#recibo_concepto_'+idRow).focus();
            $('#recibo_concepto_'+idRow).select();
        },80);
        setTimeout(function(){
            calculandoRow = false;
        },200); 
    }
}

function consecutivoSiguienteRecibo() {
    var id_comprobante = $('#id_comprobante_recibo').val();
    var fecha_manual_hoy = fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    if(id_comprobante && fecha_manual_hoy) {

        let data = {
            id_comprobante: id_comprobante,
            fecha_manual: fecha_manual_hoy
        }

        $.ajax({
            url: base_url_erp + 'consecutivo',
            method: 'GET',
            data: data,
            headers: headersERP,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                $("#documento_referencia_recibo").val(res.data);
            }
        }).fail((err) => {
        });
    }
}

function loadFormasPagoRecibos() {
    var totalRows = recibo_table_pagos.rows().data().length;
    if(recibo_table_pagos.rows().data().length){
        recibo_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            recibo_table_pagos.row(0).remove().draw();
        }
    }
    recibo_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoRecibo(true);
    });
}

function disabledFormasPagoRecibo(estado = true) {
    var dataFormasPago = recibo_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#recibo_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }

    if (totalAnticiposRecibo <= 0) {
        var pagosAnticipos = document.getElementsByClassName('anticipos');
        if (pagosAnticipos) { //HIDE ELEMENTS
            for (let index = 0; index < pagosAnticipos.length; index++) {
                const element = pagosAnticipos[index];
                element.disabled = true;
            }
        }
    }
}