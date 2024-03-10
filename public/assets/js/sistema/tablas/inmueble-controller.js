var id_inmueble = null;
var valor_inmueble = null;
var inmueble_table = null;
var inmueble_nit_table = null;
var $comboInmuebleNit = null;
var $comboZonaInmueble = null;
var searchValueInmuebles = null;
var buscarTotalesInmuebles = false;
var $comboConceptoFacturacionInmueble = null;

function inmuebleInit() {
    inmueble_table = $('#inmuebleTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        stateSave: true,
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
            url: base_url + 'inmueble',
            data: function ( d ) {
                d.search = searchValueInmuebles;
            }
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                if (row.personas.length) {
                    var persona = row.personas[0].nit;
                    return persona.numero_documento;
                }
                return '';
            }},
            {
                data: 'id',
                render: function (row, type, data){
                    if (data.personas.length) {
                        var persona = data.personas[0].nit;
                        return persona.primer_nombre+' '+persona.primer_apellido;
                    }
                    return '';
                }
            },
            {"data": function (row, type, set){
                if (row.zona) {
                    return row.zona.nombre;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.concepto) {
                    return row.concepto.nombre_concepto;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (typeof row.personas != "undefined" && row.personas.length) {
                    var totalPorcentaje = 0;
                    for (let index = 0; index < row.personas.length; index++) {
                        const personas = row.personas[index];
                        totalPorcentaje+= (personas.porcentaje_administracion*1);
                    }
                    if (totalPorcentaje == 100) {
                        return '<span class="badge rounded-pill bg-success">'+totalPorcentaje+'%</span>';
                    }
                    return '<span class="badge rounded-pill bg-danger">'+totalPorcentaje+'%</span>';
                }
                return '<span class="badge rounded-pill bg-danger">0%</span>';
            }, className: 'dt-body-right'},
            {"data":'area', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                data: 'coeficiente',
                render: function (row, type, data){
                    return parseFloat(data.coeficiente * 100).toFixed(2);
                }, className: 'dt-body-right'
            },
            // {"data":'valor_total_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (typeof row.personas != "undefined" && row.personas.length) {
                    var totalValor = 0;
                    for (let index = 0; index < row.personas.length; index++) {
                        const personas = row.personas[index];
                        totalValor+= (personas.valor_total*1);
                    }
                    return totalValor;
                }
                return 0;
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
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
                    if (editarInmueble) html+= '<span id="addnitinmueble_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary add-nit-inmueble" style="margin-bottom: 0rem !important; min-width: 50px;">Propietarios</span>&nbsp;';
                    if (editarInmueble) html+= '<span id="editinmueble_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-inmueble" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarInmueble) html+= '<span id="deleteinmueble_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-inmueble" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    inmueble_nit_table = $('#inmuebleNitTable').DataTable({
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
            url: base_url + 'inmueble-nit',
            data: function ( d ) {
                d.id_inmueble = id_inmueble;
            }
        },
        columns: [
            {"data":'porcentaje_administracion'},
            {"data": function (row, type, set){  
                if (row.nit) {
                    return row.nit.numero_documento+' - '+row.nit.nombre_completo;
                }
            }},
            {"data": function (row, type, set){  
                if (row.tipo) {
                    return 'INQUILINO';
                }
                return 'PROPIETARIO';
            }},
            {"data":'valor_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (row.enviar_notificaciones_mail) {
                    return 'SI';
                }
                return 'NO';
            }},
            {"data": function (row, type, set){  
                if (row.enviar_notificaciones_fisica) {
                    return 'SI';
                }
                return 'NO';
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
                    if (editarInmueble) html+= '<span id="editinmueblenit_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-inmueble-nit" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (editarInmueble) html+= '<span id="deleteinmueblenit_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-inmueble-nit" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (inmueble_table) {
        //EDITAR INMUEBLE
        inmueble_table.on('click', '.edit-inmueble', function() {
            clearFormInmueble();
            $("#textInmuebleCreate").hide();
            $("#textInmuebleUpdate").show();
            $("#saveInmuebleLoading").hide();
            $("#updateInmueble").show();
            $("#saveInmueble").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, inmueble_table);

            if(data.zona) {
                var dataZona = {
                    id: data.zona.id,
                    text: data.zona.nombre
                };
                var newOption = new Option(dataZona.text, dataZona.id, false, false);
                $comboZonaInmueble.append(newOption).trigger('change');
                $comboZonaInmueble.val(dataZona.id).trigger('change');
            }

            if(data.concepto) {
                var dataConceptoFacturacion = {
                    id: data.concepto.id,
                    text: data.concepto.nombre_concepto
                };
                var newOption = new Option(dataConceptoFacturacion.text, dataConceptoFacturacion.id, false, false);
                $comboConceptoFacturacionInmueble.append(newOption).trigger('change');
                $comboConceptoFacturacionInmueble.val(dataConceptoFacturacion.id).trigger('change');
            }

            $("#id_inmueble_up").val(data.id);
            $("#nombre_inmueble").val(data.nombre);

            var area = data.area;
            var coeficiente = area / area_total_m2 ;
            var totalInmueble = data.valor_total_administracion;

            if (!editar_valor_admon_inmueble) {
                var coeficiente = data.area / area_total_m2;
                totalInmueble = coeficiente * (valor_total_presupuesto_year_actual / 12);
            }
            
            $("#area_inmueble").val(new Intl.NumberFormat("ja-JP").format(data.area));
            $("#coeficiente_inmueble").val(data.coeficiente);
            $("#valor_total_administracion_inmueble").val(new Intl.NumberFormat("ja-JP").format(totalInmueble));

            $("#inmuebleFormModal").modal('show');
        });
        //BORRAR INMUEBLE
        inmueble_table.on('click', '.drop-inmueble', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, inmueble_table);

            Swal.fire({
                title: 'Eliminar inmueble: '+data.nombre+'?',
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
                        url: base_url + 'inmueble',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            inmueble_table.row(trInmueble).remove().draw();
                            getTotalesInmuebles();
                            agregarToast('exito', 'Eliminación exitosa', 'Inmueble eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //AGREGAR NIT INMUEBLE
        inmueble_table.on('click', '.add-nit-inmueble', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, inmueble_table);

            id_inmueble = data.id;
            valor_inmueble = data.valor_total_administracion;

            $('#volverInmuebles').show();
            $('#createInmueblesNit').show();
            $('#tablas_inmuebles_nits').show();
            $('#nombre_inmueble_nit').show();
            
            $('#createInmuebles').hide();
            $('#tablas_inmuebles').hide();
            $('#totales_inmuebles_view').hide();
            $('#searchInputInmuebles').hide();

            $("#nombre_inmueble_nit").html(data.zona.nombre+' - '+data.nombre);

            inmueble_nit_table.ajax.reload(function(res) {
                totalPorcentajeNits();
            });
        });
    }

    if (inmueble_nit_table) {
        //EDITAR NIT INMUEBLE
        inmueble_nit_table.on('click', '.edit-inmueble-nit', function() {
            clearFormInmuebleNit();
            $("#textInmuebleNitCreate").hide();
            $("#textInmuebleNitUpdate").show();
            $("#saveInmuebleNitLoading").hide();
            $("#updateInmuebleNit").show();
            $("#saveInmuebleNit").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, inmueble_nit_table);

            if(data.nit) {
                var dataNit = {
                    id: data.nit.id,
                    text: data.nit.numero_documento+' - '+data.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboInmuebleNit.append(newOption).trigger('change');
                $comboInmuebleNit.val(dataNit.id).trigger('change');
            }

            $("#id_inmueble_nit_up").val(data.id);
            $("#tipo_inmueble_nit").val(data.tipo);
            $("#valor_total_inmueble_nit").val(new Intl.NumberFormat("ja-JP").format(data.valor_total));
            $("#porcentaje_administracion_inmueble_nit").val(data.porcentaje_administracion);

            if (data.enviar_notificaciones_mail == '1') $('#enviar_notificaciones_mail').prop('checked', true);
            else $('#enviar_notificaciones_mail').prop('checked', false);

            if (data.enviar_notificaciones_fisica == '1') $('#enviar_notificaciones_fisica').prop('checked', true);
            else $('#enviar_notificaciones_fisica').prop('checked', false);

            $("#inmuebleNitFormModal").modal('show');
        });
        //BORRAR NIT INMUEBLE
        inmueble_nit_table.on('click', '.drop-inmueble-nit', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, inmueble_nit_table);

            Swal.fire({
                title: 'Eliminar nit: '+data.nit.numero_documento+' - '+data.nit.nombre_completo+'?',
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
                        url: base_url + 'inmueble-nit',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            inmueble_nit_table.row(trInmueble).remove().draw();
                            totalPorcentajeNits();
                            agregarToast('exito', 'Eliminación exitosa', 'Cédula / nit eliminado con exito!', true );
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

    if (editar_valor_admon_inmueble) {
        document.getElementById("valor_total_administracion_inmueble").removeAttribute("disabled");
    } else {
        document.getElementById("valor_total_administracion_inmueble").setAttribute("disabled", true);
    }

    $comboInmuebleNit = $('#id_nit_inmueble_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#inmuebleNitFormModal'),
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

    $comboZonaInmueble = $('#id_zona_inmueble').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#inmuebleFormModal'),
        delay: 250,
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

    $comboConceptoFacturacionInmueble = $('#id_concepto_facturacion_inmueble').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#inmuebleFormModal'),
        delay: 250,
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

    let column = inmueble_table.column(7);

    if (!editarInmueble && !eliminarInmueble) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    inmueble_table.ajax.reload();
    getTotalesInmuebles();
}

$(document).on('click', '#createInmuebles', function () {
    clearFormInmueble();
    $("#saveInmueble").show();
    $("#updateInmueble").hide();
    $("#saveInmuebleLoading").hide();
    $("#inmuebleFormModal").modal('show');
});

$(document).on('click', '#createInmueblesNit', function () {
    clearFormInmuebleNit();
    $("#saveInmuebleNit").show();
    $("#updateInmuebleNit").hide();
    $("#saveInmuebleNitLoading").hide();
    $("#inmuebleNitFormModal").modal('show');
});

function changeArea(){
    if ($("#id_inmueble_up").val() && editar_valor_admon_inmueble) return;
    setTimeout(function(){
        var area = stringToNumberFloat($('#area_inmueble').val());

        var coeficiente = area / area_total_m2 ;
        var totalInmueble = coeficiente * (valor_total_presupuesto_year_actual / 12);
    
        $('#coeficiente_inmueble').val(coeficiente);
        $('#valor_total_administracion_inmueble').val(totalInmueble);
        formatCurrency($('#valor_total_administracion_inmueble'));
    },100);
}

function changePorcentajeNit(){
    var totalPorcentajeInmueble = stringToNumberFloat($("#porcentaje_administracion_inmueble_nit").val()) / 100;
    $('#valor_total_inmueble_nit').val(new Intl.NumberFormat("ja-JP").format(valor_inmueble * totalPorcentajeInmueble));
}

$(document).on('click', '#saveInmueble', function () {
    var form = document.querySelector('#inmueblesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveInmuebleLoading").show();
    $("#updateInmueble").hide();
    $("#saveInmueble").hide();

    let data = {
        id_zona: $("#id_zona_inmueble").val(),
        id_concepto_facturacion: $("#id_concepto_facturacion_inmueble").val(),
        nombre: $("#nombre_inmueble").val(),
        area: stringToNumberFloat($("#area_inmueble").val()),
        coeficiente: $("#coeficiente_inmueble").val(),
        valor_total_administracion: stringToNumberFloat($("#valor_total_administracion_inmueble").val()),
    }

    $.ajax({
        url: base_url + 'inmueble',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormInmueble();
            $("#saveInmueble").show();
            $("#saveInmuebleLoading").hide();
            $("#inmuebleFormModal").modal('hide');
            inmueble_table.row.add(res.data).draw();
            getTotalesInmuebles();
            agregarToast('exito', 'Creación exitosa', 'Inmueble creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveInmueble').show();
        $('#saveInmuebleLoading').hide();
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

$(document).on('click', '#saveInmuebleNit', function () {
    var form = document.querySelector('#inmueblesNitForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveInmuebleNitLoading").show();
    $("#updateInmuebleNit").hide();
    $("#saveInmuebleNit").hide();

    let data = {
        id_nit: $("#id_nit_inmueble_nit").val(),
        id_inmueble: id_inmueble,
        tipo: $("#tipo_inmueble_nit").val(),
        porcentaje_administracion: $("#porcentaje_administracion_inmueble_nit").val(),
        valor_total: stringToNumberFloat($("#valor_total_inmueble_nit").val()),
        enviar_notificaciones_mail: $("input[type='checkbox']#enviar_notificaciones_mail").is(':checked') ? '1' : '',
        enviar_notificaciones_fisica: $("input[type='checkbox']#enviar_notificaciones_fisica").is(':checked') ? '1' : '',
    }

    $.ajax({
        url: base_url + 'inmueble-nit',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormInmuebleNit();
            $("#saveInmuebleNit").show();
            $("#saveInmuebleNitLoading").hide();
            $("#inmuebleNitFormModal").modal('hide');
            inmueble_nit_table.ajax.reload(function(res) {
                totalPorcentajeNits();
            });
            agregarToast('exito', 'Creación exitosa', 'Asignación de persona exitosa!', true);
        }
    }).fail((err) => {
        $('#saveInmuebleNit').show();
        $('#saveInmuebleNitLoading').hide();
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

$(document).on('click', '#updateInmueble', function () {
    var form = document.querySelector('#inmueblesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveInmuebleLoading").show();
    $("#updateInmueble").hide();
    $("#saveInmueble").hide();

    let data = {
        id: $("#id_inmueble_up").val(),
        id_zona: $("#id_zona_inmueble").val(),
        id_concepto_facturacion: $("#id_concepto_facturacion_inmueble").val(),
        nombre: $("#nombre_inmueble").val(),
        area: stringToNumberFloat($("#area_inmueble").val()),
        coeficiente: $("#coeficiente_inmueble").val(),
        valor_total_administracion: stringToNumberFloat($("#valor_total_administracion_inmueble").val()),
    }

    $.ajax({
        url: base_url + 'inmueble',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormInmueble();
            $("#saveInmueble").show();
            $("#saveInmuebleLoading").hide();
            $("#inmuebleFormModal").modal('hide');
            inmueble_table.ajax.reload(null, false);
            getTotalesInmuebles();
            agregarToast('exito', 'Actualización exitosa', 'Inmueble actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updateInmueble').show();
        $('#saveInmuebleLoading').hide();
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

$(document).on('click', '#updateInmuebleNit', function () {
    var form = document.querySelector('#inmueblesNitForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveInmuebleNitLoading").show();
    $("#updateInmuebleNit").hide();
    $("#saveInmuebleNit").hide();

    let data = {
        id: $("#id_inmueble_nit_up").val(),
        id_nit: $("#id_nit_inmueble_nit").val(),
        id_inmueble: id_inmueble,
        tipo: $("#tipo_inmueble_nit").val(),
        porcentaje_administracion: $("#porcentaje_administracion_inmueble_nit").val(),
        valor_total: stringToNumberFloat($("#valor_total_inmueble_nit").val()),
        enviar_notificaciones_mail: $("input[type='checkbox']#enviar_notificaciones_mail").is(':checked') ? '1' : '',
        enviar_notificaciones_fisica: $("input[type='checkbox']#enviar_notificaciones_fisica").is(':checked') ? '1' : '',
    }

    $.ajax({
        url: base_url + 'inmueble-nit',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        console.log('res: ',res);
        if(res.success){
            clearFormInmuebleNit();
            $("#saveInmuebleNit").show();
            $("#saveInmuebleNitLoading").hide();
            $("#inmuebleNitFormModal").modal('hide');
            inmueble_nit_table.ajax.reload(function () {
                totalPorcentajeNits();
            });
            agregarToast('exito', 'Actualización exitosa', 'Asignación de persona exitosa!', true);
        }
    }).fail((err) => {
        $('#updateInmuebleNit').show();
        $('#saveInmuebleNitLoading').hide();
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

$(document).on('click', '#volverInmuebles', function () {
    $('#volverInmuebles').hide();
    $('#createInmueblesNit').hide();
    $('#nombre_inmueble_nit').hide();            
    $('#tablas_inmuebles_nits').hide();
    
    $('#createInmuebles').show();
    $('#tablas_inmuebles').show();
    $('#searchInputInmuebles').show();
    $('#totales_inmuebles_view').show();

    inmueble_table.ajax.reload( null, false );
    getTotalesInmuebles();
});

function getTotalesInmuebles(){
    if (buscarTotalesInmuebles) {
        buscarTotalesInmuebles.abort();
    }
    buscarTotalesInmuebles = $.ajax({
        url: base_url + 'inmueble-total',
        method: 'GET',
        headers: headers,
        data: {search: searchValueInmuebles},
        dataType: 'json',
    }).done((res) => {
        buscarTotalesInmuebles = false;
        if(res.success){
            var countA = new CountUp('inmuebles_registrados_inmueble', 0, res.data.numero_registro_unidades);
                countA.start();

            var countB = new CountUp('area2_registrados_inmueble', 0, res.data.area_registro_m2);
                countB.start();

            var countC = new CountUp('coeficiente_registrados_inmueble', 0, res.data.valor_registro_coeficiente);
                countC.start();

            var countD = new CountUp('presupuesto_registrados_inmueble', 0, res.data.valor_registro_presupuesto);
                countD.start();
        }
    }).fail((err) => {
        buscarTotalesInmuebles = false;
    });
}

function totalPorcentajeNits(){
    var dataNits = inmueble_nit_table.rows().data();
    var valorTotal = 0;
    var porcentajeTotal = 0;

    if (dataNits.length){
        for (let index = 0; index < dataNits.length; index++) {
            const nit = dataNits[index];
            valorTotal+= (nit.valor_total*1);
            porcentajeTotal+= (nit.porcentaje_administracion*1);
        }
    }

    if(porcentajeTotal == 100) {
        $('#status_inmueble_nit_false').hide();
        $('#status_inmueble_nit_true').show();
    } else {
        $('#status_inmueble_nit_false').show();
        $('#status_inmueble_nit_true').hide();
    }

    $('#total_porcentaje_inmueble_nit').text('Total porcentaje: '+new Intl.NumberFormat("ja-JP").format(porcentajeTotal)+'%');
    $('#total_valor_inmueble_nit').text('Total valor: '+new Intl.NumberFormat("ja-JP").format(valorTotal));
}

function clearFormInmueble(){
    $("#textInmuebleCreate").show();
    $("#textInmuebleUpdate").hide();
    $("#saveInmuebleLoading").hide();

    $("#id_inmueble_up").val('');
    $("#nombre_inmueble").val('');
    $("#area_inmueble").val(0);
    $("#coeficiente_inmueble").val(0);
    $("#valor_total_administracion_inmueble").val(0);
    
    $comboZonaInmueble.val('').trigger('change');
    $comboConceptoFacturacionInmueble.val('').trigger('change');
}

function clearFormInmuebleNit(){
    $("#textInmuebleNitCreate").show();
    $("#textInmuebleNitUpdate").hide();
    $("#saveInmuebleNitLoading").hide();

    $("#id_inmueble_nit_up").val('');
    $("#tipo_inmueble_nit").val(0);
    $("#valor_total_inmueble_nit").val(0);
    $("#porcentaje_administracion_inmueble_nit").val(100);
    $("#enviar_notificaciones_mail").prop('checked', true);
    $("#enviar_notificaciones_fisica").prop('checked', true);
    changePorcentajeNit();
    $comboInmuebleNit.val('').trigger('change');
}

function searchInmuebles (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValueInmuebles = $('#searchInputInmuebles').val();
    searchValueInmuebles = searchValueInmuebles+botonPrecionado;
    if(event.key == 'Backspace') searchValueInmuebles = searchValueInmuebles.slice(0, -1);

    inmueble_table.context[0].jqXHR.abort();
    inmueble_table.ajax.reload(function () {
        getTotalesInmuebles();
    });
}

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