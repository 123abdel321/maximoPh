var zona_table = null;
$comboCecosZona = null;

function zonaInit() {
    zona_table = $('#zonaTable').DataTable({
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
            url: base_url + 'zona',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.cecos) {
                    return row.cecos.codigo+' - '+row.cecos.nombre;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.tipo == 1) {
                    return 'INMUEBLE';
                }
                if (row.tipo == 2) {
                    return 'PORTERIA';
                }
                return 'USO COMÚN';
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
                    if (editarZona) html+= '<span id="editzona_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-zona" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarZona) html+= '<span id="deletezona_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-zona" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (zona_table) {
        //EDITAR ZONA
        zona_table.on('click', '.edit-zona', function() {
            clearFormZona();
            $("#textZonaCreate").hide();
            $("#textZonaUpdate").show();
            $("#saveZonaLoading").hide();
            $("#updateZona").show();
            $("#saveZona").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, zona_table);

            if(data.cecos) {
                var dataCecos = {
                    id: data.cecos.id,
                    text: data.cecos.codigo+' - '+data.cecos.nombre
                };
                var newOption = new Option(dataCecos.text, dataCecos.id, false, false);
                $comboCecosZona.append(newOption).trigger('change');
                $comboCecosZona.val(dataCecos.id).trigger('change');
            }

            $("#id_zona_up").val(data.id);
            $("#nombre_zona").val(data.nombre);
            $("#tipo_zona").val(data.tipo);

            $("#zonaFormModal").modal('show');
        });
        //BORRAR ZONA
        zona_table.on('click', '.drop-zona', function() {
            var trZona = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, zona_table);

            Swal.fire({
                title: 'Eliminar zona: '+data.nombre+'?',
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
                        url: base_url + 'zona',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            zona_table.row(trZona).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Zona eliminada con exito!', true );
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

    $comboCecosZona = $('#id_centro_costos_zona').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#zonaFormModal'),
        delay: 250,
        placeholder: "Seleccione un centro de costos",
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

    let column = zona_table.column(5);

    if (!editarZona && !eliminarZona) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    zona_table.ajax.reload();
}

$(document).on('click', '#createZona', function () {
    clearFormZona();
    $("#saveZona").show();
    $("#updateZona").hide();
    $("#saveZonaLoading").hide();
    $("#zonaFormModal").modal('show');
});

$(document).on('click', '#saveZona', function () {
    var form = document.querySelector('#zonaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveZonaLoading").show();
    $("#updateZona").hide();
    $("#saveZona").hide();

    let data = {
        id_centro_costos: $("#id_centro_costos_zona").val(),
        nombre: $("#nombre_zona").val(),
        tipo: $("#tipo_zona").val(),
    }

    $.ajax({
        url: base_url + 'zona',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormZona();
            $("#saveZona").show();
            $("#saveZonaLoading").hide();
            $("#zonaFormModal").modal('hide');
            zona_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Zona creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveZona').show();
        $('#saveZonaLoading').hide();
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

$(document).on('click', '#updateZona', function () {
    var form = document.querySelector('#zonaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveZonaLoading").show();
    $("#updateZona").hide();
    $("#saveZona").hide();

    let data = {
        id: $("#id_zona_up").val(),
        id_centro_costos: $("#id_centro_costos_zona").val(),
        nombre: $("#nombre_zona").val(),
        tipo: $("#tipo_zona").val(),
    }

    $.ajax({
        url: base_url + 'zona',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormZona();
            $("#saveZona").show();
            $("#saveZonaLoading").hide();
            $("#zonaFormModal").modal('hide');
            zona_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Zona actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateZona').show();
        $('#saveZonaLoading').hide();
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

function clearFormZona(){
    $("#textZonaCreate").show();
    $("#textZonaUpdate").hide();
    $("#saveZonaLoading").hide();

    $("#id_zona_up").val('');
    $("#nombre_zona").val('');
    $("#tipo_zona").val(0);

    $comboCecosZona.val('').trigger('change');
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});