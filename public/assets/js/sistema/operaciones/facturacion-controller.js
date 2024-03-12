var nitsFacturados = 0;
var nitsFacturando = [];
var buscarTotales = false;
var totalesProcesando = {
    total_facturados: 0,
    valor_admon_facturados: 0,
    valor_otros_facturados: 0,
    valor_intereses_facturados: 0,
};
var detenerFacturacion = false;
var facturacion_table = null;
var facturacion_proceso_table = null;
var searchValueFacturacion = null;

function facturacionInit() {
    facturacion_table = $('#facturacionTable').DataTable({
        pageLength: 100,
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
            url: base_url + 'facturacion',
            data: function ( d ) {
                d.search = searchValueFacturacion;
            }
        },
        columns: [
            {"data":'nombre_inmueble', visible: false},
            {"data":'nombre_inmueble'},
            {"data":'area_inmueble'},
            {"data":'id_nit'},
            {"data": function (row, type, set){  
                if (row.tipo) {
                    return 'INQUILINO'
                }
                if (row.tipo_factura == 2) {
                    return;
                }
                if (row.tipo_factura == 0) {
                    return 'PROPIETARIO';
                }
                return ;
            }},
            {"data":'porcentaje_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'nombre_concepto'},
            {"data":'valor_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'}
        ],
        'rowCallback': function(row, data, index){
            if(data.tipo_factura == 1) {
                $('td', row).css('background-color', '#00d9ff29');
                return;
            }
            if (data.tipo_factura == 2) {
                $('td', row).css('background-color', '#065664');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        }
    });
    
    facturacion_proceso_table = $('#facturacionProcesoTable').DataTable({
        dom: '',
        pageLength: 200,
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ordering: false,
        columns: [
            {"data":'documento_nit'},
            {"data":'nombre_nit'},
            {"data":'numero_inmuebles'},
            {"data":'valor_inmuebles', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'total_intereses', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'total_cuotas_multas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            // {"data":'mensajes'},
            {
                data: 'estado',
                render: function (row, type, data){
                    if (data.estado == 1) {
                        return '<span class="badge rounded-pill bg-success">Facturado</span>';
                    }
                    if (data.estado == 2) {
                        return '<span class="badge rounded-pill bg-danger">Con errores</span>';
                    }
                    return '';
                }
            },
            {
                data: 'total_factura',
                render: function (row, type, data){
                    if (!data.estado) {
                        return '<span class="badge rounded-pill bg-secondary">Procesando <i class="fas fa-spinner fa-spin"></i></span>';
                    }
                    return new Intl.NumberFormat("ja-JP").format(data.total_factura);
                }, className: 'dt-body-right'
            },
        ]
    });

    $('.water').hide();
    facturacion_table.ajax.reload(function (res) {
        getTotalesFacturacion();
    }); 
    
}

$(document).on('click', '#generateFacturacion', function () {
    $("#facturacionFormModal").modal('show');
});

$(document).on('click', '#reprocesarFacturacion', function () {
    $.ajax({
        url: base_url + 'facturacion-proceso',
        data: {reprocesar: true},
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#detenerFacturacion").hide();
            $("#continuarFacturacion").hide();
            $("#confirmarFacturacion").hide();
            $("#reprocesarFacturacion").hide();
            $("#saveFacturacionLoading").hide();
            $("#tablas_facturacion_view").hide();
            $("#header_facturacion_view").hide();
            $("#totales_facturacion_view").hide();
            $("#confirmarFacturacionDisabled").hide();
            
            $("#detenerFacturacion").show();
            $("#saveFacturacion").show();
            $("#header_procesando_view").show();
            $("#tablas_procesando_view").show();
            $("#totales_procesando_facturacion_view").show();

            totalesProcesando = {
                total_facturados: 0,
                valor_admon_facturados: 0,
                valor_otros_facturados: 0,
                valor_intereses_facturados: 0,
            };

            facturacion_proceso_table.clear().draw();

            nitsFacturados = 0;
            nitsFacturando = res.data;
            setTotalesProcesando(res.totales);
            facturarNitIndividual();
        }
    }).fail((err) => {
        agregarToast('error', 'Facturación errada', 'Error al iniciar facturación');
    });
});

$(document).on('click', '#saveFacturacion', function () {
    $("#saveFacturacion").hide();
    $("#saveFacturacionLoading").show();
    $.ajax({
        url: base_url + 'facturacion-proceso',
        data: {reprocesar: false},
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#continuarFacturacion").hide();
            $("#saveFacturacionLoading").hide();
            $("#tablas_facturacion_view").hide();
            $("#header_facturacion_view").hide();
            $("#totales_facturacion_view").hide();
            $("#confirmarFacturacionDisabled").hide();
            
            $("#saveFacturacion").show();
            $("#detenerFacturacion").show();
            $("#header_procesando_view").show();
            $("#tablas_procesando_view").show();
            $("#totales_procesando_facturacion_view").show();

            $("#facturacionFormModal").modal('hide');

            nitsFacturados = 0;
            nitsFacturando = res.data;
            setTotalesProcesando(res.totales);
            facturarNitIndividual();
        }
    }).fail((err) => {
        agregarToast('error', 'Facturación errada', 'Error al iniciar facturación');
    });
});

$(document).on('click', '#detenerFacturacion', function () {
    detenerFacturacion = true;
    $('#detenerFacturacion').hide();
    $('#continuarFacturacion').show();
});

$(document).on('click', '#continuarFacturacion', function () {
    detenerFacturacion = false;
    $('#detenerFacturacion').show();
    $('#continuarFacturacion').hide();
    facturarNitIndividual ();
});

$(document).on('click', '#confirmarFacturacion', function () {
    $('#confirmarFacturacion').hide();
    $('#reprocesarFacturacion').hide();
    $('#confirmarFacturacionDisabled').show();

    $.ajax({
        url: base_url + 'facturacion-confirmar',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#tablas_procesando_view').hide();
            $('#header_procesando_view').hide();
            $('#totales_procesando_facturacion_view').hide();

            
            $('#header_facturacion_view').show();
            $('#tablas_facturacion_view').show();
            $('#totales_facturacion_view').show();

            facturacion_table.ajax.reload(function (res) {
                getTotalesFacturacion();
            });

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

function facturarNitIndividual () {

    if (detenerFacturacion) return;

    var continues = false;
    var calcular = false;
    var facturar = true;
    
    do {
        var dataNitFactura = nitsFacturando[nitsFacturados];

        facturacion_proceso_table.row.add(
            dataNitFactura
        ).draw(false);

        if (dataNitFactura.estado == 1) {
            calcular = true;
            nitsFacturados++;
            totalesProcesando.total_facturados++;
            totalesProcesando.valor_admon_facturados+= parseFloat(dataNitFactura.total_factura);
            totalesProcesando.valor_otros_facturados+= parseFloat(dataNitFactura.total_cuotas_multas);
            totalesProcesando.valor_intereses_facturados+= parseFloat(dataNitFactura.total_intereses);
        } else {
            continues = true;
        }
        
        if (nitsFacturados >= nitsFacturando.length) {
            facturar = false;
            continues = true;
        }
        
    } while (continues == false);

    if (calcular) actualizarTotalesProcesandoNull();

    if (!facturar) {
        $('#detenerFacturacion').hide();
        $('#continuarFacturacion').hide();
        $('#reprocesarFacturacion').show();
        $('#confirmarFacturacion').show();
        return;
    }

    $.ajax({
        url: base_url + 'facturacion-individual',
        method: 'POST',
        data: JSON.stringify(nitsFacturando[nitsFacturados]),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            var dataRow = res.data;
            facturacion_proceso_table.row(nitsFacturados).data(dataRow);
            nitsFacturados++;
            
            setTimeout(function(){
                document.getElementById("tablas_procesando_view").scrollTop = 9999999;
            },80);
            actualizarTotalesProcesando(dataRow);
            
            if (nitsFacturando.length >= nitsFacturados+1) {
                facturarNitIndividual();
            } else {
                if(res.success){
                    $('#detenerFacturacion').hide();
                    $('#continuarFacturacion').hide();
                    $('#reprocesarFacturacion').show();
                    $('#confirmarFacturacion').show();
                    agregarToast('exito', 'Facturación exitosa', 'Facturación generada con exito!', true);
                }
            }
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
}

function actualizarTotalesProcesando (dataRow) {

    var totalFacturados = totalesProcesando.total_facturados + 1;
    var countA = new CountUp('facturacion_proceso_facturas', totalesProcesando.total_facturados, totalFacturados, 0, 0.5);
        countA.start();

    var totalAdmonFacturados = totalesProcesando.valor_admon_facturados + dataRow.valor_inmuebles;
    var countB = new CountUp('facturacion_proceso_administracion', totalesProcesando.valor_admon_facturados, totalAdmonFacturados, 2, 0.5);
        countB.start();

    var totalOtrosFacturados = totalesProcesando.valor_otros_facturados + dataRow.total_cuotas_multas;
    var countC = new CountUp('facturacion_proceso_extras', totalesProcesando.valor_otros_facturados, totalOtrosFacturados, 2, 0.5);
        countC.start();

    var totalInteresesFacturados = totalesProcesando.valor_intereses_facturados + dataRow.total_intereses;
    var countD = new CountUp('facturacion_proceso_intereses', totalesProcesando.valor_intereses_facturados, totalInteresesFacturados, 2, 0.5);
        countD.start();

    totalesProcesando.total_facturados = totalFacturados;
    totalesProcesando.valor_admon_facturados = totalAdmonFacturados;
    totalesProcesando.valor_otros_facturados = totalOtrosFacturados;
    totalesProcesando.valor_intereses_facturados = totalInteresesFacturados;
}

function actualizarTotalesProcesandoNull () {
    var countA = new CountUp('facturacion_proceso_facturas', 0, totalesProcesando.total_facturados, 0, 0.5);
        countA.start();

    var countB = new CountUp('facturacion_proceso_administracion', 0, totalesProcesando.valor_admon_facturados, 2, 0.5);
        countB.start();

    var countC = new CountUp('facturacion_proceso_extras', 0, totalesProcesando.valor_otros_facturados, 2, 0.5);
        countC.start();

    var countD = new CountUp('facturacion_proceso_intereses', 0, totalesProcesando.valor_intereses_facturados, 2, 0.5);
        countD.start();
}

function setTotalesProcesando (totales) {
    $('#facturacion_proceso_administracion_totales').text('de '+new Intl.NumberFormat("ja-JP").format(totales.valor_inmuebles));
    $('#facturacion_proceso_facturas_totales').text('de '+totales.total_facturas);
}

function searchFacturacion (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValueFacturacion = $('#searchInputFacturacion').val();
    searchValueFacturacion = searchValueFacturacion+botonPrecionado;
    if(event.key == 'Backspace') searchValueFacturacion = searchValueFacturacion.slice(0, -1);

    facturacion_table.context[0].jqXHR.abort();
    facturacion_table.ajax.reload(function () {
        getTotalesFacturacion();
    });
}

function getTotalesFacturacion(){
    if (buscarTotales) {
        buscarTotales.abort();
    }
    buscarTotales = $.ajax({
        url: base_url + 'inmueble-total',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        validarFactura = false;
        $('#textLoadingFacturacionCreate').hide();
        if(res.success){
            var dateText = generateTextYear(res.data.periodo_facturacion);

            $('#textFacturacionCreate').text('GENERAR FACTURACIÓN '+ dateText);
            $('#generateFacturacion').text('GENERAR FACTURACIÓN '+ dateText);
            $('#div_total_multas_facturacion').hide();

            var numero_registro_unidades = res.data.numero_registro_unidades;
            var numero_total_unidades = res.data.numero_total_unidades;
            var area_registro_m2 = res.data.area_registro_m2;
            var area_total_m2 = parseInt(res.data.area_total_m2);
            var valor_registro_coeficiente = res.data.valor_registro_coeficiente;
            var valor_registro_presupuesto = res.data.valor_registro_presupuesto;
            var valor_total_presupuesto = res.data.valor_total_presupuesto / 12;
            var total_multas = res.data.total_multas;

            $('#validar_inmuebles_facturacion').text('Inmuebles totales: '+numero_registro_unidades+ ' de '+numero_total_unidades);
            $('#validar_area_facturacion').text('Area total: '+area_registro_m2+ ' de '+area_total_m2);
            $('#validar_coeficiente_facturacion').text('Coreficiente total: '+valor_registro_coeficiente+ '% de 100%');
            $('#validar_presupuesto_facturacion').text('Valor administracion total: '+new Intl.NumberFormat("ja-JP").format(valor_registro_presupuesto)+ ' de '+new Intl.NumberFormat("ja-JP").format(valor_total_presupuesto));
            if (total_multas) {
                $('#div_total_multas_facturacion').show();
                $('#text_total_multas_facturacion').text('Total multas: '+new Intl.NumberFormat("ja-JP").format(total_multas))    
            }
            
            if (numero_registro_unidades != numero_total_unidades) {
                $("#validar_inmuebles_facturacion_false").show();
                $("#validar_inmuebles_facturacion_true").hide();
            } else {
                $("#validar_inmuebles_facturacion_false").hide();
                $("#validar_inmuebles_facturacion_true").show();
            }

            if (area_registro_m2 != area_total_m2) {
                $("#validar_area_facturacion_false").show();
                $("#validar_area_facturacion_true").hide();
            } else {
                $("#validar_area_facturacion_false").hide();
                $("#validar_area_facturacion_true").show();
            }

            if (valor_registro_coeficiente != 100) {
                $("#validar_coeficiente_facturacion_false").show();
                $("#validar_coeficiente_facturacion_true").hide();
            } else {
                $("#validar_coeficiente_facturacion_false").hide();
                $("#validar_coeficiente_facturacion_true").show();
            }

            if (valor_registro_presupuesto != valor_total_presupuesto) {
                $("#validar_presupuesto_facturacion_false").show();
                $("#validar_presupuesto_facturacion_true").hide();
            } else {
                $("#validar_presupuesto_facturacion_false").hide();
                $("#validar_presupuesto_facturacion_true").show();
            }
            
            var countA = new CountUp('inmuebles_registrados_facturacion', 0, res.data.numero_registro_unidades);
                countA.start();

            var countB = new CountUp('area2_registrados_facturacion', 0, res.data.area_registro_m2, 2);
                countB.start();

            var countC = new CountUp('coeficiente_registrados_facturacion', 0, res.data.valor_registro_coeficiente, 2);
                countC.start();

            var countD = new CountUp('presupuesto_registrados_facturacion', 0, res.data.valor_registro_presupuesto);
                countD.start();
        }
    }).fail((err) => {
        validarFactura = false;
        $('#textLoadingFacturacionCreate').hide();
    });
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