function entornoInit() {

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        var numberEntorno = [
            'area_total_m2',
            'numero_total_unidades',
            'valor_total_presupuesto_year_actual',
            'porcentaje_intereses_mora',
        ];

        var checkEntorno = [
            'editar_valor_admon_inmueble',
            'validacion_estricta'
        ];

        var dateEntorno = [
            'periodo_facturacion',
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
    }
}

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        'area_total_m2': stringToNumberFloat($('#area_total_m2').val()),
        'numero_total_unidades': stringToNumberFloat($('#numero_total_unidades').val()),
        'valor_total_presupuesto_year_actual': stringToNumberFloat($('#valor_total_presupuesto_year_actual').val()),
        'porcentaje_intereses_mora': stringToNumberFloat($('#porcentaje_intereses_mora').val()),
        'periodo_facturacion': $('#periodo_facturacion').val(),
        'editar_valor_admon_inmueble': $("input[type='checkbox']#editar_valor_admon_inmueble").is(':checked') ? '1' : '',
        'validacion_estricta': $("input[type='checkbox']#validacion_estricta").is(':checked') ? '1' : '',
    };

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