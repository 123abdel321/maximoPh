var inmueble_table = null;
var $comboZonaInmueble = null;
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
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.zona) {
                    return row.zona.nombre;
                }
                return '';
            }},
            {"data":'area', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'coeficiente', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'valor_total_administracion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
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
                    if (editarInmueble) html+= '<span id="editinmueble_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-inmueble" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarInmueble) html+= '<span id="deleteinmueble_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-inmueble" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
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
            
            $("#area_inmueble").val(new Intl.NumberFormat().format(data.area));
            $("#coeficiente_inmueble").val(data.coeficiente);
            $("#valor_total_administracion_inmueble").val(new Intl.NumberFormat().format(data.valor_total_administracion));

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
    }

    if (editar_valor_admon_inmueble) {
        document.getElementById("valor_total_administracion_inmueble").removeAttribute("disabled");
    } else {
        document.getElementById("valor_total_administracion_inmueble").setAttribute("disabled", true);
    }

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

    let column = inmueble_table.column(7);

    if (!editarInmueble && !eliminarInmueble) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    inmueble_table.ajax.reload();
}

$(document).on('click', '#createInmuebles', function () {
    clearFormInmueble();
    $("#saveInmueble").show();
    $("#updateInmueble").hide();
    $("#saveInmuebleLoading").hide();
    $("#inmuebleFormModal").modal('show');
});

function changeArea(){
    if ($("#id_inmueble_up").val() && editar_valor_admon_inmueble) return;

    var area = stringToNumberFloat($('#area_inmueble').val());
    var coeficiente = area / area_total_m2;
    var totalInmueble = coeficiente * valor_total_presupuesto_year_actual;

    $('#coeficiente_inmueble').val(coeficiente);
    $('#valor_total_administracion_inmueble').val(totalInmueble);
    formatCurrency($('#valor_total_administracion_inmueble'));
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
            inmueble_table.row.add(res.data).draw();
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