var proyecto_table = null;
$comboResponsableProyecto = null;

function proyectosInit() {
    proyecto_table = $('#proyectosTable').DataTable({
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
            url: base_url + 'proyectos',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.responsable) {
                    if (row.responsable.firstname && row.responsable.lastname) {
                        return row.responsable.firstname+' '+row.responsable.lastname;
                    }
                    return row.responsable.firstname;
                }
                return '';
            }},
            {"data": "valor_total",render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
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
                    if (editarProyectos) html+= '<span id="editproyecto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-proyecto" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarProyectos) html+= '<span id="deleteproyecto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-proyecto" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (proyecto_table) {
        //EDITAR ZONA
        proyecto_table.on('click', '.edit-proyecto', function() {
            clearFormProyecto();
            $("#textProyectoCreate").hide();
            $("#textProyectoUpdate").show();
            $("#saveProyectoLoading").hide();
            $("#updateProyecto").show();
            $("#saveProyecto").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, proyecto_table);

            if(data.responsable){
                var nombre = data.responsable.firstname;
                if (data.responsable.lastname) nombre+= ' '+data.responsable.lastname;
                var dataResponsable = {
                    id: data.responsable.id,
                    text: nombre
                };
                var newOption = new Option(dataResponsable.text, dataResponsable.id, false, false);
                $comboResponsableProyecto.append(newOption).trigger('change');
                $comboResponsableProyecto.val(dataResponsable.id).trigger('change');
            }

            $("#id_proyecto_up").val(data.id);
            $("#nombre_proyecto").val(data.nombre);
            $("#fecha_inicio_proyecto").val(data.fecha_inicio);
            $("#fecha_fin_proyecto").val(data.fecha_fin);
            $("#valor_proyecto").val(new Intl.NumberFormat("ja-JP").format(data.valor_total));

            $("#proyectoFormModal").modal('show');
        });
        //BORRAR ZONA
        proyecto_table.on('click', '.drop-proyecto', function() {
            var trProyecto = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, proyecto_table);

            Swal.fire({
                title: 'Eliminar proyecto: '+data.nombre+'?',
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
                        url: base_url + 'proyectos',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            proyecto_table.row(trProyecto).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Proyecto eliminada con exito!', true );
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

    let column = proyecto_table.column(6);

    if (!editarProyectos && !eliminarProyectos) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    proyecto_table.ajax.reload();

    $comboResponsableProyecto = $('#id_responsable_proyecto').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#proyectoFormModal'),
        delay: 250,
        placeholder: "Seleccione un usuario",
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
            url: base_url + 'usuarios/combo',
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
}

$(document).on('click', '#createProyecto', function () {
    clearFormProyecto();
    $("#saveProyecto").show();
    $("#updateProyecto").hide();
    $("#saveProyectoLoading").hide();
    $("#proyectoFormModal").modal('show');
});

$(document).on('click', '#saveProyecto', function () {
    var form = document.querySelector('#proyectoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveProyectoLoading").show();
    $("#updateProyecto").hide();
    $("#saveProyecto").hide();

    let data = {
        nombre: $("#nombre_proyecto").val(),
        id_usuario: $("#id_responsable_proyecto").val(),
        fecha_inicio: $("#fecha_inicio_proyecto").val(),
        fecha_fin: $("#fecha_fin_proyecto").val(),
        valor_total: stringToNumberFloat($("#valor_proyecto").val()),
    }

    $.ajax({
        url: base_url + 'proyectos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormProyecto();
            $("#saveProyecto").show();
            $("#saveProyectoLoading").hide();
            $("#proyectoFormModal").modal('hide');
            proyecto_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Proyecto creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveProyecto').show();
        $('#saveProyectoLoading').hide();
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

$(document).on('click', '#updateProyecto', function () {
    var form = document.querySelector('#proyectoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveProyectoLoading").show();
    $("#updateProyecto").hide();
    $("#saveProyecto").hide();

    let data = {
        id: $("#id_proyecto_up").val(),
        id_usuario: $("#id_responsable_proyecto").val(),
        nombre: $("#nombre_proyecto").val(),
        fecha_inicio: $("#fecha_inicio_proyecto").val(),
        fecha_fin: $("#fecha_fin_proyecto").val(),
        valor_total: stringToNumberFloat($("#valor_proyecto").val()),
    }

    $.ajax({
        url: base_url + 'proyectos',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormProyecto();
            $("#saveProyecto").show();
            $("#saveProyectoLoading").hide();
            $("#proyectoFormModal").modal('hide');
            proyecto_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Proyecto actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateProyecto').show();
        $('#saveProyectoLoading').hide();
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

function clearFormProyecto(){
    $("#textProyectoCreate").show();
    $("#textProyectoUpdate").hide();
    $("#saveProyectoLoading").hide();

    $("#id_proyectos_up").val('');
    $("#nombre_proyecto").val('');
    $("#fecha_inicio_proyecto").val('');
    $("#fecha_fin_proyecto").val('');
    $("#valor_proyecto").val(0);
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