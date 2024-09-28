var roles_table = null;
var permisosRoles = [];

function rolesInit() {
    roles_table = $('#rolTable').DataTable({
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
            url: base_url + 'roles',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.id_empresa) {
                    return 'PROPIA';
                }
                return 'MAXIMOPH';
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
                    if (row.id_empresa != 0 && editarRol) html+= '<span id="editrol_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-rol" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (row.id_empresa != 0 && eliminarRol) html+= '<span id="deleterol_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-rol" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (roles_table) {
        //EDITAR ROLES
        roles_table.on('click', '.edit-rol', function() {
            clearFormRoles();
            clearPermisos();
            $("#textRolCreate").hide();
            $("#textRolUpdate").show();
            $("#saveRolLoading").hide();
            $("#updateRol").show();
            $("#saveRol").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, roles_table);
            var permisos = data.ids_permission.split(',');
            
            for (let index = 0; index < permisosRoles.length; index++) {
                const permiso = permisosRoles[index];
                let indexPermiso = permisos.indexOf(permiso.id_permiso+'');
                if (indexPermiso >= 0) {
                    $('#permiso_'+permiso.name).prop('checked', true);
                }
            }            
            
            $("#id_rol_up").val(data.id);
            $("#nombre_rol").val(data.nombre);
            $("#rolFormModal").modal('show');
        });
        //BORRAR ROLES
        roles_table.on('click', '.drop-rol', function() {
            var trRol = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, roles_table);

            Swal.fire({
                title: 'Eliminar rol: '+data.nombre+'?',
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
                        url: base_url + 'roles',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            roles_table.row(trRol).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Rol eliminado con exito!', true );
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

    if (componentesRoles && componentesRoles.length > 0) {
        for (let i = 0; i < componentesRoles.length; i++) {
            const componente = componentesRoles[i];
            
            for (let j = 0; j < componente.hijos.length; j++) {
                const hijos = componente.hijos[j];
                for (let k = 0; k < hijos.permisos.length; k++) {
                    const permiso = hijos.permisos[k];
                    var permisoNombre = permiso.name.split(' ');
                    permisosRoles.push({
                        name: permisoNombre[0]+'_'+permisoNombre[1],
                        id_permiso: permiso.id,
                        value: false
                    });
                }
            }
        }
    }

    $('.water').hide();
    roles_table.ajax.reload();
}

$(document).on('click', '#createRol', function () {
    clearFormRoles();
    clearPermisos();
    $("#saveRol").show();
    $("#updateRol").hide();
    $("#saveRolLoading").hide();
    $("#rolFormModal").modal('show');
});

$(document).on('click', '#updateRol', function () {
    var form = document.querySelector('#rolForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveRolLoading").show();
    $("#updateRol").hide();
    $("#saveRol").hide();

    let data = {
        id: $("#id_rol_up").val(),
        nombre: $("#nombre_rol").val(),
        permisos: getPermisos()
    }

    $.ajax({
        url: base_url + 'roles',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormRoles();
            $("#saveRol").show();
            $("#updateRol").hide();
            $("#saveRolLoading").hide();
            $("#rolFormModal").modal('hide');
            roles_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Rol actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#saveRol').hide();
        $('#updateRol').show();
        $('#saveRolLoading').hide();
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

$(document).on('click', '#saveRol', function () {
    var form = document.querySelector('#rolForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveRolLoading").show();
    $("#updateRol").hide();
    $("#saveRol").hide();

    let data = {
        nombre: $("#nombre_rol").val(),
        permisos: getPermisos()
    }

    $.ajax({
        url: base_url + 'roles',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormRoles();
            $("#saveRol").show();
            $("#updateRol").hide();
            $("#saveRolLoading").hide();
            $("#rolFormModal").modal('hide');
            // usuarios_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Rol creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveRol').show();
        $('#saveRolLoading').hide();
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


function clearFormRoles () {
    $("#nombre_rol").val('');
}

function getPermisos() {
    var idPermisos = '';
    for (let index = 0; index < permisosRoles.length; index++) {
        const permiso = permisosRoles[index];
        var check = $("input[type='checkbox']#permiso_"+permiso.name).is(':checked') ? '1' : '';
        if (check) {
            if(idPermisos) idPermisos+=','+permiso.id_permiso;
            else idPermisos+=''+permiso.id_permiso;
        }
        permiso.value = check;
    }

    return idPermisos;
}

function clearPermisos() {
    for (let index = 0; index < permisosRoles.length; index++) {
        const permiso = permisosRoles[index];
        permiso.value = ''
    }

    permisosRoles.forEach(permiso => {
        $('#permiso_'+permiso.name).prop('checked', false);
    });
}