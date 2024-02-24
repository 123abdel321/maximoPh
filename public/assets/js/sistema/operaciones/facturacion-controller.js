var facturacion_table = null;

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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'facturacion',
        },
        columns: [
            {"data":'id_inmueble', visible: false},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.zona.nombre+' - '+row.inmueble.nombre;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.area;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.nit.numero_documento+ ' - '+row.nit.nombre_completo;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.tipo) {
                    return 'INQUILINO'
                }
                return 'PROPIETARIO';
            }},
            {"data":'porcentaje_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.concepto.nombre_concepto
                }
                return '';
            }},
            {"data":'valor_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'}
        ]
    });

    $('.water').hide();
    facturacion_table.ajax.reload();
    getTotalesFacturacion();
}

$(document).on('click', '#generateFacturacion', function () {
    $("#facturacionFormModal").modal('show');
});

$(document).on('click', '#saveFacturacion', function () {
    $("#saveFacturacionLoading").show();
    $("#saveFacturacion").hide();
    $("#cancelFacturacion").hide();

    $.ajax({
        url: base_url + 'facturacion',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#cancelFacturacion").show();
            $("#saveFacturacion").show();
            $("#saveFacturacionLoading").hide();
            $("#facturacionFormModal").modal('hide');
            facturacion_table.ajax.reload();
            getTotalesFacturacion();
            agregarToast('exito', 'Facturación exitosa', 'Facturación generada con exito!', true);
        }
    }).fail((err) => {
        $('#saveFacturacion').show();
        $("#cancelFacturacion").show();
        $('#saveFacturacionLoading').hide();
        getTotalesFacturacion();
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

function getTotalesFacturacion(){
    $.ajax({
        url: base_url + 'inmueble-total',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#textLoadingFacturacionCreate').hide();
        if(res.success){
            var dateText = generateTextYear(res.data.periodo_facturacion);

            $('#textFacturacionCreate').text('Generar facturación '+ dateText);
            $('#generateFacturacion').text('Generar facturación '+ dateText);

            var numero_registro_unidades = res.data.numero_registro_unidades;
            var numero_total_unidades = res.data.numero_total_unidades;
            var area_registro_m2 = res.data.area_registro_m2;
            var area_total_m2 = parseInt(res.data.area_total_m2);
            var valor_registro_coeficiente = res.data.valor_registro_coeficiente;
            var valor_registro_presupuesto = res.data.valor_registro_presupuesto;
            var valor_total_presupuesto = res.data.valor_total_presupuesto / 12;

            $('#validar_inmuebles_facturacion').text('Inmuebles totales: '+numero_registro_unidades+ ' de '+numero_total_unidades);
            $('#validar_area_facturacion').text('Area total: '+area_registro_m2+ ' de '+area_total_m2);
            $('#validar_coeficiente_facturacion').text('Coreficiente total: '+valor_registro_coeficiente+ '% de 100%');
            $('#validar_presupuesto_facturacion').text('Valor administracion total: '+new Intl.NumberFormat().format(valor_registro_presupuesto)+ ' de '+new Intl.NumberFormat().format(valor_total_presupuesto));

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

            var countB = new CountUp('area2_registrados_facturacion', 0, res.data.area_registro_m2);
                countB.start();

            var countC = new CountUp('coeficiente_registrados_facturacion', 0, res.data.valor_registro_coeficiente);
                countC.start();

            var countD = new CountUp('presupuesto_registrados_facturacion', 0, res.data.valor_registro_presupuesto);
                countD.start();
        }
    }).fail((err) => {
        $('#textLoadingFacturacionCreate').hide();
        agregarToast('error', 'Consulta errada', 'Error al consultar totales!');
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