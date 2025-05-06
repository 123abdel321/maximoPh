var cuotasData = [];
var inmueblesData = [];
var saldosTotales = [];
var nitsFacturados = 0;
var nitsFacturando = [];
var facturandoPersona = false;
var detenerFacturacion = false;
var countIntereses = 0;
var channelFacturacionRapida = pusher.subscribe('facturacion-rapida-'+localStorage.getItem("notificacion_code"));

function facturacionInit() {
    saldosTotales = [
        {items: 0, valor: 0, causado: 0, nuevosaldo: 0, items_causados: 0},// 0; ANTICIPOS
        {items: 0, valor: 0, causado: 0, nuevosaldo: 0, items_causados: 0},// 1; SALDO BASE
        {items: 0, valor: 0, causado: 0, nuevosaldo: 0, items_causados: 0},// 2; SALDO ACTUAL
    ];
    getFacturacionData();
    $('.water').hide();
    document.querySelector("#width_progress_bar").style.setProperty("background-color", "#075260", "important");
}

function getFacturacionData() {
    let url = 'facturacion-preview';
    if (causacion_mensual_rapida) url = 'facturacion-preview';

    document.getElementById('generateFacturacion').classList.add('disabled');

    $("#reloadFacturacionIconNormal").hide();
    $("#reloadFacturacionIconLoading").show();

    $.ajax({
        url: base_url + url,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#reloadFacturacionIconNormal").show();
            $("#reloadFacturacionIconLoading").hide();
            cuotasData = [];
            inmueblesData = [];
            nitsFacturados = 0;
            countIntereses = 0;
            nitsFacturando = res.data.nits;
            generarTablaPreview(res.data);
            document.getElementById('generateFacturacion').classList.remove('disabled');
            $('#confirmarFacturacion').hide();
        } else {
            $("#confirmarFacturacion").show();
            $("#reloadFacturacionIconNormal").show();
            $("#reloadFacturacionIconLoading").hide();
            var dateText = generateTextYear(res.data.periodo_facturacion);
            $('#generateFacturacion').text('FACTURACIÓN CON INFORMACIÓN '+ dateText);
            agregarToast('warning', 'Facturación con datos', 'Confirmar facturacion del mes '+ dateText, true, 5000);
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
        if (parseInt(causacion_mensual_rapida)) $('#generateFacturacion').text('FACTURACIÓN RAPIDA '+ dateText);
        else $('#generateFacturacion').text('GENERAR FACTURACIÓN '+ dateText);
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
            styles = 'style="background-color: rgb(9 129 151); color: white; font-weight: 600;"';
        }
        htmlInmuebles+= `<tr ${styles}>
            <td style="font-weight: 600;">${inmueble.concepto_facturacion}</td>
            <td style="text-align: end;">${inmueble.items}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(inmueble.saldo_anterior)}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(inmueble.valor_total)}</td>
            <td style="text-align: end;" id="inmueble_causado_${inmueble.id_concepto_facturacion}">0</td>
            <td style="text-align: end;" id="inmueble_diferencia_${inmueble.id_concepto_facturacion}">${new Intl.NumberFormat("ja-JP").format(inmueble.valor_total)}</td>
            <td style="text-align: end;" id="inmueble_items_${inmueble.id_concepto_facturacion}">0</td>
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
            styles = 'style="background-color: rgb(9 129 151); color: white; font-weight: 600;"';
        } else if (extra.id_concepto_facturacion == 'intereses') {
            styles = 'style="color: black;"';
        }
        
        htmlExtras+= `<tr ${styles}>
            <td style="font-weight: 600;">${extra.concepto_facturacion}</td>
            <td style="text-align: end;">${extra.items}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(extra.saldo_anterior)}</td>
            <td style="text-align: end;">${new Intl.NumberFormat("ja-JP").format(extra.valor_total)}</td>
            <td style="text-align: end;" id="extras_causado_${extra.id_concepto_facturacion}">0</td>
            <td style="text-align: end;" id="extras_diferencia_${extra.id_concepto_facturacion}">${new Intl.NumberFormat("ja-JP").format(extra.valor_total)}</td>
            <td style="text-align: end;" id="extras_items_${extra.id_concepto_facturacion}">0</td>
        </tr>`;
    }

    var tbody = document.createElement('tbody');
    tbody.setAttribute("id", "facturacion-extras-preview");
    tbody.innerHTML = [
        htmlExtras
    ].join('');
    document.getElementById('tabla_inmuebles_preview').insertBefore(tbody, null);

    //ANTICIPOS
    var countW = new CountUp('anticipos_items', 0, data.count_anticipos, 0, 0.5);
        countW.start();

    var countX = new CountUp('anticipos_valor', 0, data.total_anticipos, 0, 0.5);
        countX.start();

    var countY = new CountUp('saldo_items', 0, data.count_saldo_anterior, 0, 0.5);
        countY.start();

    var countZ = new CountUp('saldo_valor', 0, data.saldo_anterior, 0, 0.5);
        countZ.start();    

    // var countP = new CountUp('anticipos_nuevo_saldo', 0, data.total_anticipos, 0, 0.5);
    //     countP.start();
        
    // var countR = new CountUp('saldo_nuevo_saldo', 0, data.saldo_anterior, 0, 0.5);
    //     countR.start(); 

    var countY = new CountUp('saldo_causado', 0, 0, 0, 0.5);
        countY.start();

    var count1 = new CountUp('base_items', 0, data.count_saldo_base, 0, 0.5);
        count1.start(); 

    var count2 = new CountUp('base_valor', 0, data.saldo_base, 0, 0.5);
        count2.start();

    saldosTotales[0].items = data.count_anticipos;
    saldosTotales[0].valor = data.total_anticipos;
    saldosTotales[0].nuevosaldo = data.total_anticipos;

    saldosTotales[1].items = data.count_saldo_anterior;
    saldosTotales[1].valor = data.saldo_anterior;
    saldosTotales[1].nuevosaldo = data.saldo_anterior;

    saldosTotales[2].items = data.count_saldo_base;
    saldosTotales[2].valor = data.saldo_base;
    saldosTotales[2].nuevosaldo = data.saldo_base;

    var countA = new CountUp('inmuebles_registrados_facturacion', 0, data.numero_registro_unidades, 0, 0.5);
        countA.start();

    var countB = new CountUp('area2_registrados_facturacion', 0, data.area_registro_m2, 2, 0.5);
        countB.start();

    var countC = new CountUp('coeficiente_registrados_facturacion', 0, data.valor_registro_coeficiente, 2, 0.5);
        countC.start();

    var countD = new CountUp('presupuesto_registrados_facturacion', 0, data.valor_registro_presupuesto, 0, 0.5);
        countD.start();

    if (data.numero_registro_unidades != data.numero_total_unidades) {
        document.getElementById('inmuebles_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('inmuebles_registrados_facturacion').style.color = "#344767;";
    }

    if (parseFloat(data.area_registro_m2) != parseFloat(data.area_total_m2)) {
        document.getElementById('area2_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('area2_registrados_facturacion').style.color = "#344767;";
    }

    if (data.valor_registro_coeficiente != 100) {
        document.getElementById('coeficiente_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('coeficiente_registrados_facturacion').style.color = "#344767;";
    }
    
    if (data.valor_registro_presupuesto.toFixed() != data.valor_total_presupuesto.toFixed()) {
        document.getElementById('presupuesto_registrados_facturacion').style.color = "red";
    } else {
        document.getElementById('presupuesto_registrados_facturacion').style.color = "#344767;";
    }
}

function facturarNitIndividual() {
    if (detenerFacturacion) return;

    $("#progress_bar").show();
    $("#detenerFacturacion").html(`DETENER FACTURACION`);
    var porcentaje = (nitsFacturados / nitsFacturando.length) * 100
    $("#text_progress_bar").html(`${nitsFacturando[nitsFacturados].nombre_nit} - Facturados ${nitsFacturados} de ${nitsFacturando.length}`);
    var bar = document.querySelector("#width_progress_bar");
    bar.style.width = porcentaje + "%";
    bar.innerText = parseInt(porcentaje) + "%";

    $("#reloadFacturacion").hide();
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
            actualizarTotales(JSON.parse(res.data.mensajes), res.data);
            actualizarSaldos(res.data);
            if (nitsFacturando.length >= nitsFacturados+1) {
                facturarNitIndividual();
            } else {
                if(res.success){
                    $("#confirmarFacturacion").show();
                    $("#detenerFacturacion").hide();
                    $("#generateFacturacion").show();
                    $("#progress_bar").hide();
                    agregarToast('exito', 'Facturación exitosa', 'Facturación generada con exito!', true);
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function facturarRapidamente() {
    $("#reloadFacturacion").hide();
    $("#generateFacturacion").hide();
    $("#generateFacturacionLoading").show();

    $("#text_progress_bar").html(`ELIMINANDO DOCUMENTOS ...`);
    document.querySelector("#width_progress_bar").style.setProperty("background-color", "#c29802", "important");
    var bar = document.querySelector("#width_progress_bar");
    bar.style.width = 100 + "%";

    $("#progress_bar").show();
    getFacturacionData();
    
    facturandoPersona = $.ajax({
        url: base_url + 'facturacion-asyncrona',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            agregarToast('info', 'Generando facturaciones', 'En un momento se le notificará cuando haya finalizado ...', true );
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);

        $("#progress_bar").hide();
        $("#generateFacturacion").show();
        $("#generateFacturacionLoading").hide();
        
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

channelFacturacionRapida.bind('notificaciones', function(data) {
    
    if (data.action == 2) {
        $("#text_progress_bar").html(`ORGANIZANDO DATOS ...`);
        document.querySelector("#width_progress_bar").style.setProperty("background-color", "#02c2ab", "important");
        return;
    }

    if (data.action == 3) {
        $("#text_progress_bar").html(`GENERANDO DOCUMENTOS CONTABLES ...`);
        document.querySelector("#width_progress_bar").style.setProperty("background-color", "#0250c2", "important");

        var dataGeneral = data.dataGeneral
        if (dataGeneral) {
            actualizarTotales(dataGeneral, {
                'valor_anticipos': dataGeneral.valor_anticipos,
                'valor': dataGeneral.valor,
            });
            actualizarSaldos({
                'valor_anticipos': dataGeneral.valor_anticipos,
                'valor': dataGeneral.valor,
            });
        }
        return;
    }

    if (data.action == 4) {
        $("#progress_bar").hide();
        $("#text_progress_bar").html(``);
        $("#reloadFacturacion").show();
        $("#generateFacturacion").show();
        $("#confirmarFacturacion").show();
        $("#generateFacturacionLoading").hide();
        agregarToast('exito', 'Facturación exitosa', 'Facturación rapida finalizada con exito!', true);
        return;
    }

    if (data.action == 5) {
        var errorsMsg = "";
        var mensaje = data.message;
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

        getFacturacionData();

        $("#progress_bar").hide();
        $("#text_progress_bar").html(``);
        $("#reloadFacturacion").show();
        $("#generateFacturacion").show();
        $("#confirmarFacturacion").hide();
        $("#generateFacturacionLoading").hide();

        agregarToast('error', 'Facturación errada', errorsMsg);

        return;
    }
    
});

function actualizarTotales(data, factura) {
    var extras = data.extras;
    var inmuebles = data.inmuebles;

    if (factura.count_intereses) {
        countIntereses++;
    }
    for (const [key, value] of Object.entries(extras)) {

        var index = cuotasData.findIndex(item => item.id_concepto_facturacion == key);
        var total = cuotasData.findIndex(item => item.id_concepto_facturacion == "total_extras");

        if (index >= 0) {
            if (cuotasData[index].id_concepto_facturacion == "intereses" && value.items) {
                if (cuotasData[index].items != 0) {
                    if (cuotasData[index].items > 1) {
                        cuotasData[index].items_causados = value.items;
                        cuotasData[total].items_causados = value.items;
                    } else {
                        cuotasData[index].items_causados+= 1;
                        cuotasData[total].items_causados+= 1;
                    }
                }
            } else {
                cuotasData[index].items_causados+= parseFloat(value.items);
                cuotasData[total].items_causados+= parseFloat(value.items); 
            }

            cuotasData[index].total_causados+= parseFloat(value.valor_causado);
            cuotasData[total].total_causados+= parseFloat(value.valor_causado);

            var previoCountA = cuotasData[index].total_causados - value.valor_causado;
            var countA = new CountUp('extras_causado_'+key, previoCountA, cuotasData[index].total_causados, 0, 0.5);
                countA.start();

            var laterCountB =  cuotasData[index].valor_total - cuotasData[index].total_causados;
            var previoCountB = cuotasData[index].valor_total - (cuotasData[index].total_causados - parseFloat(value.valor_causado));
            var countB = new CountUp('extras_diferencia_'+key, previoCountB, laterCountB, 0, 0.5);
                countB.start();

            var previoCountC = cuotasData[index].items_causados - value.items;
            var countC = new CountUp('extras_items_'+key, previoCountC, cuotasData[index].items_causados, 0, 0.5);
                countC.start();

            var previoCountD = cuotasData[total].total_causados - parseFloat(value.valor_causado);
            var countD = new CountUp('extras_causado_total_extras', previoCountD, cuotasData[total].total_causados, 0, 0.5);
                countD.start();

            var laterCountE =  cuotasData[total].valor_total - cuotasData[total].total_causados;
            var previoCountE = cuotasData[total].valor_total - cuotasData[total].total_causados + parseFloat(value.valor_causado);
            var countE = new CountUp('extras_diferencia_total_extras', previoCountE, laterCountE, 0, 0.5);
                countE.start();

            var previoCountF = cuotasData[total].items_causados - value.items;
            var countF = new CountUp('extras_items_total_extras', previoCountF, cuotasData[total].items_causados, 0, 0.5);
                countF.start();

            //VALIDAR ERRORES
            if (parseInt(cuotasData[index].total_causados) > parseInt(cuotasData[index].valor_total)) {
                document.getElementById('extras_causado_'+key).style.color = "red";
            } else if (parseInt(cuotasData[index].valor_total) == parseInt(cuotasData[index].total_causados)) {
                document.getElementById('extras_causado_'+key).style.color = "#008000";
            } else {
                document.getElementById('extras_causado_'+key).style.color = "black";
            }

            if (cuotasData[index].items_causados > cuotasData[index].items) {
                document.getElementById('extras_items_'+key).style.color = "red";
            } else if (cuotasData[index].items_causados == cuotasData[index].items) {
                document.getElementById('extras_items_'+key).style.color = "#008000";
            } else {
                document.getElementById('extras_items_'+key).style.color = "black";
            }

            var diferencia = parseInt(cuotasData[index].valor_total - cuotasData[index].total_causados);
            if (diferencia < 0) {
                document.getElementById('extras_diferencia_'+key).style.color = "red";
            } else if (diferencia == 0){
                document.getElementById('extras_diferencia_'+key).style.color = "#008000";
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
            var countA = new CountUp('inmueble_causado_'+key, previoCountA, parseInt(parseFloat(inmueblesData[index].total_causados).toFixed()), 0, 0.5);
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

            var laterCountE = inmueblesData[total].valor_total - inmueblesData[total].total_causados;
            var previoCountE =  inmueblesData[total].valor_total - inmueblesData[total].total_causados + inmueblesData[index].total_causados;
            var countE = new CountUp('inmueble_diferencia_total_inmuebles', previoCountE, laterCountE, 0, 0.5);
                countE.start();

            var previoCountF = inmueblesData[total].items_causados - value.items;
            var countF = new CountUp('inmueble_items_total_inmuebles', previoCountF, inmueblesData[total].items_causados, 0, 0.5);
                countF.start();

            //VALIDAR ERRORES
            if (parseInt(parseFloat(inmueblesData[index].total_causados).toFixed()) > parseInt(inmueblesData[index].valor_total)) {
                document.getElementById('inmueble_causado_'+key).style.color = "red";
            } else if (parseInt(inmueblesData[index].valor_total) == parseInt(parseFloat(inmueblesData[index].total_causados).toFixed())) {
                document.getElementById('inmueble_causado_'+key).style.color = "#008000";
            } else {
                document.getElementById('inmueble_causado_'+key).style.color = "black";
            }

            if (inmueblesData[index].items_causados > inmueblesData[index].items) {
                document.getElementById('inmueble_items_'+key).style.color = "red";
            } else if (parseInt(inmueblesData[index].items_causados) == parseInt(inmueblesData[index].items)) {
                document.getElementById('inmueble_items_'+key).style.color = "#008000";
            } else {
                document.getElementById('inmueble_items_'+key).style.color = "black";
            }

            var diferencia = parseInt(inmueblesData[index].valor_total) - parseInt(parseFloat(inmueblesData[index].total_causados).toFixed());
            if (diferencia < 0) {
                document.getElementById('inmueble_diferencia_'+key).style.color = "red";
            } else if (diferencia == 0){
                document.getElementById('inmueble_diferencia_'+key).style.color = "#008000";
            } else {
                document.getElementById('inmueble_diferencia_'+key).style.color = "black";
            }
        }
    }
}

function actualizarSaldos(data) {

    var totalValorAnticipo = parseFloat(data.valor_anticipos);
    if (totalValorAnticipo > parseFloat(data.valor)) {
        totalValorAnticipo = parseFloat(data.valor)
    }

    if (totalValorAnticipo) {

        saldosTotales[0].items_causados+= 1;
        saldosTotales[0].causado+= totalValorAnticipo;
        saldosTotales[0].nuevosaldo-= totalValorAnticipo;
        
        var countW = new CountUp('anticipos_items_nuevo', saldosTotales[0].items_causados - 1, saldosTotales[0].items_causados, 0, 0.5);
            countW.start();
    
        var countX = new CountUp('anticipos_causado', saldosTotales[0].causado - totalValorAnticipo, saldosTotales[0].causado, 0, 0.5);
            countX.start();

        var countP = new CountUp('anticipos_nuevo_saldo', saldosTotales[0].nuevosaldo + totalValorAnticipo, saldosTotales[0].nuevosaldo, 0, 0.5);
            countP.start();
    }

    if (saldosTotales[0].items < 0) {
        document.getElementById('anticipos_items_nuevo').style.color = "red";
    } else {
        document.getElementById('anticipos_items_nuevo').style.color = "#344767;";
    }

    if (saldosTotales[0].causado < 0) {
        document.getElementById('anticipos_causado').style.color = "red";
    } else {
        document.getElementById('anticipos_causado').style.color = "#344767;";
    }

    if (saldosTotales[0].nuevosaldo < 0) {
        document.getElementById('anticipos_nuevo_saldo').style.color = "red";
    } else {
        document.getElementById('anticipos_nuevo_saldo').style.color = "#344767;";
    }

    var totalY = parseFloat(data.valor);
    var causadoY = totalValorAnticipo;

    if (totalY) {

        saldosTotales[1].items_causados+= 1;
        saldosTotales[1].causado+= (totalY - causadoY);
        saldosTotales[1].nuevosaldo+= (totalY - causadoY);

        var countY = new CountUp('saldo_items_nuevo', saldosTotales[1].items_causados - 1, saldosTotales[1].items_causados, 0, 0.5);
            countY.start();
    
        var countZ = new CountUp('saldo_causado', saldosTotales[1].causado - (totalY - causadoY), saldosTotales[1].causado, 0, 0.5);
            countZ.start();

        var countR = new CountUp('saldo_nuevo_saldo', saldosTotales[1].nuevosaldo - (totalY - causadoY), saldosTotales[1].nuevosaldo, 0, 0.5);
            countR.start();

        if (saldosTotales[1].causado < 0) {
            document.getElementById('saldo_causado').style.color = "red";
        } else{
            document.getElementById('saldo_causado').style.color = "#344767;";
        }

        if (saldosTotales[1].nuevosaldo < 0) {
            document.getElementById('saldo_nuevo_saldo').style.color = "red";
        } else{
            document.getElementById('saldo_nuevo_saldo').style.color = "#344767;";
        }

        // saldosTotales[2].items_causados+= 1;
        // saldosTotales[2].causado+= (totalY - causadoY);
        // saldosTotales[2].nuevosaldo+= (totalY - causadoY);
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
    detenerFacturacion = false;
    if (parseInt(causacion_mensual_rapida)) facturarRapidamente();
    else facturarNitIndividual();
});

$(document).on('click', '#detenerFacturacion', function () {
    detenerFacturacion = true;
    facturandoPersona.abort();
    document.querySelector("#width_progress_bar").style.setProperty("background-color", "#fd7e14", "important");
    $('#generateFacturacion').hide();
    $('#continuarFacturacion').show();
    $('#detenerFacturacion').hide();
    $('#continuarFacturacion').show();
});

$(document).on('click', '#continuarFacturacion', function () {
    detenerFacturacion = false;
    document.querySelector("#width_progress_bar").style.setProperty("background-color", "#075260", "important");
    $('#detenerFacturacion').show();
    $('#continuarFacturacion').hide();
    facturarNitIndividual ();
});

$(document).on('click', '#confirmarFacturacion', function () {
    $('#detenerFacturacion').hide();
    $('#generateFacturacion').hide();
    $('#confirmarFacturacion').hide();
    $('#confirmarFacturacionDisabled').show();

    $.ajax({
        url: base_url + 'facturacion-confirmar',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            saldosTotales = null;
            saldosTotales = [
                {items: 0, valor: 0, causado: 0, nuevosaldo: 0},// 0; ANTICIPOS
                {items: 0, valor: 0, causado: 0, nuevosaldo: 0},// 1; SALDO BASE
                {items: 0, valor: 0, causado: 0, nuevosaldo: 0},// 2; SALDO ACTUAL
            ];
            cuotasData = [];
            inmueblesData = [];
            getFacturacionData();
            $('#reloadFacturacion').show();
            $('#generateFacturacion').show();
            $('#confirmarFacturacionDisabled').hide();
            agregarToast('exito', 'Facturación exitosa', 'Facturación confirmada con exito!', true);
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
        agregarToast('error', 'Facturación errada', errorsMsg);
    });
});