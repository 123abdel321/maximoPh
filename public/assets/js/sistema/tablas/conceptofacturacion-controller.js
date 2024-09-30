var concepto_facturacion_table = null
var $comboCuentaIngreso = null;
var $comboCuentaCobrar = null;
var $comboCuentaIntereses = null;
var $comboCuentaIva = null;
var $comboCuentaProntoPagoGasto = null;
var $comboCuentaProntoPagoAnticipo = null;

function conceptofacturacionInit() {
    concepto_facturacion_table = $('#conceptoFacturacionTable').DataTable({
        pageLength: 15,
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
            url: base_url + 'concepto-facturacion',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre_concepto'},
            {"data": function (row, type, set){  
                if (row.cuenta_ingreso) {
                    return row.cuenta_ingreso.cuenta+' - '+row.cuenta_ingreso.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_cobrar) {
                    return row.cuenta_cobrar.cuenta+' - '+row.cuenta_cobrar.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_interes) {
                    return row.cuenta_interes.cuenta+' - '+row.cuenta_interes.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_iva) {
                    return row.cuenta_iva.cuenta+' - '+row.cuenta_iva.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.intereses) {
                    return 'SI'
                }
                return 'NO';
            }},
            {"data": function (row, type, set){  
                if (row.tipo_concepto) {
                    return 'CUOTAS EXTRAS & MULTAS'
                }
                return 'FACTURACIÓN';
            }},
            {"data":'valor'},
            {"data": function (row, type, set){  
                if (row.pronto_pago) {
                    return 'SI'
                }
                return 'NO';
            }},
            {"data":'dias_pronto_pago'},
            {"data":'porcentaje_pronto_pago'},
            {"data": function (row, type, set){
                if (row.cuenta_gasto) {
                    return row.cuenta_gasto.cuenta+' - '+row.cuenta_gasto.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_anticipo) {
                    return row.cuenta_anticipo.cuenta+' - '+row.cuenta_anticipo.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (editarConceptoFacturacion) html+= '<span id="editconceptoFacturacion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-conceptoFacturacion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarConceptoFacturacion) html+= '<span id="deleteconceptoFacturacion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-conceptoFacturacion" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (concepto_facturacion_table) {
        //EDITAR CONCEPTOS FACTURACION
        concepto_facturacion_table.on('click', '.edit-conceptoFacturacion', function() {
            clearFormConceptoFacturacion();
            $("#textConceptoFacturacionCreate").hide();
            $("#textConceptoFacturacionUpdate").show();
            $("#saveConceptoFacturacionLoading").hide();
            $("#updateConceptoFacturacion").show();
            $("#saveConceptoFacturacion").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, concepto_facturacion_table);

            if(data.cuenta_ingreso) {
                var dataCuenta = {
                    id: data.cuenta_ingreso.id,
                    text: data.cuenta_ingreso.cuenta + ' - ' + data.cuenta_ingreso.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIngreso.append(newOption).trigger('change');
                $comboCuentaIngreso.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_interes) {
                var dataCuenta = {
                    id: data.cuenta_interes.id,
                    text: data.cuenta_interes.cuenta + ' - ' + data.cuenta_interes.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIntereses.append(newOption).trigger('change');
                $comboCuentaIntereses.val(dataCuenta.id).trigger('change');
            }
            
            if(data.cuenta_cobrar) {
                var dataCuenta = {
                    id: data.cuenta_cobrar.id,
                    text: data.cuenta_cobrar.cuenta + ' - ' + data.cuenta_cobrar.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCobrar.append(newOption).trigger('change');
                $comboCuentaCobrar.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_iva) {
                var dataCuenta = {
                    id: data.cuenta_iva.id,
                    text: data.cuenta_iva.cuenta + ' - ' + data.cuenta_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaIva.append(newOption).trigger('change');
                $comboCuentaIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_anticipo) {
                var dataCuenta = {
                    id: data.cuenta_anticipo.id,
                    text: data.cuenta_anticipo.cuenta + ' - ' + data.cuenta_anticipo.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaProntoPagoAnticipo.append(newOption).trigger('change');
                $comboCuentaProntoPagoAnticipo.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_gasto) {
                var dataCuenta = {
                    id: data.cuenta_gasto.id,
                    text: data.cuenta_gasto.cuenta + ' - ' + data.cuenta_gasto.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaProntoPagoGasto.append(newOption).trigger('change');
                $comboCuentaProntoPagoGasto.val(dataCuenta.id).trigger('change');
            }

            $("#id_concepto_facturacion_up").val(data.id);
            $("#codigo_concepto_facturacion").val(data.codigo);
            $("#tipo_concepto_facturacion").val(data.tipo_concepto);
            $("#nombre_concepto_facturacion").val(data.nombre_concepto);
            $("#valor_concepto_facturacion").val(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.valor));

            if (data.intereses) $('#intereses_concepto_facturacion').prop('checked', true);
            else $('#intereses_concepto_facturacion').prop('checked', false);

            if (data.pronto_pago) {
                $('#pronto_pago_concepto_facturacion').prop('checked', true);
                $('#input-id_cuenta_pronto_pago_gasto').show();
                $('#input-id_cuenta_pronto_pago_anticipo').show();
                $('#input-dias_concepto_facturacion').show();
                $('#input-porcentaje_descuento_concepto_facturacion').show();
            } else {
                $('#pronto_pago_concepto_facturacion').prop('checked', false);
                $('#input-id_cuenta_pronto_pago_gasto').hide();
                $('#input-id_cuenta_pronto_pago_anticipo').hide();
                $('#input-dias_concepto_facturacion').hide();
                $('#input-porcentaje_descuento_concepto_facturacion').hide();
            }

            $("#dias_concepto_facturacion").val(data.dias_pronto_pago);
            $("#porcentaje_descuento_concepto_facturacion").val(data.porcentaje_pronto_pago);

            $("#conceptoFacturacionFormModal").modal('show');
        });
        //BORRAR CONCEPTOS FACTURACION
        concepto_facturacion_table.on('click', '.drop-conceptoFacturacion', function() {

            var trConceptoFacturacion = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, concepto_facturacion_table);

            Swal.fire({
                title: 'Eliminar concepto facturacion: '+data.nombre_concepto+'?',
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'concepto-facturacion',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            concepto_facturacion_table.row(trConceptoFacturacion).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Concepto facturación eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //EDITAR DOBLE CLICK
        concepto_facturacion_table.on('dblclick', 'tr', function () {
            var data = concepto_facturacion_table.row(this).data();
            if (data) {
                document.getElementById("editconceptoFacturacion_"+data.id).click();
            }
        });

    }

    $comboCuentaIngreso = $('#id_cuenta_ingreso_concepto_facturacion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                    // id_tipo_cuenta: []
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

    $comboCuentaCobrar = $('#id_cuenta_cobrar_concepto_facturacion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                    // id_tipo_cuenta: []
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

    $comboCuentaIntereses = $('#id_cuenta_interes_concepto_facturacion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione un concepto",
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                    // id_tipo_cuenta: []
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

    $comboCuentaIva = $('#id_cuenta_iva_concepto_facturacion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione un concepto",
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                    // id_tipo_cuenta: []
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

    $comboCuentaProntoPagoGasto = $('#id_cuenta_pronto_pago_gasto').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
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

    $comboCuentaProntoPagoAnticipo = $('#id_cuenta_pronto_pago_anticipo').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoFacturacionFormModal'),
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

    let column = concepto_facturacion_table.column(9);

    if (!editarConceptoFacturacion && !eliminarConceptoFacturacion) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    concepto_facturacion_table.ajax.reload();
}

$(document).on('click', '#createConceptoFacturacion', function () {
    clearFormConceptoFacturacion();
    $("#saveConceptoFacturacion").show();
    $("#updateConceptoFacturacion").hide();
    $("#saveConceptoFacturacionLoading").hide();
    $("#conceptoFacturacionFormModal").modal('show');
});

function clearFormConceptoFacturacion(){
    $("#textConceptoFacturacionCreate").show();
    $("#textConceptoFacturacionUpdate").hide();
    $("#saveConceptoFacturacionLoading").hide();

    $("#id_concepto_facturacion_up").val('');
    $("#codigo_concepto_facturacion").val(''),
    $("#nombre_concepto_facturacion").val('');
    $("#intereses_concepto_facturacion").val('');
    $("#valor_concepto_facturacion").val(0);

    if (dias_pronto_pago) {
        $("#dias_concepto_facturacion").prop('disabled', true);
        $("#dias_concepto_facturacion").val(dias_pronto_pago);
    } else {
        $("#dias_concepto_facturacion").prop('disabled', false);
        $("#dias_concepto_facturacion").val('0');
    }
    
    $comboCuentaIngreso.val('').trigger('change');
    $comboCuentaCobrar.val('').trigger('change');
    $comboCuentaIntereses.val('').trigger('change');
    $comboCuentaIva.val('').trigger('change');
}

$(document).on('click', '#saveConceptoFacturacion', function () {

    var form = document.querySelector('#conceptoFacturacionForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveConceptoFacturacionLoading").show();
    $("#updateConceptoFacturacion").hide();
    $("#saveConceptoFacturacion").hide();

    let data = {
        codigo_concepto: $("#codigo_concepto_facturacion").val(),
        nombre_concepto: $("#nombre_concepto_facturacion").val(),
        id_cuenta_ingreso: $("#id_cuenta_ingreso_concepto_facturacion").val(),
        id_cuenta_interes: $("#id_cuenta_interes_concepto_facturacion").val(),
        id_cuenta_cobrar: $("#id_cuenta_cobrar_concepto_facturacion").val(),
        id_cuenta_iva: $("#id_cuenta_iva_concepto_facturacion").val(),
        intereses: $("input[type='checkbox']#intereses_concepto_facturacion").is(':checked') ? '1' : '',
        tipo_concepto: $('#tipo_concepto_facturacion').val(),
        valor: $("#valor_concepto_facturacion").val(),
        pronto_pago: $("input[type='checkbox']#pronto_pago_concepto_facturacion").is(':checked') ? '1' : '',
        id_cuenta_pronto_pago_anticipo: $('#id_cuenta_pronto_pago_anticipo').val(),
        id_cuenta_pronto_pago_gasto: $('#id_cuenta_pronto_pago_gasto').val(),
        dias_pronto_pago: $('#dias_concepto_facturacion').val(),
        porcentaje_pronto_pago: $('#porcentaje_descuento_concepto_facturacion').val(),
    }

    $.ajax({
        url: base_url + 'concepto-facturacion',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptoFacturacion();
            $("#saveConceptoFacturacion").show();
            $("#saveConceptoFacturacionLoading").hide();
            $("#conceptoFacturacionFormModal").modal('hide');
            concepto_facturacion_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Concepto de facturación creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveConceptoFacturacion').show();
        $('#saveConceptoFacturacionLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateConceptoFacturacion', function () {

    var form = document.querySelector('#conceptoFacturacionForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveConceptoFacturacionLoading").show();
    $("#updateConceptoFacturacion").hide();
    $("#saveConceptoFacturacion").hide();

    let data = {
        id: $("#id_concepto_facturacion_up").val(),
        codigo_concepto: $("#codigo_concepto_facturacion").val(),
        nombre_concepto: $("#nombre_concepto_facturacion").val(),
        id_cuenta_ingreso: $("#id_cuenta_ingreso_concepto_facturacion").val(),
        id_cuenta_interes: $("#id_cuenta_interes_concepto_facturacion").val(),
        id_cuenta_cobrar: $("#id_cuenta_cobrar_concepto_facturacion").val(),
        id_cuenta_iva: $("#id_cuenta_iva_concepto_facturacion").val(),
        intereses: $("input[type='checkbox']#intereses_concepto_facturacion").is(':checked') ? '1' : '',
        tipo_concepto: $('#tipo_concepto_facturacion').val(),
        valor: $("#valor_concepto_facturacion").val(),
        pronto_pago: $("input[type='checkbox']#pronto_pago_concepto_facturacion").is(':checked') ? '1' : '',
        id_cuenta_pronto_pago_anticipo: $('#id_cuenta_pronto_pago_anticipo').val(),
        id_cuenta_pronto_pago_gasto: $('#id_cuenta_pronto_pago_gasto').val(),
        dias_pronto_pago: $('#dias_concepto_facturacion').val(),
        porcentaje_pronto_pago: $('#porcentaje_descuento_concepto_facturacion').val(),
    }

    $.ajax({
        url: base_url + 'concepto-facturacion',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptoFacturacion();
            $("#saveConceptoFacturacion").show();
            $("#saveConceptoFacturacionLoading").hide();
            $("#conceptoFacturacionFormModal").modal('hide');
            concepto_facturacion_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Concepto de facturación actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#saveConceptoFacturacion').show();
        $('#saveConceptoFacturacionLoading').hide();
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
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});

$("#pronto_pago_concepto_facturacion").on('change', function(event) {
    if ($("input[type='checkbox']#pronto_pago_concepto_facturacion").is(':checked')) {
        $('#input-id_cuenta_pronto_pago_gasto').show();
        $('#input-id_cuenta_pronto_pago_anticipo').show();
        $('#input-dias_concepto_facturacion').show();
        $('#input-porcentaje_descuento_concepto_facturacion').show();
    } else {
        $('#input-id_cuenta_pronto_pago_gasto').hide();
        $('#input-id_cuenta_pronto_pago_anticipo').hide();
        $('#input-dias_concepto_facturacion').hide();
        $('#input-porcentaje_descuento_concepto_facturacion').hide();
    }
});

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("input[data-type='currency']").on({
    keyup: function(event) {
        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
            formatCurrency($(this));
        }
    },
    blur: function() {
        formatCurrency($(this), "blur");
    }
});