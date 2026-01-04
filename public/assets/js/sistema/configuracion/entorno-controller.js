var $nitPorDefecto = null;
var newFirmaDigital = null;
var newQrFactura = null;
var tokenEco = "";
// var tokenEco = "Bearer 5|HoHWzVDv3wwomgELloQS5RXGyEBEdBZtyKYzSI7X5e1f60a7";
var $comboConceptoFacturacion = null;
var $comboFormasPagoPlacetoPay = null;
var $comboFormasPagoComprobante = null;
var $comboCuentaIngreso = null;
var $comboCuentaAnticipo = null;
var $comboCuentaIntereses = null;
var $comboCuentaIngresoIntereses = null;
var $comboCuentaIngresoPagos = null;
var $comboCuentaIngresoPasarela = null;
var $comboCuentaIngresoComprobante = null;

function entornoInit() {

    cargarCombosEntorno();

    var numberEntorno = [
        'area_total_m2',
        'redondeo_intereses',
        'redondeo_pronto_pago',
        'numero_total_unidades',
        'valor_total_presupuesto_year_actual',
        'porcentaje_intereses_mora',
        'dias_pronto_pago',
    ];

    var checkEntorno = [
        'editar_coheficiente_admon_inmueble',
        'editar_valor_admon_inmueble',
        'validacion_estricta',
        'causacion_mensual_rapida',
        'descuento_pago_parcial',
        'recausar_meses',
        'validar_fecha_entrega_causacion',
        'detallar_facturas',
        'aceptar_terminos',
    ];

    var dateEntorno = [
        'periodo_facturacion',
    ];

    var textEntorno = [
        'factura_texto1',
        'factura_texto2',
        'placetopay_url',
        'placetopay_login',
        'placetopay_trankey',
        'terminos_condiciones',
        'nombre_administrador',
    ]; 

    var select = [
        'documento_referencia_agrupado',
    ];

    var select2 = [
        'id_concepto_pago_none',
        'id_nit_por_defecto',
        'placetopay_forma_pago',
        'id_forma_pago_comprobante',
        'id_cuenta_ingreso',
        'id_cuenta_anticipos',
        'id_cuenta_intereses',
        'id_cuenta_egreso_pagos',
        'id_cuenta_ingreso_pasarela',
        'id_cuenta_ingreso_intereses',
        'id_cuenta_ingreso_recibos_caja',
    ];

    var img = [
        'firma_digital'
    ]

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        if (variable.nombre == 'firma_digital') {
            $("#preview_firma_digital_paz_salvo").attr('src', variable.valor);
            $("#preview_firma_digital_paz_salvo").show();
            $("#firma_digital_paz_salvo").hide();
        }

        if (variable.nombre == 'qr_facturas') {
            $("#preview_qr_facturas").attr('src', variable.valor);
            $("#preview_qr_facturas").show();
            $("#qr_facturas").hide();
        }

        if (variable.nombre == 'eco_login') {
            tokenEco = variable.valor
        }

        if (numberEntorno.indexOf(variable.nombre) + 1) {
            $('#'+variable.nombre).val(new Intl.NumberFormat("ja-JP").format(variable.valor));
        }

        if (checkEntorno.indexOf(variable.nombre) + 1) {
            if (variable.valor == '1') $('#'+variable.nombre).prop('checked', true);
            else $('#'+variable.nombre).prop('checked', false);
        }

        if (dateEntorno.indexOf(variable.nombre) + 1) {
            $('#'+variable.nombre).val(variable.valor);
        }

        if (textEntorno.indexOf(variable.nombre) + 1) {
            $('#'+variable.nombre).val(variable.valor);
        }

        if (select.indexOf(variable.nombre) + 1) {
            $('#'+variable.nombre).val(variable.valor);
        }

        if (select2.indexOf(variable.nombre) + 1) {
            if (variable.nombre == 'id_concepto_pago_none') {
                var dataConceptoFacturacion = {
                    id: variable.concepto_facturacion.id,
                    text: variable.concepto_facturacion.codigo + ' - ' + variable.concepto_facturacion.nombre_concepto
                };
                var newOption = new Option(dataConceptoFacturacion.text, dataConceptoFacturacion.id, false, false);
                $comboConceptoFacturacion.append(newOption).trigger('change');
                $comboConceptoFacturacion.val(dataConceptoFacturacion.id).trigger('change');
            }

            if (variable.nombre == 'id_nit_por_defecto' && variable.nit) {
                var dataNit = {
                    id: variable.nit.id,
                    text: variable.nit.razon_social ? variable.nit.razon_social : variable.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $nitPorDefecto.append(newOption).trigger('change');
                $nitPorDefecto.val(dataNit.id).trigger('change');
            }

            if (variable.nombre == 'placetopay_forma_pago') {
                var dataPlacetoPay = {
                    id: variable.formas_pago.id,
                    text: variable.formas_pago.nombre
                };
                var newOption = new Option(dataPlacetoPay.text, dataPlacetoPay.id, false, false);
                $comboFormasPagoPlacetoPay.append(newOption).trigger('change');
                $comboFormasPagoPlacetoPay.val(dataPlacetoPay.id).trigger('change');
            }

            if (variable.nombre == 'id_forma_pago_comprobante' && variable.formas_pago) {
                var dataPagoComprobante = {
                    id: variable.formas_pago.id,
                    text: variable.formas_pago.nombre
                };
                var newOption = new Option(dataPagoComprobante.text, dataPagoComprobante.id, false, false);
                $comboFormasPagoComprobante.append(newOption).trigger('change');
                $comboFormasPagoComprobante.val(dataPagoComprobante.id).trigger('change');
            }

            if (variable.nombre == 'id_cuenta_ingreso' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngreso.append(newOption).trigger('change');
                $comboCuentaIngreso.val(dataCuenta.id).trigger('change');
            }
            
            if (variable.nombre == 'id_cuenta_anticipos' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaAnticipo.append(newOption).trigger('change');
                $comboCuentaAnticipo.val(dataCuenta.id).trigger('change');
            }

            if (variable.nombre == 'id_cuenta_intereses' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIntereses.append(newOption).trigger('change');
                $comboCuentaIntereses.val(dataCuenta.id).trigger('change');
            }
            
            if (variable.nombre == 'id_cuenta_ingreso_intereses' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngresoIntereses.append(newOption).trigger('change');
                $comboCuentaIngresoIntereses.val(dataCuenta.id).trigger('change');
            }

            if (variable.nombre == 'id_cuenta_egreso_pagos' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngresoPagos.append(newOption).trigger('change');
                $comboCuentaIngresoPagos.val(dataCuenta.id).trigger('change');
            }

            if (variable.nombre == 'id_cuenta_ingreso_pasarela' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngresoPasarela.append(newOption).trigger('change');
                $comboCuentaIngresoPasarela.val(dataCuenta.id).trigger('change');
            }

            if (variable.nombre == 'id_cuenta_ingreso_recibos_caja' && variable.cuenta) {
                var dataCuenta = {
                    id: variable.cuenta.id,
                    text: variable.cuenta.cuenta + ' - ' + variable.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngresoComprobante.append(newOption).trigger('change');
                $comboCuentaIngresoComprobante.val(dataCuenta.id).trigger('change');
            }
            
        }
        
    }

    $('#contenedor-canales').html(`
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-sm mt-2">Cargando...</p>
        </div>
    `);
}

function cargarCombosEntorno() {
    $comboConceptoFacturacion = $('#id_concepto_pago_none').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un concepto de facturación",
        allowClear: true,
        ajax: {
            url: 'api/concepto-facturacion-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 0
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboFormasPagoPlacetoPay = $('#placetopay_forma_pago').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una forma de pago",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'forma-pago/combo-forma-pago',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 0
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboFormasPagoComprobante = $('#id_forma_pago_comprobante').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una forma de pago",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'forma-pago/combo-forma-pago',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 0
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $nitPorDefecto = $('#id_nit_por_defecto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una persona",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
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
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIngreso = $('#id_cuenta_ingreso_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaAnticipo = $('#id_cuenta_anticipos_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIntereses = $('#id_cuenta_intereses_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIngresoIntereses = $('#id_cuenta_ingreso_intereses_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIngresoPagos = $('#id_cuenta_ingreso_pagos_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIngresoPasarela = $('#id_cuenta_ingreso_pasarela_entorno').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaIngresoComprobante = $('#id_cuenta_ingreso_recibos_caja').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: base_url_erp + 'plan-cuenta/combo-cuenta',
            headers: headersERP,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function readURLFirmaDigitalNueva(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newFirmaDigital = e.target.result;
            $('#preview_firma_digital_paz_salvo').attr('src', e.target.result);
            $('#firma_digital_paz_salvo').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#preview_firma_digital_paz_salvo').hide();
        $('#firma_digital_paz_salvo').show();
    }
}

function readURLQrFactura(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newQrFactura = e.target.result;
            $('#preview_qr_facturas').attr('src', e.target.result);
            $('#qr_facturas').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#preview_qr_facturas').hide();
        $('#qr_facturas').show();
    }
}

function validarNotificaciones() {
    $.ajax({
        url: base_url_eco + 'credenciales',
        method: 'GET',
        headers: {
            "Authorization": tokenEco,
            "Content-Type": "application/json",
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            let htmlContent = '';

            // Recorremos los datos
            res.data.forEach(item => {
                // Lógica visual según el tipo de canal
                let icono = '';
                let colorIcono = '';
                let titulo = '';

                // Definir iconos y colores según el tipo
                if(item.tipo === 'whatsapp'){
                    icono = 'fab fa-whatsapp';
                    titulo = 'WhatsApp';
                    // Si está activo verde, si no gris
                    colorIcono = item.activo ? 'text-success' : 'text-secondary'; 
                } else if(item.tipo === 'email'){
                    icono = 'fas fa-envelope';
                    titulo = 'Correo Electrónico';
                    // Si está activo warning (típico de email en argon) o info, si no gris
                    colorIcono = item.activo ? 'text-warning' : 'text-secondary';
                } else {
                    icono = 'fab fa-bell';
                    titulo = item.tipo.charAt(0).toUpperCase() + item.tipo.slice(1);
                    colorIcono = 'text-info';
                }

                // Lógica para el estado (Badge)
                let badgeClass = item.activo ? 'bg-gradient-success' : 'bg-gradient-secondary';
                let textoEstado = item.activo ? 'Activo' : 'Inactivo';
                
                // Lógica para el estado de verificación
                let verificacionHtml = '';
                if(item.estado_verificacion === 'verificado'){
                    verificacionHtml = `<span class="text-xs text-success font-weight-bold"><i class="fas fa-check-circle me-1"></i>Verificado</span>`;
                } else {
                    verificacionHtml = `<span class="text-xs text-danger font-weight-bold"><i class="fas fa-exclamation-circle me-1"></i>No verificado</span>`;
                }

                // Construcción de la tarjeta (Card de Argon)
                htmlContent += `
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-frame shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl me-3">
                                    <i class="${icono} ${colorIcono} opacity-10" aria-hidden="true" style="font-size: 1.5rem; line-height: 1.5;"></i>
                                </div>
                                
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-sm font-weight-bolder">${titulo}</h6>
                                        <span class="badge badge-sm ${badgeClass}">${textoEstado}</span>
                                    </div>
                                    <p class="text-xs text-secondary mb-0">
                                        Proveedor: <span class="text-dark font-weight-bold text-capitalize">${item.proveedor}</span>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        ${verificacionHtml}
                                        <small class="text-xxs text-secondary">${new Date(item.ultima_verificacion).toLocaleDateString()}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });

            // Si no hay datos
            if(res.data.length === 0){
                htmlContent = `
                    <div class="col-12 text-center text-muted">
                        <p>No se encontraron canales configurados.</p>
                    </div>`;
            }

            // Inyectar el HTML
            $('#contenedor-canales').html(htmlContent);
        }
    }).fail((err) => {
        // Manejo de error visual en el contenedor
        $('#contenedor-canales').html(`
            <div class="col-12 text-center text-danger py-3">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p class="text-sm">Error al cargar las notificaciones.</p>
            </div>
        `);

        $('#updateCecos').show();
        $('#saveCecosLoading').hide();
        
        var mensaje = err.responseJSON?.message || "Error desconocido";
        // Tu función existente de manejo de errores
        if(typeof arreglarMensajeError === 'function') {
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    });
}

function generarTokenEco() {
    
    // Cambio visual: Ocultar botón, mostrar cargando
    $('#btn-container-token').hide();
    $('#spinner-token').show();

    $.ajax({
        url: base_url + 'eco-register',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $('#btn-container-token').show();
        $('#spinner-token').hide();
        
        if(res.success){
            // 1. Asignar el token a la variable global
            tokenEco = res.token; 
            // 2. Feedback visual rápido (Opcional, un toast de éxito)
            agregarToast('exito', 'Conexión Exitosa', 'Notificaciones configuradas correctamente!', true);
            // 3. Cambiar vistas automáticamente
            $("#div-token-eco").fadeOut(300, function() {
                $("#div-canales-eco").fadeIn(300);
                validarNotificaciones();
            });

        }

    }).fail((err) => {
        // Restaurar estado visual
        $('#btn-container-token').show();
        $('#spinner-token').hide();

        var mensaje = err.responseJSON?.message || "Error de conexión";
        if(typeof arreglarMensajeError === 'function') {
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Error', errorsMsg);
        }
    });
}

$(document).on('click', '#notificaciones-tab', function () {
    if (tokenEco) {
        $("#div-canales-eco").show();
        $("#div-token-eco").hide();
        validarNotificaciones();
    } else {
        $("#div-canales-eco").hide();
        $("#div-token-eco").show();
    }
});

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        'area_total_m2': stringToNumberFloat($('#area_total_m2').val()),
        'redondeo_intereses': stringToNumberFloat($('#redondeo_intereses').val()),
        'redondeo_pronto_pago': stringToNumberFloat($('#redondeo_pronto_pago').val()),
        'numero_total_unidades': stringToNumberFloat($('#numero_total_unidades').val()),
        'valor_total_presupuesto_year_actual': stringToNumberFloat($('#valor_total_presupuesto_year_actual').val()),
        'porcentaje_intereses_mora': stringToNumberFloat($('#porcentaje_intereses_mora').val()),
        'periodo_facturacion': $('#periodo_facturacion').val(),
        'editar_valor_admon_inmueble': $("input[type='checkbox']#editar_valor_admon_inmueble").is(':checked') ? '1' : '0',
        'editar_coheficiente_admon_inmueble': $("input[type='checkbox']#editar_coheficiente_admon_inmueble").is(':checked') ? '1' : '0',
        'validacion_estricta': $("input[type='checkbox']#validacion_estricta").is(':checked') ? '1' : '0',
        'causacion_mensual_rapida': $("input[type='checkbox']#causacion_mensual_rapida").is(':checked') ? '1' : '0',
        'descuento_pago_parcial': $("input[type='checkbox']#descuento_pago_parcial").is(':checked') ? '1' : '0',
        'recausar_meses': $("input[type='checkbox']#recausar_meses").is(':checked') ? '1' : '0',
        'validar_fecha_entrega_causacion': $("input[type='checkbox']#validar_fecha_entrega_causacion").is(':checked') ? '1' : '0',
        'detallar_facturas': $("input[type='checkbox']#detallar_facturas").is(':checked') ? '1' : '0',
        'aceptar_terminos': $("input[type='checkbox']#aceptar_terminos").is(':checked') ? '1' : '0',
        'factura_texto1': $('#factura_texto1').val(),
        'factura_texto2': $('#factura_texto2').val(),
        'terminos_condiciones': $('#terminos_condiciones').val(),
        'dias_pronto_pago': stringToNumberFloat($('#dias_pronto_pago').val()),
        'documento_referencia_agrupado': $('#documento_referencia_agrupado').val(),
        // 'tasa_pronto_pago': stringToNumberFloat($('#tasa_pronto_pago').val()),
        'id_concepto_pago_none': $('#id_concepto_pago_none').val(),
        'id_nit_por_defecto': $('#id_nit_por_defecto').val(),

        'id_cuenta_ingreso': $('#id_cuenta_ingreso_entorno').val(),
        'id_cuenta_anticipos': $('#id_cuenta_anticipos_entorno').val(),
        'id_cuenta_intereses': $('#id_cuenta_intereses_entorno').val(),
        'id_cuenta_ingreso_intereses': $('#id_cuenta_ingreso_intereses_entorno').val(),
        'id_cuenta_egreso_pagos': $('#id_cuenta_ingreso_pagos_entorno').val(),
        'id_cuenta_ingreso_pasarela': $('#id_cuenta_ingreso_pasarela_entorno').val(),
        'id_cuenta_ingreso_recibos_caja': $('#id_cuenta_ingreso_recibos_caja').val(),

        'id_forma_pago_comprobante': $('#id_forma_pago_comprobante').val(),

        'firma_digital': newFirmaDigital,
        'qr_facturas': newQrFactura,
        'nombre_administrador': $('#nombre_administrador').val(),

        'placetopay_url': $('#placetopay_url').val(),
        'placetopay_login': $('#placetopay_login').val(),
        'placetopay_trankey': $('#placetopay_trankey').val(),
        'placetopay_forma_pago': $('#placetopay_forma_pago').val(),
    };

    if (stringToNumberFloat($('#dias_pronto_pago').val() > 30)) {
        $('#dias_pronto_pago').val(30);
        agregarToast('error', 'Días pronto pago', 'Maximo 30 días de pronto pago');
        return;
    }

    $.ajax({
        url: base_url + 'entorno',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#updateEntornoLoading").hide();
            $("#updateEntorno").show();

            agregarToast('exito', 'Actualización exitosa', 'Datos de entorno actualizados con exito!', true);
        }
    }).fail((err) => {
        $("#updateEntornoLoading").hide();
        $("#updateEntorno").show();
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
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
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