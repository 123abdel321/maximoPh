var searchValue = null;
var nitsInmuebles = [];
var cuotas_multas_table = null;
var $comboNitCuotasMuldas = null;
var $comboZonaCuotasMuldas = null;
var $comboInmuebleCuotasMuldas = null;
var $comboConceptoFacturacionCuotasMuldas = null;
var $comboConceptoTipoFacturacionCuotasMuldas = null;

function cuotasmultasInit() {

    $('#fecha_desde_cuotas_multas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2));
    $('#fecha_hasta_cuotas_multas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2));

    cuotas_multas_table = $('#cuotaMultaTable').DataTable({
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
            url: base_url + 'cuotasmultas',
            data: function ( d ) {
                d.search = searchValue;
                d.fecha_desde = $('#fecha_desde_cuotas_multas').val();
                d.fecha_hasta = $('#fecha_hasta_cuotas_multas').val();
                d.id_concepto = $('#id_concepto_filter_cuotas_multas').val();
            }
        },
        columns: [
            {"data":'id', visible: false},
            {"data": function (row, type, set){  
                if (row.concepto) {
                    return row.concepto.nombre_concepto;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.nit) {
                    return row.nit.numero_documento
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.nit) {
                    return row.nit.nombre_completo;
                }
                return '';
            }},
            {"data":'fecha_inicio', className: 'dt-body-left', render: function (data, type) { return monthYear(data);}},
            {"data":'fecha_fin', className: 'dt-body-left', render: function (data, type) { return monthYear(data);}},
            {"data":'valor_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.zona.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.nombre
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.area
                }
                return '';
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (row.inmueble) {
                    return row.inmueble.coeficiente
                }
                return '';
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion', className: 'dt-body-left'},
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
                    if (editarCuotaMulta) html+= '<span id="editcuotamulta_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-cuota-multa" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarCuotaMulta) html+= '<span id="deletecuotamulta_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-cuota-multa" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (cuotas_multas_table) {
        //EDITAR CUOTAS / MULTAS
        cuotas_multas_table.on('click', '.edit-cuota-multa', function() {
            clearForm();
            cuotasConceptosIndividual();
            $('#textCuotaMultaCreate').hide();
            $('#textCuotaMultaUpdate').show();
            $("#saveCuotaMultaLoading").hide();
            $("#updateCuotaMulta").show();
            $("#saveCuotaMulta").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, cuotas_multas_table);

            if(data.id_inmueble){
                var dataInmueble = {
                    id: data.inmueble.id,
                    text: data.inmueble.nombre
                };
                var newOption = new Option(dataInmueble.text, dataInmueble.id, false, false);
                $comboInmuebleCuotasMuldas.append(newOption).trigger('change');
                $comboInmuebleCuotasMuldas.val(dataInmueble.id).trigger('change');
            }

            if(data.id_nit){
                var dataNit = {
                    id: data.nit.id,
                    text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitCuotasMuldas.append(newOption).trigger('change');
                $comboNitCuotasMuldas.val(dataNit.id).trigger('change');
            }

            if(data.id_concepto_facturacion){
                var dataConcepto = {
                    id: data.concepto.id,
                    text: data.concepto.nombre_concepto
                };
                var newOption = new Option(dataConcepto.text, dataConcepto.id, false, false);
                $comboConceptoFacturacionCuotasMuldas.append(newOption).trigger('change');
                $comboConceptoFacturacionCuotasMuldas.val(dataConcepto.id).trigger('change');
            }

            var valor = parseFloat(data.tipo_concepto ? data.valor_total : data.valor_coeficiente);

            $('#id_cuota_multa_up').val(data.id);
            $('#input_masivo_cuotas_multas').hide();
            $('#tipo_concepto_cuotas_multas').val(data.tipo_concepto);
            $('#fecha_inicio_cuotas_multas').val(data.fecha_inicio);
            $('#fecha_fin_cuotas_multas').val(data.fecha_fin);
            $('#valor_cuotas_multas').val(new Intl.NumberFormat("ja-JP").format(valor));
            $('#observacion_cuotas_multas').val(data.observacion);

            $("#cuotaMultasFormModal").modal('show');
        });
        //BORRAR CUOTAS / MULTAS
        cuotas_multas_table.on('click', '.drop-cuota-multa', function() {
            var trCuotaMulta = $(this).closest('tr');
            var id = this.id.split('_')[1];

            Swal.fire({
                title: 'Eliminar cuota / multa ?',
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
                        url: base_url + 'cuotasmultas',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            cuotas_multas_table.row(trCuotaMulta).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Cuota / multa eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    $comboNitCuotasMuldas = $('#id_nit_cuotas_multas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cuotaMultasFormModal'),
        delay: 250,
        placeholder: "Seleccione una persona",
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
                    search: params.term,
                    id_nits: nitsInmuebles
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

    $comboZonaCuotasMuldas = $('#id_zona_cuotas_multas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cuotaMultasFormModal'),
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
            }
        }
    });

    $comboInmuebleCuotasMuldas = $('#id_inmueble_cuotas_multas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cuotaMultasFormModal'),
        delay: 250,
        placeholder: "Seleccione un inmueble",
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
        },
        templateResult: formatInmuebleCombo,
        templateSelection: formatInmuebleSelection
    });

    $comboConceptoFacturacionCuotasMuldas = $('#id_concepto_facturacion_cuotas_multas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cuotaMultasFormModal'),
        delay: 250,
        placeholder: "Seleccione un concepto",
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
            url: 'api/concepto-facturacion-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 1
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

    $comboConceptoTipoFacturacionCuotasMuldas = $('#id_concepto_tipo_facturacion_cuotas_multas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cuotaMultasFormModal'),
        delay: 250,
        placeholder: "Seleccione un tipo concepto",
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

    $('#id_concepto_filter_cuotas_multas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione un concepto",
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
            url: 'api/concepto-facturacion-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 1
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

    $('.water').hide();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
    });
}

function formatInmuebleSelection (inmueble) {
    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

function formatInmuebleCombo (inmueble) {

    if (inmueble.loading) return inmueble.text;

    var persona = '';

    if (inmueble && inmueble.personas && inmueble.personas.length) {
        persona = ' - ' + inmueble.personas[0].nit.nombre_completo
    }

    return inmueble.text + persona;
}

$(document).on('click', '#saveCuotaMulta', function () {
    var form = document.querySelector('#cuotaMultasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveCuotaMultaLoading").show();
    $("#updateCuotaMulta").hide();
    $("#saveCuotaMulta").hide();

    var masivo = $("input[type='checkbox']#masivo_cuotas_multas").is(':checked') ? '1' : '';

    let data = {
        tipo_concepto: $('#tipo_concepto_cuotas_multas').val(),
        id_concepto_tipo_facturacion: masivo ? $('#id_concepto_tipo_facturacion_cuotas_multas').val() : null,
        id_zona: masivo ? $('#id_cuotamulta_cuotas_multas').val() : null,
        id_inmueble: masivo ? null : $('#id_inmueble_cuotas_multas').val(),
        id_nit: masivo ? null : $('#id_nit_cuotas_multas').val(),
        id_concepto_facturacion: $('#id_concepto_facturacion_cuotas_multas').val(),
        fecha_inicio: $('#fecha_inicio_cuotas_multas').val(),
        fecha_fin: $('#fecha_fin_cuotas_multas').val(),
        valor:  stringToNumberFloat($('#valor_cuotas_multas').val()),
        observacion: $('#observacion_cuotas_multas').val(),
        masivo: masivo
    };

    $.ajax({
        url: base_url + 'cuotasmultas',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearForm();
            $("#saveCuotaMulta").show();
            $("#saveCuotaMultaLoading").hide();
            $("#cuotaMultasFormModal").modal('hide');
            cuotas_multas_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Cuota extra / multa creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveConceptoFacturacion').show();
        $('#saveCuotaMultaLoading').hide();
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

$(document).on('click', '#updateCuotaMulta', function () {
    var form = document.querySelector('#cuotaMultasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveCuotaMultaLoading").show();
    $("#updateCuotaMulta").hide();
    $("#saveCuotaMulta").hide();
    $("#cancelCuotaMulta").hide();

    let data = {
        id: $('#id_cuota_multa_up').val(),
        tipo_concepto: $('#tipo_concepto_cuotas_multas').val(),
        id_concepto_tipo_facturacion: null,
        id_zona: null,
        id_inmueble: $('#id_inmueble_cuotas_multas').val(),
        id_nit: $('#id_nit_cuotas_multas').val(),
        id_concepto_facturacion: $('#id_concepto_facturacion_cuotas_multas').val(),
        fecha_inicio: $('#fecha_inicio_cuotas_multas').val(),
        fecha_fin: $('#fecha_fin_cuotas_multas').val(),
        valor:  stringToNumberFloat($('#valor_cuotas_multas').val()),
        observacion: $('#observacion_cuotas_multas').val(),
    };

    $.ajax({
        url: base_url + 'cuotasmultas',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearForm();
            $("#cancelCuotaMulta").show();
            $("#saveCuotaMulta").show();
            $("#saveCuotaMultaLoading").hide();
            $("#cuotaMultasFormModal").modal('hide');
            cuotas_multas_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Cuota extra / multa actualizada con exito!', true);
        }
    }).fail((err) => {
        $("#cancelCuotaMulta").show();
        $('#updateCuotaMulta').show();
        $('#saveConceptoFacturacion').show();
        $('#saveCuotaMultaLoading').hide();
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

$(document).on('click', '#createCuotasMultas', function () {
    clearForm();
    ocultarCampos();
    $('#saveCuotaMulta').show();
    $('#updateCuotaMulta').hide();
    $('#textCuotaMultaUpdate').hide();
    $('#textCuotaMultaCreate').show();
    if ($("input[type='checkbox']#masivo_cuotas_multas").is(':checked')) {
        cuotasConceptosMasivo();
    } else {
        cuotasConceptosIndividual();
    }
    $("#cuotaMultasFormModal").modal('show');
});

$(document).on('change', '#masivo_cuotas_multas', function () {
    ocultarCampos();
    $('#saveCuotaMulta').show();
    $('#textCuotaMultaCreate').show();
    if ($("input[type='checkbox']#masivo_cuotas_multas").is(':checked')) {
        cuotasConceptosMasivo();
    } else {
        cuotasConceptosIndividual();
    }
});

$(document).on('change', '#id_inmueble_cuotas_multas', function () {
    var data = $(this).select2('data');

    if (!data) return;
    if (!data.length) return;
    if (!data[0].personas) return;

    nitsInmuebles = [];
    var inmueble = data[0];
    var personas = inmueble.personas;

    if (!personas.length) {
        agregarToast('warning', 'Inmueble sin nits', 'El inmueble: '+data.nombre+' no tiene personas asignadas');
        return;
    }

    personas.forEach(persona => {
        nitsInmuebles.push(persona.id_nit);
    });

    var persona = personas[0];

    var dataNit = {
        id: persona.nit.id,
        text: persona.nit.numero_documento + ' - ' + persona.nit.nombre_completo
    };
    var newOption = new Option(dataNit.text, dataNit.id, false, false);
    $comboNitCuotasMuldas.append(newOption).trigger('change');
    $comboNitCuotasMuldas.val(dataNit.id).trigger('change');

    $('#id_nit_cuotas_multas').prop('disabled',false);
});

$(document).on('change', '#id_concepto_facturacion_cuotas_multas', function () {
    var data = $(this).select2('data');
    if (!data) return;
    if (!data.length) return;

    data = data[0];
    $('#valor_cuotas_multas').val(new Intl.NumberFormat("ja-JP").format(data.valor));
});

$(document).on('change', '#id_concepto_filter_cuotas_multas', function () {
    cuotas_multas_table.context[0].jqXHR.abort();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
    });
});

$(document).on('change', '#fecha_desde_cuotas_multas', function () {
    cuotas_multas_table.context[0].jqXHR.abort();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
    });
});

$(document).on('change', '#fecha_hasta_cuotas_multas', function () {
    cuotas_multas_table.context[0].jqXHR.abort();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
    });
});

$(document).on('click', '#reloadCuotasMultas', function () {
    console.log('reloadCuotasMultas');
    $("#reloadCuotasMultasIconNormal").hide();
    $("#reloadCuotasMultasIconLoading").show();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
        setTimeout(function(){
            $("#reloadCuotasMultasIconNormal").show();
            $("#reloadCuotasMultasIconLoading").hide();
        },500);
    });
});

function searchCuotaMulta (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValue = $('#searchInputCuotasMultas').val();
    searchValue = searchValue+botonPrecionado;
    if(event.key == 'Backspace') searchValue = searchValue.slice(0, -1);

    cuotas_multas_table.context[0].jqXHR.abort();
    cuotas_multas_table.ajax.reload(function () {
        getTotalesCuotasMultas();
    });
}

function getTotalesCuotasMultas(){
    $.ajax({
        url: base_url + 'cuotasmultas-total',
        method: 'GET',
        headers: headers,
        data: {
            fecha_desde: $('#fecha_desde_cuotas_multas').val(),
            fecha_hasta: $('#fecha_hasta_cuotas_multas').val(),
            id_concepto: $('#id_concepto_filter_cuotas_multas').val(),
            search: searchValue
        },
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            var countA = new CountUp('total_valor_cuotasmultas', 0, res.data.total, 2, 0.5);
                countA.start();
        }
    }).fail((err) => {
        agregarToast('error', 'Consulta errada', 'Error al consultar totales!');
    });
}

function ocultarCampos() {
    var campos = [
        'id_zona_cuotas_multas',
        'tipo_concepto_cuotas_multas',
        'id_concepto_tipo_facturacion_cuotas_multas',
        'id_cuotamulta_cuotas_multas',
        'id_inmueble_cuotas_multas',
        'id_nit_cuotas_multas',
        'fecha_inicio_cuotas_multas',
        'fecha_fin_cuotas_multas',
        'id_concepto_facturacion_cuotas_multas',
        'valor_cuotas_multas',
        'observacion_cuotas_multas',
    ];

    campos.forEach(campo => {
        $('#input_'+campo).hide();
        $('#'+campo).prop('required',false);
    });
}

function cuotasConceptosIndividual() {
    var campos = [
        'tipo_concepto_cuotas_multas',
        'id_inmueble_cuotas_multas',
        'id_nit_cuotas_multas',
        'fecha_inicio_cuotas_multas',
        'fecha_fin_cuotas_multas',
        'id_concepto_facturacion_cuotas_multas',
        'valor_cuotas_multas',
        'observacion_cuotas_multas',
    ];

    campos.forEach(campo => {
        $('#input_'+campo).show();
        $('#'+campo).prop('required',true);
    });

    $('#id_nit_cuotas_multas').prop('disabled',true);
}

function cuotasConceptosMasivo() {
    var campos = [
        'tipo_concepto_cuotas_multas',
        'fecha_inicio_cuotas_multas',
        'fecha_fin_cuotas_multas',
        'id_concepto_facturacion_cuotas_multas',
        'valor_cuotas_multas',
        'observacion_cuotas_multas',
    ];

    $('#input_id_zona_cuotas_multas').show();
    $('#input_id_cuotamulta_cuotas_multas').show();
    $('#input_id_concepto_tipo_facturacion_cuotas_multas').show();

    campos.forEach(campo => {
        $('#input_'+campo).show();
        $('#'+campo).prop('required',true);
    });
}

function clearForm() {
    $('#input_masivo_cuotas_multas').show();
    $('#tipo_concepto_cuotas_multas').val(1);
    $('#id_concepto_tipo_facturacion_cuotas_multas').val('').change();
    $('#id_cuotamulta_cuotas_multas').val('').change();
    $('#id_inmueble_cuotas_multas').val('').change();
    $('#id_nit_cuotas_multas').val('').change();
    $('#id_concepto_facturacion_cuotas_multas').val('').change();
    $('#fecha_inicio_cuotas_multas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2));
    $('#fecha_fin_cuotas_multas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2));
    $('#valor_cuotas_multas').val(0);
    $('#observacion_cuotas_multas').val('');
}

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

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});