var email_eco_table = null;
var whatsapp_eco_table = null;
var id_email_eco_filter = null;
var id_whatsapp_eco_filter = null;
var $comboEmailEcoEmail = null;
var $comboEmailEcoWhatsapp = null;
var email_eco_detalle_table = null;
var whatsapp_eco_detalle_table = null;


function notificacionesInit() {
    console.log("notificacionesInit");
    initFechasEco();
    initTablesEco();
    initCombosEco();

    $('.water').hide();
}

function initFechasEco() {
    var fechaEcoNow = new Date();
    var fechaDesde = fechaEcoNow.getFullYear()+'-'+("0" + (fechaEcoNow.getMonth() + 1)).slice(-2)+'-'+("0" + (fechaEcoNow.getDate())).slice(-2);

    $('#fecha_desde_eco_email').val(fechaEcoNow.getFullYear()+'-'+("0" + (fechaEcoNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_eco_email').val(fechaDesde);

    $('#fecha_desde_eco_whatsapp').val(fechaEcoNow.getFullYear()+'-'+("0" + (fechaEcoNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_eco_whatsapp').val(fechaDesde);
}

function initTablesEco() {
    email_eco_table = $('#emailEcoTable').DataTable({
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
            headers: {
                "Authorization": tokenEcoNotificaciones,
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            // url: base_url_erp + 'email/list',
            url: 'https://eco.portafolioerp.com/api/email/list',
            data: function ( d ) {
                d.estado = $('#estado_email_filter').val();
                d.id_nit = $('#id_nit_email_filter').val();
                d.fecha_desde = $('#fecha_desde_email').val();
                d.fecha_hasta = $('#fecha_hasta_email').val();
            }
        },
        columns: [
            {"data": 'id'},
            {"data":'email'},
            {"data": function (row, type, set){
                if (row.filter_metadata) {
                    return row.filter_metadata.nombre_completo
                }
                return '';
            }},
            {"data":'contexto'},
            {
                "data": function (row, type, set){
                    
                    const baseStyle = 'style="margin-bottom: 0rem !important; min-width: 90px;"';
                    const iconStyle = 'style="margin-right: 5px;"';

                    if (row.status == 'en_cola' || row.status == 'pendiente') {// 🟡 EN COLA / PENDIENTE: Estado intermedio, esperando (fa-clock)
                        return `<span class="badge bg-warning rounded-pill" ${baseStyle}>
                                    <i class="fas fa-clock" ${iconStyle}></i> EN COLA
                                </span>`;
                    }
                    if (row.status == 'enviado') {// 🔵 ENVIADO: Indica que salió de nuestro sistema (fa-paper-plane)
                        return `<span class="badge bg-primary rounded-pill" ${baseStyle}>
                                    <i class="fas fa-paper-plane" ${iconStyle}></i> ENVIADO
                                </span>`;
                    }
                    if (row.status == 'abierto') {// 🟢 ABIERTO: El usuario lo vio (fa-envelope-open)
                        return `<span class="badge bg-success rounded-pill" ${baseStyle}>
                                    <i class="fas fa-envelope-open" ${iconStyle}></i> ABIERTO
                                </span>`;
                    }
                    if (row.status == 'entregado') {// 🟢 ENTREGADO: Entregado al buzón (fa-check-circle)
                        return `<span class="badge bg-success rounded-pill" ${baseStyle}>
                                    <i class="fas fa-check-circle" ${iconStyle}></i> ENTREGADO
                                </span>`;
                    }
                    if (row.status == 'rechazado' || row.status == 'fallido') {// 🔴 RECHAZADO / FALLIDO: Error o rechazo (fa-times-circle)
                        return `<span class="badge bg-danger rounded-pill" ${baseStyle}>
                                    <i class="fas fa-times-circle" ${iconStyle}></i> RECHAZADO
                                </span>`;
                    }
                    if (row.status == 'diferido') {// 🟡 EN COLA / PENDIENTE: Estado intermedio, esperando (fa-clock)
                        return `<span class="badge bg-warning rounded-pill" ${baseStyle}>
                                    <i class="fas fa-clock" ${iconStyle}></i> DIFERIDO
                                </span>`;
                    }
                    // ⚫ POR DEFECTO: Estado desconocido (fa-question-circle)
                    return `<span class="badge bg-dark rounded-pill" ${baseStyle}>
                                <i class="fas fa-question-circle" ${iconStyle}></i> DESCONOCIDO
                            </span>`;
                }
            },
            {"data":'fecha_creacion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= `
                        <span
                            id="detalleemail_${row.id}"
                            href="javascript:void(0)"
                            class="btn badge bg-gradient-info detalle-email-eco"
                            style="margin-bottom: 0rem !important; min-width: 50px;"
                        >
                            Detalles
                        </span>&nbsp;`;
                    return html;
                }
            },
        ],
    });

    whatsapp_eco_table = $('#whatsappEcoTable').DataTable({
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
            headers: {
                "Authorization": tokenEcoNotificaciones,
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            // url: base_url_erp + 'email/list',
            url: 'https://eco.portafolioerp.com/api/whatsapp/list',
            data: function ( d ) {
                d.estado = $('#estado_eco_whatsapp').val();
                d.id_nit = $('#id_nit_eco_whatsapp').val();
                d.fecha_desde = $('#fecha_desde_eco_whatsapp').val();
                d.fecha_hasta = $('#fecha_hasta_eco_whatsapp').val();
            }
        },
        columns: [
            {"data": 'id'},
            {"data":'phone'},
            {"data": function (row, type, set){
                if (row.filter_metadata) {
                    return row.filter_metadata.nombre_completo
                }
                return '';
            }},
            {"data":'contexto'},
            {
                "data": function (row, type, set){
                    
                    const baseStyle = 'style="margin-bottom: 0rem !important; min-width: 90px;"';
                    const iconStyle = 'style="margin-right: 5px;"';

                    if (row.status == 'en_cola' || row.status == 'pendiente') {// 🟡 EN COLA / PENDIENTE: Estado intermedio, esperando (fa-clock)
                        return `<span class="badge bg-warning rounded-pill" ${baseStyle}>
                                    <i class="fas fa-clock" ${iconStyle}></i> EN COLA
                                </span>`;
                    }
                    if (row.status == 'enviado') {// 🔵 ENVIADO: Indica que salió de nuestro sistema (fa-paper-plane)
                        return `<span class="badge bg-primary rounded-pill" ${baseStyle}>
                                    <i class="fas fa-paper-plane" ${iconStyle}></i> ENVIADO
                                </span>`;
                    }
                    if (row.status == 'abierto') {// 🟢 ABIERTO: El usuario lo vio (fa-envelope-open)
                        return `<span class="badge bg-success rounded-pill" ${baseStyle}>
                                    <i class="fas fa-envelope-open" ${iconStyle}></i> ABIERTO
                                </span>`;
                    }
                    if (row.status == 'entregado') {// 🟢 ENTREGADO: Entregado al buzón (fa-check-circle)
                        return `<span class="badge bg-success rounded-pill" ${baseStyle}>
                                    <i class="fas fa-check-circle" ${iconStyle}></i> ENTREGADO
                                </span>`;
                    }
                    if (row.status == 'rechazado' || row.status == 'fallido') {// 🔴 RECHAZADO / FALLIDO: Error o rechazo (fa-times-circle)
                        return `<span class="badge bg-danger rounded-pill" ${baseStyle}>
                                    <i class="fas fa-times-circle" ${iconStyle}></i> RECHAZADO
                                </span>`;
                    }
                    // ⚫ POR DEFECTO: Estado desconocido (fa-question-circle)
                    return `<span class="badge bg-dark rounded-pill" ${baseStyle}>
                                <i class="fas fa-question-circle" ${iconStyle}></i> DESCONOCIDO
                            </span>`;
                }
            },
            {"data":'fecha_creacion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= `
                        <span
                            id="detalleemail_${row.id}"
                            href="javascript:void(0)"
                            class="btn badge bg-gradient-info detalle-whatsapp-eco"
                            style="margin-bottom: 0rem !important; min-width: 50px;"
                        >
                            Detalles
                        </span>&nbsp;`;
                    return html;
                }
            },
        ],
    });

    email_eco_detalle_table = $('#emailEcoDetalleTable').DataTable({
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
            headers: {
                "Authorization": tokenEcoNotificaciones,
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            // url: base_url_erp + 'email/detail',
            url: 'https://eco.portafolioerp.com/api/email/detail',
            data: function ( d ) {
                d.id = $("#id_email_eco").val();;
            }
        },
        columns: [
            // 1. COLUMNA EVENTO (Mejorada con Badges e Íconos)
            {"data": function (row, type, set){
                const baseIconStyle = 'style="margin-right: 5px;"';
                
                // Mapeo de eventos a íconos, colores y textos
                const events = {
                    'open': { text: 'Abierto', color: 'success', icon: 'fa-envelope-open' },
                    'delivered': { text: 'Entregado', color: 'primary', icon: 'fa-check-circle' },
                    'processed': { text: 'Procesado', color: 'info', icon: 'fa-cogs' },
                    'bounce': { text: 'Rebotado', color: 'danger', icon: 'fa-times-circle' },
                    'deferred': { text: 'Diferido', color: 'warning', icon: 'fa-clock' },
                    'click': { text: 'Click', color: 'success', icon: 'fa-mouse-pointer' },
                    'dropped': { text: 'Abandonó', color: 'secondary', icon: 'fa-minus-circle' },
                    'spamreport': { text: 'Spam', color: 'danger', icon: 'fa-exclamation-triangle' },
                    // Eventos de tu API
                    'enviado': { text: 'Enviado (API)', color: 'info', icon: 'fa-paper-plane' },
                    'entregado': { text: 'Entregado (API)', color: 'primary', icon: 'fa-check-circle' },
                    'abierto': { text: 'Abierto (API)', color: 'success', icon: 'fa-envelope-open' },
                    'Error API (Validación)': { text: 'Fallo Validación', color: 'danger', icon: 'fa-exclamation-circle' },
                    'Error API (Fatal)': { text: 'Fallo Fatal', color: 'danger', icon: 'fa-bug' },
                };

                const eventData = events[row.event] || { text: row.event, color: 'dark', icon: 'fa-question-circle' };
                
                // Usando íconos de Font Awesome (asumo que está cargado)
                return `<span class="badge bg-${eventData.color} rounded-pill" style="min-width: 100px;">
                            <i class="fa ${eventData.icon}" ${baseIconStyle}></i> 
                            ${eventData.text}
                        </span>`;
            }},
            
            // 2. COLUMNA MENSAJE (Muestra el mensaje de error o el estado si no hay error)
            {"data": function (row, type, set){
                if (row.error_message && row.error_message.length > 0) {
                    // Si hay error, mostrarlo en rojo/advertencia
                    return row.error_message;
                }
                // Si es un evento de éxito, muestra el tipo de evento
                return row.event;
            }},
            
            // 4. COLUMNA FECHA/HORA
            {"data": 'fecha_creacion'}
        ]
    });

    whatsapp_eco_detalle_table = $('#whatsappEcoDetalleTable').DataTable({
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
            headers: {
                "Authorization": tokenEcoNotificaciones,
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            // url: base_url_erp + 'email/detail',
            url: 'https://eco.portafolioerp.com/api/whatsapp/detail',
            data: function ( d ) {
                d.id = $("#id_whatsapp_eco").val();
            }
        },
        columns: [
            // 1. COLUMNA EVENTO (Mejorada con Badges e Íconos)
            {"data": function (row, type, set){
                const baseIconStyle = 'style="margin-right: 5px;"';
                
                // Mapeo de eventos a íconos, colores y textos
                const events = {
                    'open': { text: 'Abierto', color: 'success', icon: 'fa-envelope-open' },
                    'delivered': { text: 'Entregado', color: 'primary', icon: 'fa-check-circle' },
                    'processed': { text: 'Procesado', color: 'info', icon: 'fa-cogs' },
                    'bounce': { text: 'Rebotado', color: 'danger', icon: 'fa-times-circle' },
                    'deferred': { text: 'Diferido', color: 'warning', icon: 'fa-clock' },
                    'click': { text: 'Click', color: 'success', icon: 'fa-mouse-pointer' },
                    'dropped': { text: 'Abandonó', color: 'secondary', icon: 'fa-minus-circle' },
                    'spamreport': { text: 'Spam', color: 'danger', icon: 'fa-exclamation-triangle' },
                    // Eventos de tu API
                    'enviado': { text: 'Enviado', color: 'info', icon: 'fa-paper-plane' },
                    'entregado': { text: 'Entregado', color: 'primary', icon: 'fa-check-circle' },
                    'abierto': { text: 'Abierto', color: 'success', icon: 'fa-envelope-open' },
                    'Error API (Validación)': { text: 'Fallo Validación', color: 'danger', icon: 'fa-exclamation-circle' },
                    'Error API (Fatal)': { text: 'Fallo Fatal', color: 'danger', icon: 'fa-bug' },
                };

                const eventData = events[row.event] || { text: row.event, color: 'dark', icon: 'fa-question-circle' };
                
                // Usando íconos de Font Awesome (asumo que está cargado)
                return `<span class="badge bg-${eventData.color} rounded-pill" style="min-width: 100px;">
                            <i class="fa ${eventData.icon}" ${baseIconStyle}></i> 
                            ${eventData.text}
                        </span>`;
            }},
            
            // 2. COLUMNA MENSAJE (Muestra el mensaje de error o el estado si no hay error)
            {"data": function (row, type, set){
                if (row.error_message && row.error_message.length > 0) {
                    // Si hay error, mostrarlo en rojo/advertencia
                    return row.error_message;
                }
                // Si es un evento de éxito, muestra el tipo de evento
                return row.event;
            }},
            
            // 4. COLUMNA FECHA/HORA
            {"data": 'fecha_creacion'}
        ]
    });

    if (email_eco_table) {        
        email_eco_table.on('click', '.detalle-email-eco', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, email_eco_table);
            
            $("#id_email_eco").val(data.id);
            email_eco_detalle_table.ajax.reload();
        
            $("#emailEcoDetalleShowFormModal").modal('show');
        });
    };

    if (whatsapp_eco_table) {        
        whatsapp_eco_table.on('click', '.detalle-whatsapp-eco', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, whatsapp_eco_table);
            
            $("#id_whatsapp_eco").val(data.id);
            whatsapp_eco_detalle_table.ajax.reload();
        
            $("#whatsappEcoDetalleShowFormModal").modal('show');
        });
    };

    email_eco_table.ajax.reload();
    whatsapp_eco_table.ajax.reload();
}

function initCombosEco() {
    $comboEmailEcoEmail = $('#id_nit_eco_email').select2({
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
    $comboEmailEcoEmail = $('#id_nit_eco_whatsapp').select2({
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

$("#email-tab").on('click', function(){
    console.log("click aca");
    email_eco_table.ajax.reload();
    email_eco_table.columns.adjust().draw();
    setTimeout(function(){
    },10);
});

$("#whatsapp-tab").on('click', function(){
    console.log("click aca");
    whatsapp_eco_table.ajax.reload();
    whatsapp_eco_table.columns.adjust().draw();
    setTimeout(function(){
    },10);
});

$("#estado_eco_email").on('change', function(){
    email_eco_table.ajax.reload();
});

$("#id_nit_eco_email").on('change', function(){
    email_eco_table.ajax.reload();
});

$("#fecha_desde_eco_email").on('change', function(){
    email_eco_table.ajax.reload();
});

$("#fecha_hasta_eco_email").on('change', function(){
    email_eco_table.ajax.reload();
});

$("#estado_eco_whatsapp").on('change', function(){
    whatsapp_eco_table.ajax.reload();
});

$("#id_nit_eco_whatsapp").on('change', function(){
    whatsapp_eco_table.ajax.reload();
});

$("#fecha_desde_eco_whatsapp").on('change', function(){
    whatsapp_eco_table.ajax.reload();
});

$("#fecha_hasta_eco_whatsapp").on('change', function(){
    whatsapp_eco_table.ajax.reload();
});