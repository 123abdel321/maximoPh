let email_table = null;
let email_detalle_table = null;
let id_email_filter = null;
var $comboEmailNitFilter = null;

function emailInit() {

    let timeEmail = new Date();
    const fechaDesde = timeEmail.getFullYear()+'-'+("0" + (timeEmail.getMonth() + 1)).slice(-2)+'-'+("0" + (timeEmail.getDate())).slice(-2);

    $('#fecha_desde_email').val(timeEmail.getFullYear()+'-'+("0" + (timeEmail.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_email').val(fechaDesde);

    initTablesEmail();
    initSelect2Email();
}

function initTablesEmail() {
    email_table = $('#emailTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'email',
            data: function ( d ) {
                d.estado = $('#estado_email_filter').val();
                d.id_nit = $('#id_nit_email_filter').val();
                d.fecha_desde = $('#fecha_desde_email').val();
                d.fecha_hasta = $('#fecha_hasta_email').val();
            }
        },
        columns: [
            {"data":'email'},
            {"data": function (row, type, set){
                if (row.nit) {
                    return `${row.nit.numero_documento} - ${row.nit.nombre_completo}`;
                }
                return ``;
            }},
            {"data": function (row, type, set){
                if (row.contexto == 'emails.factura') {
                    return `Email Facturación`;
                }
                if (row.contexto == 'whatsapp.factura') {
                    return `Whatsapp Facturación`;
                }
                if (row.contexto == 'emails.welcome') {
                    return `Email Bienvenida`;
                }
                return row.contexto;
            }},
            {"data": function (row, type, set){
                if (row.status == 'en_cola') {
                    return `<span class="badge bg-secondary" style="margin-bottom: 0rem !important; min-width: 50px;">EN COLA</span>`;
                }
                if (row.status == 'enviado') {
                    return `<span class="badge bg-info" style="margin-bottom: 0rem !important; min-width: 50px;">ENVIADO</span>`;
                }
                if (row.status == 'abierto') {
                    return `<span class="badge bg-success" style="margin-bottom: 0rem !important; min-width: 50px;">ABIERTO</span>`;
                }
                if (row.status == 'rechazado') {
                    return `<span class="badge bg-danger" style="margin-bottom: 0rem !important; min-width: 50px;">RECHAZADO</span>`;
                }
                if (row.status == 'entregado') {
                    return `<span class="badge bg-dark" style="margin-bottom: 0rem !important; min-width: 50px;">ENTREGADO</span>`;
                }
                return `<span class="badge bg-dark" style="margin-bottom: 0rem !important; min-width: 50px;">NINGUNO</span>`;
            }},
            {"data":'fecha_creacion'},
            {"data":'fecha_edicion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="detalleemail_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info detalle-email" style="margin-bottom: 0rem !important; min-width: 50px;">Detalles</span>&nbsp;';
                    return html;
                }
            },
        ],
    });

    email_detalle_table = $('#emailDetalleTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'email-detalle',
            data: function ( d ) {
                d.id = id_email_filter;
            }
        },
        columns: [
            {"data": function (row, type, set){
                if (row.event == 'open') return `Abierto`;
                if (row.event == 'processed') return `Procesado`;
                if (row.event == 'delivered') return `Entregado`;
                if (row.event == 'bounce') return `Rebotado`;
                if (row.event == 'deferred') return `Diferido`;
                if (row.event == 'click') return `Click`;
                if (row.event == 'dropped') return `Abandonó`;
                if (row.event == 'spamreport') return `Informe de spam`;
                if (row.event == 'enviado') return `Enviado`;
                if (row.event == 'entregado') return `Entregado`;
                if (row.event == 'abierto') return `Abierto`;
                if (row.event == 'accepted') return `Aceptado`;
                return '';
            }},
            {"data":'fecha_creacion'}
        ],
    });

    if (email_table) {        
        email_table.on('click', '.detalle-email', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, email_table);
            
            id_email_filter = data.id;
            email_detalle_table.ajax.reload();
        
            $("#emailDetalleShowFormModal").modal('show');
        });
    };

    email_table.ajax.reload();
}

function initSelect2Email() {
    $comboEmailNitFilter = $('#id_nit_email_filter').select2({
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
}

$(document).on('click', '#reloadEmail', function () {
    $("#reloadEmailIconNormal").hide();
    $("#reloadEmailIconLoading").show();
    email_table.ajax.reload(function (res) {
        $("#reloadEmailIconNormal").show();
        $("#reloadEmailIconLoading").hide();
    }); 
});

$("#estado_email_filter").on('change', function(){
    email_table.ajax.reload();
});

$("#id_nit_email_filter").on('change', function(){
    email_table.ajax.reload();
});

$("#fecha_desde_email").on('change', function(){
    const fecha_desde = $("#fecha_desde_email").val();
    const fecha_hasta = $("#fecha_hasta_email").val();
    if (fecha_desde && fecha_hasta) {
        email_table.ajax.reload();
    }
});

$("#fecha_hasta_email").on('change', function(){
    const fecha_desde = $("#fecha_desde_email").val();
    const fecha_hasta = $("#fecha_hasta_email").val();
    if (fecha_desde && fecha_hasta) {
        email_table.ajax.reload();
    }
});