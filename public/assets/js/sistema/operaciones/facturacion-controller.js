var cuotasData = [];
var inmueblesData = [];
var nitsFacturados = 0;
var nitsFacturando = [];
var facturandoPersona = false;
var detenerFacturacion = false;

function facturacionInit() {
    getFacturacionData();
    $('.water').hide();
}

function getFacturacionData() {
    $.ajax({
        url: base_url + 'facturacion-preview',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            nitsFacturando = res.data.nits;
            generarTablaPreview(res.data)
        }
    }).fail((err) => {
    });
}

function generarTablaPreview(data) {
    //FACTURACION
    var dateText = generateTextYear(data.periodo_facturacion);
    if (data.existe_facturacion) {
        $('#generateFacturacion').text('VALIDAR FACTURACIÓN '+ dateText);
    } else {
        $('#generateFacturacion').text('GENERAR FACTURACIÓN '+ dateText);
    }
    //INMUEBLES
    var tablaBefore = document.getElementById("facturacion-inmuebles-preview");
    if (tablaBefore) tablaBefore.remove();

    var inmuebles = data.inmuebles;
    var htmlInmuebles = ``;

    for (let index = 0; index < inmuebles.length; index++) {
        var inmueble = inmuebles[index];
        inmueble.total_causados = 0;
        inmueble.items_causados = 0;
        inmueble.diferencia = 0;
        inmueble.valor_total = parseFloat(inmueble.valor_total);
        inmueblesData.push(inmueble);
        var styles = 'style="color: black;"';
        if (inmueble.id_concepto_facturacion == 'total_inmuebles') {
            styles = 'style="background-color: rgb(6, 86, 100); color: white; font-weight: 600;"';
        }
        htmlInmuebles+= `<tr ${styles}>
            <td style="font-weight: 600;">${inmueble.concepto_facturacion}</td>
            <td style="text-align: end;">${inmueble.items}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(inmueble.valor_total)}</td>
            <td style="text-align: end;" id="inmueble_causado_${inmueble.id_concepto_facturacion}">0</td>
            <td style="text-align: end;" id="inmueble_diferencia_${inmueble.id_concepto_facturacion}">${new Intl.NumberFormat("ja-JP").format(inmueble.valor_total)}</td>
            <td id="inmueble_items_${inmueble.id_concepto_facturacion}">0</td>
        </tr>`;
    }

    var tbody = document.createElement('tbody');
    tbody.setAttribute("id", "facturacion-inmuebles-preview");
    tbody.innerHTML = [
        htmlInmuebles
    ].join('');
    document.getElementById('tabla_inmuebles_preview').insertBefore(tbody, null);

    //EXTRAS
    var tablaBefore = document.getElementById("facturacion-extras-preview");
    if (tablaBefore) tablaBefore.remove();

    var extras = data.cuotas;
    var htmlExtras = ``;

    for (let index = 0; index < extras.length; index++) {
        var extra = extras[index];
        extra.total_causados = 0;
        extra.items_causados = 0;
        extra.diferencia = 0;
        extra.valor_total = parseFloat(extra.valor_total);
        cuotasData.push(extra);
        var styles = 'style="color: black;"';
        if (extra.id_concepto_facturacion == 'total_extras') {
            styles = 'style="background-color: rgb(6, 86, 100); color: white; font-weight: 600;"';
        } else if (extra.id_concepto_facturacion == 'intereses') {
            styles = 'style="color: black;"';
        }
        
        htmlExtras+= `<tr ${styles}>
            <td style="font-weight: 600;">${extra.concepto_facturacion}</td>
            <td style="text-align: end;">${extra.items}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(extra.valor_total)}</td>
            <td style="text-align: end;" id="extras_causado_${extra.id_concepto_facturacion}">0</td>
            <td style="text-align: end;" id="extras_diferencia_${extra.id_concepto_facturacion}">${new Intl.NumberFormat("ja-JP").format(extra.valor_total)}</td>
            <td id="extras_items_${extra.id_concepto_facturacion}">0</td>
        </tr>`;
    }

    var tbody = document.createElement('tbody');
    tbody.setAttribute("id", "facturacion-extras-preview");
    tbody.innerHTML = [
        htmlExtras
    ].join('');
    document.getElementById('tabla_extras_preview').insertBefore(tbody, null);

    //ANTICIPOS
    $('#count_anticipos').text(new Intl.NumberFormat("ja-JP").format(data.count_anticipos));
    $('#total_anticipos').text(new Intl.NumberFormat("ja-JP").format(data.total_anticipos));
    //SALDO ANTERIOR
    $('#count_saldo_anterior').text(new Intl.NumberFormat("ja-JP").format(data.count_saldo_anterior));
    $('#total_saldo_anterior').text(new Intl.NumberFormat("ja-JP").format(data.saldo_anterior));

    var countA = new CountUp('inmuebles_registrados_facturacion', 0, data.numero_registro_unidades);
        countA.start();

    var countB = new CountUp('area2_registrados_facturacion', 0, data.area_registro_m2, 2);
        countB.start();

    var countC = new CountUp('coeficiente_registrados_facturacion', 0, data.valor_registro_coeficiente, 2);
        countC.start();

    var countD = new CountUp('presupuesto_registrados_facturacion', 0, data.valor_registro_presupuesto);
        countD.start();

    if (data.numero_registro_unidades != data.numero_total_unidades) {
        document.getElementById('inmuebles_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('inmuebles_registrados_facturacion').style.color = "#344767;";
    }

    if (data.area_registro_m2 != data.area_total_m2) {
        document.getElementById('area2_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('area2_registrados_facturacion').style.color = "#344767;";
    }

    if (data.valor_registro_coeficiente != 100) {
        document.getElementById('coeficiente_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('coeficiente_registrados_facturacion').style.color = "#344767;";
    }

    if (data.valor_registro_presupuesto != data.valor_total_presupuesto) {
        document.getElementById('presupuesto_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('presupuesto_registrados_facturacion').style.color = "#344767;";
    }
}

function facturarNitIndividual() {
    if (detenerFacturacion) return;

    $("#detenerFacturacion").html(`DETENER: ${nitsFacturando[nitsFacturados].documento_nit + ' ' + nitsFacturando[nitsFacturados].nombre_nit} <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin" style="font-size: 15px; vertical-align: middle;"></i>`);

    $("#detenerFacturacion").show();
    $("#generateFacturacion").hide();

    facturandoPersona = $.ajax({
        url: base_url + 'facturacion-individual',
        method: 'POST',
        data: JSON.stringify({id: nitsFacturando[nitsFacturados].id_nit}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            nitsFacturados++;
            actualizarTotales(JSON.parse(res.data.mensajes));
            if (nitsFacturando.length >= nitsFacturados+1) {
                facturarNitIndividual();
            } else {
                if(res.success){
                    $("#detenerFacturacion").hide();
                    $("#generateFacturacion").show();
                    // getFacturacionData();
                    agregarToast('exito', 'Facturación exitosa', 'Facturación generada con exito!', true);
                }
            }
        }
    }).fail((err) => {
    });
}

function actualizarTotales(data) {
    var extras = data.extras;
    var inmuebles = data.inmuebles;

    for (const [key, value] of Object.entries(extras)) {
        var index = cuotasData.findIndex(item => item.id_concepto_facturacion == key);
        var total = cuotasData.findIndex(item => item.id_concepto_facturacion == "total_extras");
        if (index == 0 || index) {
            cuotasData[index].items_causados+= parseFloat(value.items);
            cuotasData[index].total_causados+= parseFloat(value.valor_causado);
            cuotasData[total].items_causados+= parseFloat(value.items);
            cuotasData[total].total_causados+= parseFloat(value.valor_causado);

            var previoCountA = cuotasData[index].total_causados - value.valor_causado;
            var countA = new CountUp('extras_causado_'+key, previoCountA, cuotasData[index].total_causados, 0, 0.5);
                countA.start();

            var laterCountB =  cuotasData[index].valor_total - cuotasData[index].total_causados;
            var previoCountB = cuotasData[index].valor_total - (cuotasData[index].total_causados - value.valor_causado);
            var countB = new CountUp('extras_diferencia_'+key, previoCountB, laterCountB, 0, 0.5);
                countB.start();

            var previoCountC = cuotasData[index].items_causados - value.items;
            var countC = new CountUp('extras_items_'+key, previoCountC, cuotasData[index].items_causados, 0, 0.5);
                countC.start();

            var previoCountD = cuotasData[total].total_causados - value.valor_causado;
            var countD = new CountUp('extras_causado_total_extras', previoCountD, cuotasData[total].total_causados, 0, 0.5);
                countD.start();

            var laterCountE =  cuotasData[total].valor_total - cuotasData[index].total_causados;
            var previoCountE = cuotasData[total].valor_total - (cuotasData[index].total_causados - value.valor_causado);
            var countE = new CountUp('extras_diferencia_total_extras', previoCountE, laterCountE, 0, 0.5);
                countE.start();

            var previoCountF = cuotasData[total].items_causados - value.items;
            var countF = new CountUp('extras_items_total_extras', previoCountF, cuotasData[total].items_causados, 0, 0.5);
                countF.start();

            //VALIDAR ERRORES
            if (cuotasData[index].total_causados > cuotasData[index].valor_total) {
                document.getElementById('extras_causado_'+key).style.color = "red";
            } else if (cuotasData[index].valor_total == cuotasData[index].total_causados) {
                document.getElementById('extras_causado_'+key).style.color = "green";
            } else {
                document.getElementById('extras_causado_'+key).style.color = "black";
            }

            if (cuotasData[index].items_causados > cuotasData[index].items) {
                document.getElementById('extras_items_'+key).style.color = "red";
            } else if (cuotasData[index].items_causados == cuotasData[index].items) {
                document.getElementById('extras_items_'+key).style.color = "green";
            } else {
                document.getElementById('extras_items_'+key).style.color = "black";
            }

            var diferencia = cuotasData[index].valor_total - cuotasData[index].total_causados;
            if (diferencia < 0) {
                document.getElementById('extras_diferencia_'+key).style.color = "red";
            } else if (diferencia == 0){
                document.getElementById('extras_diferencia_'+key).style.color = "green";
            } else {
                document.getElementById('extras_diferencia_'+key).style.color = "black";
            }
        }
    }

    for (const [key, value] of Object.entries(inmuebles)) {
        var index = inmueblesData.findIndex(item => item.id_concepto_facturacion == key);
        var total = inmueblesData.findIndex(item => item.id_concepto_facturacion == "total_inmuebles");
        if (index == 0 || index) {
            inmueblesData[index].items_causados+= parseFloat(value.items);
            inmueblesData[index].total_causados+= parseFloat(value.valor_causado);
            inmueblesData[total].items_causados+= parseFloat(value.items);
            inmueblesData[total].total_causados+= parseFloat(value.valor_causado);

            var previoCountA = inmueblesData[index].total_causados - value.valor_causado;
            var countA = new CountUp('inmueble_causado_'+key, previoCountA, inmueblesData[index].total_causados, 0, 0.5);
                countA.start();

            var laterCountB =  inmueblesData[index].valor_total - inmueblesData[index].total_causados;
            var previoCountB = inmueblesData[index].valor_total - (inmueblesData[index].total_causados - value.valor_causado);
            var countB = new CountUp('inmueble_diferencia_'+key, previoCountB, laterCountB, 0, 0.5);
                countB.start();

            var previoCountC = inmueblesData[index].items_causados - value.items;
            var countC = new CountUp('inmueble_items_'+key, previoCountC, inmueblesData[index].items_causados, 0, 0.5);
                countC.start();

            var previoCountD = inmueblesData[total].total_causados - value.valor_causado;
            var countD = new CountUp('inmueble_causado_total_inmuebles', previoCountD, inmueblesData[total].total_causados, 0, 0.5);
                countD.start();

            var laterCountE =  inmueblesData[total].valor_total - inmueblesData[index].total_causados;
            var previoCountE = inmueblesData[total].valor_total - (inmueblesData[index].total_causados - value.valor_causado);
            var countE = new CountUp('inmueble_diferencia_total_inmuebles', previoCountE, laterCountE, 0, 0.5);
                countE.start();

            var previoCountF = inmueblesData[total].items_causados - value.items;
            var countF = new CountUp('inmueble_items_total_inmuebles', previoCountF, inmueblesData[total].items_causados, 0, 0.5);
                countF.start();

            //VALIDAR ERRORES
            if (inmueblesData[index].total_causados > inmueblesData[index].valor_total) {
                document.getElementById('inmueble_causado_'+key).style.color = "red";
            } else if (inmueblesData[index].valor_total == inmueblesData[index].total_causados) {
                document.getElementById('inmueble_causado_'+key).style.color = "green";
            } else {
                document.getElementById('inmueble_causado_'+key).style.color = "black";
            }

            if (inmueblesData[index].items_causados > inmueblesData[index].items) {
                document.getElementById('inmueble_items_'+key).style.color = "red";
            } else if (inmueblesData[index].items_causados == inmueblesData[index].items) {
                document.getElementById('inmueble_items_'+key).style.color = "green";
            } else {
                document.getElementById('inmueble_items_'+key).style.color = "black";
            }

            var diferencia = inmueblesData[index].valor_total - inmueblesData[index].total_causados;
            if (diferencia < 0) {
                document.getElementById('inmueble_diferencia_'+key).style.color = "red";
            } else if (diferencia == 0){
                document.getElementById('inmueble_diferencia_'+key).style.color = "green";
            } else {
                document.getElementById('inmueble_diferencia_'+key).style.color = "black";
            }
        }
    }
    
}

function generateTextYear(date){
    var dateSplit = date.split('-');
    var meses = {
        '01': 'ENERO',
        '02': 'FEBRERO',
        '03': 'MARZO',
        '04': 'ABRIL',
        '05': 'MAYO',
        '06': 'JUNIO',
        '07': 'JULIO',
        '08': 'AGOSTO',
        '09': 'SEPTIEMBRE',
        '10': 'OCTUBRE',
        '11': 'NOVIEMBRE',
        '12': 'DICIEMBRE',

    };

    return meses[dateSplit[1]]+' '+dateSplit[0];
}

$(document).on('click', '#reloadFacturacion', function () {
    getFacturacionData();
});

$(document).on('click', '#generateFacturacion', function () {
    facturarNitIndividual();
});

$(document).on('click', '#detenerFacturacion', function () {
    detenerFacturacion = true;
    facturandoPersona.abort();
    $('#volverFacturacion').show();
    $('#detenerFacturacion').hide();
    $('#continuarFacturacion').show();
});