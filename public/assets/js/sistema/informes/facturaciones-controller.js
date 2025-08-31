var facturaiones_table = null;
var $comboPeriodoFacturaciones = null;
let channelEmailNofiticacion = pusher.subscribe('facturacion-email-'+localStorage.getItem("notificacion_code"));
let channelFacturaNofiticacion = pusher.subscribe('facturacion-factura-'+localStorage.getItem("notificacion_code"));

function facturacionesInit() {

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
                d.id_zona = $("#id_zona_facturaciones").val();
                d.factura_fisica = $("input[type='checkbox']#nit_fisica_facturaciones").is(':checked') ? '1' : ''
            }
        },
        columns: [
            { data:'numero_documento'},
            { data: 'nombre_nit'},
            { data: 'apartamentos'},
            { data: 'saldo_anterior', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'saldo_final', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: 'email'},
            { data: 'email_1'},
            { data: 'email_2'},
            {
                "data": function (row, type, set){
                    var html = ``;
                    if (enviar_facturas) html+= `
                        <span
                            id="enviarfacturaciones_${row.id}"
                            href="javascript:void(0)"
                            class="btn badge bg-gradient-dark enviar-facturaciones"
                            style="margin-bottom: 0rem !important; min-width: 50px;"
                        >
                            <i class="fas fa-envelope"></i>&nbsp;&nbsp;
                            Enviar Email
                        </span>&nbsp;`;
                    if (whatsapp_facturas && (row.telefono_1 || row.telefono_2)) html+= `
                        <span 
                            id="whatsappfacturaciones_${row.id}"
                            href="javascript:void(0)"
                            class="btn badge bg-gradient-dark whatsapp-facturaciones"
                            style="margin-bottom: 0rem !important; min-width: 50px; background-image: linear-gradient(310deg, #25d366 0%, #25d366 100%);"
                        >
                            <i class="fa-brands fa-whatsapp"></i>&nbsp;
                            Enviar Whatsapp
                        </span>

                        <span 
                            id="whatsappfacturacionesloading_${row.id}"
                            class="badge bg-gradient-dark"
                            style="margin-bottom: 0rem !important; min-width: 50px; background-image: linear-gradient(310deg, #25d366 0%, #25d366 100%); display: none;"
                        >
                            <b style="opacity: 0.3; text-transform: math-auto;">Enviar Whatsapp</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -47px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                        `;
                    html+= `
                        <span
                            id="imprimirfacturaciones_${row.id_nit}"
                            href="javascript:void(0)"
                            class="btn badge bg-gradient-success imprimir-facturaciones"
                            style="margin-bottom: 0rem !important; min-width: 50px; background-image: linear-gradient(310deg, #CC2229 0%, #CC2229 100%);"
                        >
                            <i class="fas fa-file-pdf"></i>&nbsp;&nbsp;
                            Imprimir
                        </span>&nbsp;`;
                    return html;
                }
            },
        ]
    });

    if (facturaiones_table) {
        facturaiones_table.on('click', '.imprimir-facturaciones', function() {
            var id_nit = this.id.split('_')[1];
            window.open("/facturacion-show-pdf?id_nit="+id_nit+"&periodo="+formatoFechaFacturacion(), "_blank");
        });

        facturaiones_table.on('click', '.enviar-facturaciones', function() {

            var id = this.id.split('_')[1];

            var data = getDataById(id, facturaiones_table);

            let emailData = {
                factura_fisica: '',
                periodo: formatoFechaFacturacion(),
                id_nit: data.id_nit
            }
        
            Swal.fire({
                title: 'Enviar factura?',
                text: "Desea enviar la factura a "+data.nombre_nit+"?",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Enviar facturas!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $("#enviarEmailFacturas").hide();
                    $("#enviarEmailFacturasLoading").show();
                    $.ajax({
                        url: base_url + 'facturacion-email',
                        method: 'GET',
                        data: emailData,
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        $("#enviarEmailFacturas").show();
                        $("#enviarEmailFacturasLoading").hide();
                        
                        agregarToast('info', 'Enviando email', 'Se notificará cuando se hayan enviado las facturas!', true);
                    }).fail((err) => {
                        var mensaje = err.responseJSON.message;
                        var errorsMsg = arreglarMensajeError(mensaje);
                        agregarToast('error', 'Creación errada', errorsMsg);
                    });
                }
            })
        });

        facturaiones_table.on('click', '.whatsapp-facturaciones', function() {

            var id = this.id.split('_')[1];

            var data = getDataById(id, facturaiones_table);

            let whatsappData = {
                factura_fisica: '',
                periodo: formatoFechaFacturacion(),
                id_nit: data.id_nit
            }
        
            Swal.fire({
                title: 'Enviar factura a whatsapp?',
                text: "Desea enviar la factura a "+data.nombre_nit+"?",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Enviar facturas!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){

                    $("#whatsappfacturaciones_"+id).hide();
                    $("#whatsappfacturacionesloading_"+id).show();
                    
                    $.ajax({
                        url: base_url + 'facturacion-whatsapp',
                        method: 'GET',
                        data: whatsappData,
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        $("#whatsappfacturaciones_"+id).show();
                        $("#whatsappfacturacionesloading_"+id).hide();
                        
                        agregarToast('info', 'Enviando whatsapp', 'Se notificará cuando se hayan enviado las facturas!', true);
                    }).fail((err) => {

                        $("#whatsappfacturaciones_"+id).show();
                        $("#whatsappfacturacionesloading_"+id).hide();

                        var mensaje = err.responseJSON.message;
                        var errorsMsg = arreglarMensajeError(mensaje);
                        agregarToast('error', 'Creación errada', errorsMsg);
                    });
                }
            })
        });
    }
    
    $('#id_nit_facturaciones').select2({
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

    $('#id_zona_facturaciones').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una zona",
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
            },
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

    $("#periodo_facturaciones").on('change', function(event) {
        facturaiones_table.ajax.reload();
    });
    
    $("#id_nit_facturaciones").on('change', function(event) {
        facturaiones_table.ajax.reload();
    });

    $("#id_zona_facturaciones").on('change', function(event) {
        facturaiones_table.ajax.reload();
    });
    
    $("#nit_fisica_facturaciones").on('change', function(event) {
        facturaiones_table.ajax.reload();
    });
}

$("#imprimirMultipleFacturacion").on('click', function(event) {
    $("#imprimirMultipleFacturacion").hide();
    $("#imprimirMultipleFacturacionLoading").show();
    $.ajax({
        url: base_url + 'facturacion-multiple',
        method: 'POST',
        data: JSON.stringify({
            factura_fisica: $("input[type='checkbox']#nit_fisica_facturaciones").is(':checked') ? '1' : '',
            periodo: formatoFechaFacturacion(),
            id_nit: $("#id_nit_facturaciones").val(),
            id_zona: $("#id_zona_facturaciones").val(),
        }),
        headers: headers
    }).done((res) => {
        agregarToast('info', 'Generando facturas pdf', 'Se notificará cuando se hayan generado las facturas pdf!', true);
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$("#enviarEmailFacturas").on('click', function(event) {

    let data = {
        factura_fisica: $("input[type='checkbox']#nit_fisica_facturaciones").is(':checked') ? '1' : '',
        periodo: formatoFechaFacturacion(),
        id_nit: $("#id_nit_facturaciones").val(),
        id_zona: $("#id_zona_facturaciones").val(),
    }

    Swal.fire({
        title: 'Enviar facturas?',
        text: "Desea enviar las facturas a todas las personas filtradas?",
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Enviar facturas!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){
            $("#enviarEmailFacturas").hide();
            $("#enviarEmailFacturasLoading").show();
            $.ajax({
                url: base_url + 'facturacion-email',
                method: 'GET',
                data: data,
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#enviarEmailFacturas").show();
                $("#enviarEmailFacturasLoading").hide();

                agregarToast('info', 'Enviando email', 'Se notificará cuando se hayan enviado las facturas!', true);
            }).fail((err) => {
                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Creación errada', errorsMsg);
            });
        }
    })
});

function formatoFechaFacturacion() {
    var periodo = $("#periodo_facturaciones").val();
    var fecha = '';
    fecha+= periodo[0]+periodo[1]+periodo[2]+periodo[3];
    fecha+= '-'+periodo[4]+periodo[5];
    fecha+= '-'+periodo[6]+periodo[7];
    return fecha;
}

function formatInmuebleRecibo (inmueble) {

    if (inmueble.loading) return inmueble.text;

    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

function formatInmuebleReciboSelection (inmueble) {
    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

channelEmailNofiticacion.bind('notificaciones', function(data) {
    let mensaje = `Total de facturas enviadas: ${data.total_envios}`;
    agregarToast('exito', 'Email enviados', mensaje, true);
});

channelFacturaNofiticacion.bind('notificaciones', function(data) {
    $("#imprimirMultipleFacturacion").show();
    $("#imprimirMultipleFacturacionLoading").hide();
    agregarToast('exito', 'Pdf generado', 'Los pdf de las facturas se han generado con exito!', true);
    window.open(data.urf_factura, "_blank");
});

channelFacturaNofiticacion.bind('notificaciones', function(data) {
    $("#imprimirMultipleFacturacion").show();
    $("#imprimirMultipleFacturacionLoading").hide();
    agregarToast('exito', 'Pdf generado', 'Los pdf de las facturas se han generado con exito!', true);
    
    // Verificar y completar la URL si es necesario
    let url = data.urf_factura;
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com' + 
        (url.startsWith('/') ? url : '/' + url);
    }
    
    window.open(url, "_blank");
});