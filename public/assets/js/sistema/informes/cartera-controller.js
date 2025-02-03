var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
var generarCartera = false;
var carteraExistente = false;
var cartera_table = null;

function carteraInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarCartera = false;
    carteraExistente = false;

    $('#fecha_desde_cartera').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_cartera').val(fechaDesde);

    cartera_table = $('#CarteraInformeTable').DataTable({
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
            var nivel = getNivelCartera();
            var nivelData = parseInt(data.nivel);
            var naturaleza = parseInt(data.naturaleza_cuenta);
            
            if (naturaleza == 0 && nivelData == 2) {
                if (parseInt(data.saldo_anterior) < 0 || parseInt(data.saldo) < 0) {
                    $('td', row).css('background-color', '#ff00004d');
                    $('td', row).css('color', 'black');
                    return;
                }
            }
            if (naturaleza == 1 && nivelData == 2) {
                if (parseInt(data.saldo_anterior) > 0 || parseInt(data.saldo) > 0) {
                    $('td', row).css('background-color', '#ff00004d');
                    $('td', row).css('color', 'black');
                    return;
                }
            }
            if (nivel == 1) {
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if (data.nivel == 9) {
                    $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                    $('td', row).css('font-weight', '600');
                    return;
                }
                if (data.errores) $('td', row).css('background-color', 'rgb(209 64 64 / 40%)');
            } else if (nivel == 2) {
                if (data.nivel == 9) {
                    $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                    $('td', row).css('font-weight', '700');
                    return;
                }
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.nivel == 1){
                    if (data.errores) {
                        $('td', row).css('background-color', 'rgb(209 64 64 / 55%)');
                        $('td', row).css('font-weight', 'bold');
                        return;
                    } else {
                        $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                        $('td', row).css('font-weight', '600');
                        return ;
                    }
                }
            } else if (nivel == 3) {
                if (data.nivel == 9) {
                    $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                    $('td', row).css('font-weight', '700');
                    return;
                }
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.nivel == 1){
                    if (data.errores) {
                        $('td', row).css('background-color', 'rgb(209 64 64 / 55%)');
                        $('td', row).css('font-weight', 'bold');
                        return;
                    } else {
                        $('td', row).css('background-color', 'rgb(64 164 209 / 35%)');
                        $('td', row).css('font-weight', '550');
                        return ;
                    }
                    return;
                }
                if(data.nivel == 2){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                    $('td', row).css('font-weight', '400');
                    return;
                }
            }
        },
        ajax:  {
            type: "GET",
            url: base_url_erp + 'extracto',
            headers: headersERP,
            data: function( d ) {
                d.notificacion = localStorage.getItem("notificacion_code")
            }
        },
        "columns": [
            {"data": function (row, type, set){
                var agrupado = $('#agrupar_cartera').val();
                if (agrupado == 'id_cuenta') {
                    if (row.nivel == 1) {
                        return row.cuenta;
                    } else {
                        return row.numero_documento;
                    }
                }
                if (agrupado == 'id_nit') {
                    if (row.nivel == 1) {
                        return row.numero_documento;
                    } else {
                        return row.cuenta;
                    }
                }
                return '';
            }},
            {"data": function (row, type, set){
                var agrupado = $('#agrupar_cartera').val();
                if (agrupado == 'id_cuenta') {
                    if (row.nivel == 1) {
                        return row.nombre_cuenta;
                    } else {
                        return row.nombre_nit;
                    }
                }
                if (agrupado == 'id_nit') {
                    if (row.nivel == 1) {
                        return row.nombre_nit;
                    } else {
                        return row.nombre_cuenta;
                    }
                }
                return '';
            }},
            {"data": function (row, type, set){
                var agrupado = $('#agrupar_cartera').val();
                if (agrupado == 'id_cuenta') {
                    if (row.nivel == 1) {
                        return '';
                    } else {
                        return row.apartamento_nit;
                    }
                }
                if (agrupado == 'id_nit') {
                    if (row.nivel == 1) {
                        return row.apartamento_nit;
                    } else {
                        return '';
                    }
                }
                return '';
            }},
            {data: 'documento_referencia'},
            {data: 'fecha_manual'},
            {
                data: null,
                render: function (row, type, set) {
                    var nivelData = parseInt(row.nivel);
                    var naturaleza = parseInt(row.naturaleza_cuenta);
                    const formattedNumber = $.fn.dataTable.render.number(',', '.', 2, '').display(row.saldo_anterior);

                    if (naturaleza == 0 && nivelData == 2) {
                        if (parseInt(row.saldo_anterior) < 0) {
                            return `<div class="">
                                <i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;
                                ${formattedNumber}
                            </div>`;
                        }
                    }
                    if (naturaleza == 1 && nivelData == 2) {
                        if (parseInt(row.saldo_anterior) > 0) {
                            return `<div class="">
                                <i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;
                                ${formattedNumber}
                            </div>`;
                        }
                    }
                    return formattedNumber;
                },
                className: 'dt-body-right',
                responsivePriority: 5,
                targets: -4
            },
            {
                data: null,
                render: function (row, type, set) {
                    const formattedNumber = $.fn.dataTable.render.number(',', '.', 2, '').display(row.total_facturas);
                    return formattedNumber;
                },
                className: 'dt-body-right',
                responsivePriority: 4,
                targets: -3
            },
            {
                data: null,
                render: function (row, type, set) {
                    const formattedNumber = $.fn.dataTable.render.number(',', '.', 2, '').display(row.total_abono);
                    return formattedNumber;
                },
                className: 'dt-body-right',
                responsivePriority: 3,
                targets: -2
            },
            {
                data: null,
                render: function (row, type, set) {
                    var nivelData = parseInt(row.nivel);
                    var naturaleza = parseInt(row.naturaleza_cuenta);
                    const formattedNumber = $.fn.dataTable.render.number(',', '.', 2, '').display(row.saldo);

                    if (naturaleza == 0 && nivelData == 2) {
                        if (parseInt(row.saldo) < 0) {
                            return `<div class="">
                                <i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;
                                ${formattedNumber}
                            </div>`;
                        }
                    }
                    if (naturaleza == 1 && nivelData == 2) {
                        if (parseInt(row.saldo) > 0) {
                            return `<div class="">
                                <i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;
                                ${formattedNumber}
                            </div>`;
                        }
                    }
                    return formattedNumber;
                },
                className: 'dt-body-right',
                responsivePriority: 2,
                targets: -1
            },
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    return row.consecutivo
                }
                return '';
            }},
            {data: 'dias_cumplidos', responsivePriority: 6, targets: -4},
            {data: 'mora', responsivePriority: 7, targets: -5},
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    if (row.codigo_comprobante) {
                        return row.codigo_comprobante+' - '+row.nombre_comprobante;
                    }
                }
            }},
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    return row.concepto
                }
                return '';
            }},
        ]
    });

    $('#id_cuenta_cartera').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            data: function (params) {
                var query = {
                    search: params.term,
                    total_cuentas: true,
                    id_tipo_cuenta: $("#tipo_informe_cartera").val() == 'por_cobrar' ? [3,7] : [4,8]
                }
                return query;
            },
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_nit_cartera').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un cliente",
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

    $('input[type=radio][name=detallar_cartera]').change(function() {
        if(!$("input[type='radio']#detallar_cartera1").is(':checked')){
            cartera_table.column( 6 ).visible( false );
        } else {
            cartera_table.column( 6 ).visible( true );
        }
        document.getElementById("generarCartera").click();
    });

    $("#nivel_cartera1").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarCartera").click();
    });
    
    $("#nivel_cartera2").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarCartera").click();
    });
    
    $("#nivel_cartera3").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarCartera").click();
    });
    
    $(".agrupar_cartera").on('change', function(){
        var agrupado = $("#agrupar_cartera").val();
        if (agrupado == 'id_cuenta') {
            $("#nombre_item_cartera").text("Cuenta");
        }
        if (agrupado == 'id_nit') {
            $("#nombre_item_cartera").text("Documento");
        }
        actualizarColumnas();
        document.getElementById("generarCartera").click();
    });

    $("#id_cuenta_cartera").on('change', function(){
        clearCartera();
        findCartera();
    });
    
    $("#id_nit_cartera").on('change', function(){
        clearCartera();
        findCartera();
    });
    
    $("#fecha_desde_cartera").on('change', function(){
        clearCartera();
        findCartera();
    });
    
    $(".detallar_cartera").on('change', function(){
        clearCartera();
        findCartera();
    });

    $(".tipo_informe_cartera").on('change', function(){
        clearCartera();
        findCartera();
        ctualizarColumnas();
    });

    findCartera();
    actualizarColumnas();
}

function actualizarColumnas() {
    const nivel = getNivelCartera();
    const agrupado = $("#agrupar_cartera").val();
    const tipoInforme = $("#tipo_informe_cartera").val();

    const columnUbicacionMaximoPH = cartera_table.column(2);
    const columnFactura = cartera_table.column(3);
    const columnFecha = cartera_table.column(4);
    const columnConcecutivo = cartera_table.column(9);
    const columnDias = cartera_table.column(10);
    const columnMora = cartera_table.column(11);
    const columnComprobante = cartera_table.column(12);
    const columnConcepto = cartera_table.column(13);
    
    $("#nombre_saldo_anterior").html('Saldo anterior');
    $("#nombre_total_factura").html('Total factura');
    $("#nombre_total_abono").html('Total abono');
    $("#nombre_saldo_final").html('Saldo final');

    if (nivel == 1 || nivel == 2) {
        columnFactura.visible(false);
        columnFecha.visible(false);
        columnConcecutivo.visible(false);
        columnDias.visible(false);
        columnMora.visible(false);
        columnComprobante.visible(false);
        columnConcepto.visible(false);
    }
    if (nivel == 3) {
        columnFactura.visible(true);
        columnFecha.visible(true);
        columnConcecutivo.visible(true);
        columnDias.visible(true);
        columnMora.visible(true);
        columnComprobante.visible(true);
        columnConcepto.visible(true);
    }

    if (!tipoInforme) {
        $("#nombre_saldo_anterior").html('');
        $("#nombre_total_factura").html('');
        $("#nombre_total_abono").html('');
        $("#nombre_saldo_final").html('');
    }

    if (ubicacion_maximoph_cartera) {
        if (nivel == 1) {
            if (agrupado == 'id_nit') columnUbicacionMaximoPH.visible(true);
            else columnUbicacionMaximoPH.visible(false);
        } else columnUbicacionMaximoPH.visible(true);
    } else columnUbicacionMaximoPH.visible(false);
}

function loadCarteraById(id_cartera) {
    var url = base_url_erp + 'cartera-show?id='+id_cartera;
    cartera_table.ajax.url(url).load(function(res) {
        
        if(res.success){
            $("#generarCartera").show();
            $("#generarCarteraLoading").hide();
            $("#generarCarteraUltimoLoading").hide();
            $('#descargarExcelCartera').prop('disabled', false);
            $("#descargarExcelCartera").show();
            $("#descargarExcelCarteraDisabled").hide();
            $('#generarCarteraUltimo').hide();
            $('#generarCarteraUltimoLoading').hide();

            agregarToast('exito', 'Cartera cargado', 'Informe cargado con exito!', true);
            
            mostrarTotalesCartera(res.totales);
        }
    });
}

function mostrarTotalesCartera(data) {
    if(!data) {
        return;
    }
    $("#cartera_anterior").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_anterior));
    $("#cartera_facturas").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_facturas));
    $("#cartera_abonos").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_abono));
    $("#cartera_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo));
}

$(document).on('click', '#generarCartera', function () {
    generarCartera = false;
    $("#generarCartera").hide();
    $("#generarCarteraLoading").show();
    $('#descargarExcelCartera').prop('disabled', true);
    $("#descargarExcelCartera").hide();
    
    $("#cartera_anterior").text('$0');
    $("#cartera_facturas").text('$0');
    $("#cartera_abonos").text('$0');
    $("#cartera_diferencia").text('$0');

    var url = base_url_erp + 'cartera';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_desde='+$('#fecha_desde_cartera').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_cartera').val();
    url+= '&agrupar_cartera='+$('#agrupar_cartera').val();
    url+= '&tipo_informe='+$("#tipo_informe_cartera").val();
    url+= '&nivel='+getNivelCartera();

    cartera_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar Cartera?',
                    text: "Cartera generado anteriormente ¿Desea cargarlo?",
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
                        loadCarteraById(res.data);
                    } else {
                        generarCartera = true;
                        GenerateCartera();
                    }
                })
            } else {
                agregarToast('info', 'Generando cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });

});

function findCartera() {
    carteraExistente = false;
    $('#generarCarteraUltimo').hide();
    $('#generarCarteraUltimoLoading').show();

    var url = base_url_erp + 'cartera-find';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_desde_cartera='+$('#fecha_desde_cartera').val();
    url+= '&agrupar_cartera='+$('#agrupar_cartera').val();
    url+= '&tipo_informe='+$("#tipo_informe_cartera").val();
    url+= '&nivel='+getNivelCartera();
    
    $.ajax({
        url: url,
        method: 'GET',
        headers: headersERP,
        dataType: 'json',
    }).done((res) => {
        $('#generarCarteraUltimoLoading').hide();
        if(res.data){
            carteraExistente = res.data;
            $('#generarCarteraUltimo').show();
        }
    }).fail((err) => {
        $('#generarCarteraUltimoLoading').hide();
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
        agregarToast('error', 'Error al consultar cartera', errorsMsg, true);
    });
}

function GenerateCartera() {
    var url = base_url_erp + 'cartera-find';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_desde='+$('#fecha_desde_cartera').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_cartera').val();
    url+= '&agrupar='+$('#agrupar_cartera').val();
    url+= '&tipo_informe='+$("#tipo_informe_cartera").val();
    url+= '&nivel='+getNivelCartera();
    url+= '&generar='+generarCartera;

    cartera_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}

$(document).on('click', '#generarCarteraUltimo', function () {
    $('#generarCarteraUltimo').hide();
    $('#generarCarteraUltimoLoading').show();
    loadCarteraById(carteraExistente);
});

var channelCartera = pusher.subscribe('informe-cartera-'+localStorage.getItem("notificacion_code"));

channelCartera.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_cartera){
        $('#id_cartera_cargado').val(data.id_cartera);
        loadCarteraById(data.id_cartera);
        return;
    }
});

function clearCartera() {
    $("#descargarExcelCartera").hide();
    $("#descargarExcelCarteraDisabled").show();
}

function getNivelCartera() {
    if($("input[type='radio']#nivel_cartera1").is(':checked')) return 1;
    if($("input[type='radio']#nivel_cartera2").is(':checked')) return 2;
    if($("input[type='radio']#nivel_cartera3").is(':checked')) return 3;

    return false;
}

function formatNitCartera (nit) {
    
    if (nit.loading) return nit.text;

    if (ubicacion_maximoph_cartera) {
        if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
        else return nit.text;
    }
    else return nit.text;
}

function formatRepoCartera (nit) {
    return nit.full_name || nit.text;
}