var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
var generarEstadisticas = false;
var estadisticasExistente = false;
var estadisticas_table = null;
var channelEstadisticas = pusher.subscribe('informe-estadisticas-'+localStorage.getItem("notificacion_code"));

function estadisticasInit() {
    
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarEstadisticas = false;
    estadisticasExistente = false;

    $('#fecha_desde_estadisticas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_estadisticas').val(fechaDesde);

    estadisticas_table = $('#EstadisticasInformeTable').DataTable({
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
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader : {
            header : true,
            footer : true,
            headerOffset: 45
        },
        'rowCallback': function(row, data, index){
            var detallar = getDellarEstadisticas();
            if (data.total == 2) {
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (detallar == '1' && data.total == 1) {
                $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                return;
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'estadisticas',
            headers: headers,
            data: function( d ) {
                d.id_zona = $('#id_zona_estadisticas').val();
                d.id_concepto_facturacion = $('#id_concepto_estadisticas').val();
                d.id_nit = $('#id_nit_estadisticas').val();
                d.fecha_desde = $('#fecha_desde_estadisticas').val();
                d.fecha_hasta = $('#fecha_hasta_estadisticas').val();
                d.agrupar = $('#agrupado_estadisticas').val();
                d.detalle = getDellarEstadisticas();
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.numero_documento
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.nombre_completo
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    if (row.nit.apartamentos) {
                        return row.nit.apartamentos.slice(0,30);
                    }
                }
                return '';
            }},
            
            // {data: 'total_area', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            // {data: 'total_coheficiente', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'saldo_anterior', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'valor_intereses', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'factura', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {data: 'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
        ]
    });

    $('#id_nit_estadisticas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un cliente",
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

    $('#id_zona_estadisticas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Zona",
        allowClear: true,
        ajax: {
            url: 'api/zona-combo',
            headers: headers,
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

    $('#id_concepto_estadisticas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Concepto",
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
}

channelEstadisticas.bind('notificaciones', function(data) {
    console.log('entro');
    if(data.url_file){
        setTimeout(function(){
            console.log('mostro');
            window.open('https://'+data.url_file, "_blank");
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        },5000);
        return;
    }
    if(data.id_estadistica){
        $('#id_estadisticas_cargado').val(data.id_estadistica);
        loadEstadisticaById(data.id_estadistica);
        return;
    }
});

function loadEstadisticaById(id_estadistica) {
    var url = base_url + 'estadisticas-show?id='+id_estadistica;
    estadisticas_table.ajax.url(url).load(function(res) {
        
        if(res.success){
            $("#generarEstadisticas").show();
            $("#generarEstadisticasLoading").hide();
            $("#descargarExcelEstadisticas").show();
            $("#descargarExcelEstadisticasDisabled").hide();

            agregarToast('exito', 'Estadisticas generales cargado', 'Informe cargado con exito!', true);
            
            // mostrarTotalesCartera(res.totales);
        }
    });
}

$(document).on('click', '#generarEstadisticas', function () {
    generarEstadoActual = false;

    $("#generarEstadisticas").hide();
    $("#generarEstadisticasLoading").show();

    var url = base_url + 'estadisticas';
    url+= '?id_zona='+$('#id_zona_estadisticas').val();
    url+= '&id_concepto_facturacion='+$('#id_concepto_estadisticas').val();
    url+= '&id_nit='+$('#id_nit_estadisticas').val();
    url+= '&fecha_desde='+$('#fecha_desde_estadisticas').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_estadisticas').val();
    url+= '&agrupar='+$('#agrupado_estadisticas').val();
    url+= '&detalle='+getDellarEstadisticas();
    url+= '&generar='+generarEstadoActual;

    estadisticas_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar Estadisticas?',
                    text: "Estadisticas generadas anteriormente ¿Desea cargarlo?",
                    type: 'info',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Cargar',
                    cancelButtonText: 'Generar nuevo',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value){
                        $('#id_cartera_cargado').val(res.data);
                        loadEstadisticaById(res.data);
                    } else {
                        generarCartera = true;
                        GenerateCartera();
                    }
                })
            } else {
                agregarToast('info', 'Generando estadisticas generales', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });
});

$(document).on('click', '#descargarExcelEstadisticas', function () {
    $.ajax({
        url: base_url + 'estadisticas-excel',
        method: 'POST',
        data: JSON.stringify({id: $('#id_estadisticas_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
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
        agregarToast('error', 'Error al generar excel', errorsMsg);
    });
});

function getDellarEstadisticas() {
    if($("input[type='radio']#detallar_estadisticas1").is(':checked')) return '0';
    if($("input[type='radio']#detallar_estadisticas2").is(':checked')) return '1';

    return '0';
}