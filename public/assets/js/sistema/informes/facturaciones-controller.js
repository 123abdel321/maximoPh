var facturaiones_table = null;
var $comboPeriodoFacturaciones = null;

function facturacionesInit() {

    $('#id_nit_facturaciones').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
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

    $comboPeriodoFacturaciones = $('#periodo_facturaciones').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un periodo",
        ajax: {
            url: base_url + 'periodo-facturacion-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    facturaiones_table = $('#FacturacionesInformeTable').DataTable({
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'facturaciones',
            data: function ( d ) {
                d.periodo = formatoFechaFacturacion();
                d.id_nit = $("#id_nit_facturaciones").val();
                d.factura_fisica = $("input[type='checkbox']#nit_fisica_facturaciones").is(':checked') ? '1' : ''
            }
        },
        columns: [
            {"data":'numero_documento'},
            {"data": 'nombre_nit'},
            { data: 'saldo_anterior', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'saldo_final', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {
                "data": function (row, type, set){
                    return `<span id="imprimirfacturaciones_${row.id_nit}" href="javascript:void(0)" class="btn badge bg-gradient-success imprimir-facturaciones" style="margin-bottom: 0rem !important; min-width: 50px;">Imprimir</span>&nbsp;`;
                }
            },
        ]
    });

    if (facturaiones_table) {
        facturaiones_table.on('click', '.imprimir-facturaciones', function() {
            var id_nit = this.id.split('_')[1];
            window.open("/facturacion-show-pdf?id_nit="+id_nit+"&periodo="+formatoFechaFacturacion(), "_blank");
        });
    }

    if (periodo_facturaciones) {
        var dataPeriodo = {
            id: periodo_facturaciones.id,
            text: periodo_facturaciones.text
        };
        var newOption = new Option(dataPeriodo.text, dataPeriodo.id, false, false);
        $comboPeriodoFacturaciones.append(newOption).trigger('change');
        $comboPeriodoFacturaciones.val(dataPeriodo.id).trigger('change');
    }

    facturaiones_table.ajax.reload();
}

function formatoFechaFacturacion() {
    var periodo = $("#periodo_facturaciones").val();
    var fecha = '';
    fecha+= periodo[0]+periodo[1]+periodo[2]+periodo[3];
    fecha+= '-'+periodo[4]+periodo[5];
    fecha+= '-'+periodo[6]+periodo[7];
    return fecha;
}

$("#periodo_facturaciones").on('change', function(event) {
    facturaiones_table.ajax.reload();
});

$("#id_nit_facturaciones").on('change', function(event) {
    facturaiones_table.ajax.reload();
});