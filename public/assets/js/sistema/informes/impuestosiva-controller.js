var fechaDesde = null;
var impuesto_iva_table = null;

function impuestosivaInit() {
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_impuesto_iva').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_impuesto_iva').val(fechaDesde);

    impuesto_iva_table = $('#ImpuestoIvaInformeTable').DataTable({
        pageLength: 15,
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
        'rowCallback': function(row, data, index){
            if (data.detalle == '') {
                $('td', row).css('background-color', 'rgb(214 231 246)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'black');
                return;
            }
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'impuestosiva',
            data: function ( d ) {
                d.fecha_desde = $("#fecha_desde_impuesto_iva").val();
                d.fecha_hasta = $("#fecha_hasta_impuesto_iva").val();
                d.id_nit = $("#id_nit_impuesto_iva").val();
                d.id_cecos = $("#id_cecos_impuesto_iva").val();
                d.id_cuenta = $("#id_cuenta_impuesto_iva").val();
                d.agrupar = $("#agrupar_impuesto_iva").val();
                d.detallar = $("input[type='radio']#detallar_impuesto_iva1").is(':checked') ? 'no' : 'si';
            }
        },
        columns: [
            {"data":'cuenta'},
            {"data":'nombre_cuenta'},
            {"data":'numero_documento'},
            {"data":'nombre_nit'},
            {"data":'nombre_comprobante'},
            {"data":'nombre_cecos'},
            {"data": "gasto_valor", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "iva_valor", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'fecha_manual'},
            {"data":'observacion'}
        ]
    });

    $('#id_nit_impuesto_iva').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un nit",
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
            url: 'api/inmueble-combo',
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
            },
        }
    });

    $('#id_cecos_impuesto_iva').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un centro de costos",
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
            url: base_url_erp + 'centro-costos/combo-centro-costo',
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

    $('#id_cuenta_impuesto_iva').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                    id_tipo_cuenta: [1]
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

$(document).on('click', '#generarImpuestoIva', function () {
    impuesto_iva_table.ajax.reload();
});
