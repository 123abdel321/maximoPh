var $nitPorDefecto = null;
var $comboConceptoFacturacion = null;

function entornoInit() {

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

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        var numberEntorno = [
            'area_total_m2',
            'redondeo_intereses',
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
            'presupuesto_mensual',
            'descuento_pago_parcial',
        ];

        var dateEntorno = [
            'periodo_facturacion',
        ];

        var textEntorno = [
            'factura_texto1',
            'factura_texto2',
        ]; 

        var select = [
            'documento_referencia_agrupado',
        ];

        var select2 = [
            'id_concepto_pago_none',
            'id_nit_por_defecto',
        ];

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
            if (variable.nombre == 'id_nit_por_defecto') {
                var dataNit = {
                    id: variable.nit.id,
                    text: variable.nit.razon_social ? variable.nit.razon_social : variable.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $nitPorDefecto.append(newOption).trigger('change');
                $nitPorDefecto.val(dataNit.id).trigger('change');
            }
        }
    }
}

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        'area_total_m2': stringToNumberFloat($('#area_total_m2').val()),
        'redondeo_intereses': $('#redondeo_intereses').val(),
        'numero_total_unidades': stringToNumberFloat($('#numero_total_unidades').val()),
        'valor_total_presupuesto_year_actual': stringToNumberFloat($('#valor_total_presupuesto_year_actual').val()),
        'porcentaje_intereses_mora': stringToNumberFloat($('#porcentaje_intereses_mora').val()),
        'periodo_facturacion': $('#periodo_facturacion').val(),
        'editar_valor_admon_inmueble': $("input[type='checkbox']#editar_valor_admon_inmueble").is(':checked') ? '1' : '0',
        'editar_coheficiente_admon_inmueble': $("input[type='checkbox']#editar_coheficiente_admon_inmueble").is(':checked') ? '1' : '0',
        'validacion_estricta': $("input[type='checkbox']#validacion_estricta").is(':checked') ? '1' : '0',
        'causacion_mensual_rapida': $("input[type='checkbox']#causacion_mensual_rapida").is(':checked') ? '1' : '0',
        'presupuesto_mensual': $("input[type='checkbox']#presupuesto_mensual").is(':checked') ? '1' : '0',
        'descuento_pago_parcial': $("input[type='checkbox']#descuento_pago_parcial").is(':checked') ? '1' : '0',
        'factura_texto1': $('#factura_texto1').val(),
        'factura_texto2': $('#factura_texto2').val(),
        'dias_pronto_pago': stringToNumberFloat($('#dias_pronto_pago').val()),
        'documento_referencia_agrupado': $('#documento_referencia_agrupado').val(),
        // 'tasa_pronto_pago': stringToNumberFloat($('#tasa_pronto_pago').val()),
        'id_concepto_pago_none': $('#id_concepto_pago_none').val(),
        'id_nit_por_defecto': $('#id_nit_por_defecto').val(),
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