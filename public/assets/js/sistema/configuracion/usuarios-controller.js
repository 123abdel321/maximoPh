var usuarios_table = null;
var $comboNitUsuario = null;
var $comboBodegaUsuario = null;
var $comboNitUsuarioFilter = null;
var $comboResolucionUsuario = null;

function usuariosInit() {
    
    usuarios_table =  $('#usuariosTable').DataTable({
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
            url: base_url + 'usuarios',
            data: function ( d ) {
                d.id_nit = $("#id_nit_usuario_filter").val(),
                d.id_rol = $("#id_rol_usuario_filter").val(),
                d.search = $("#searchInputUsuarios").val()
            }
        },
        columns: [
            {"data":'username'},
            {"data":'nombre_rol'},
            {"data":'nombre_completo'},
            {"data":'firstname'},
            {"data":'email'},
            {"data":'telefono'},
            {"data":'address'},
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
                    html+= '<span id="editusuarios_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    // if (eliminarUsuarios) html+= '<span id="deleteusuarios_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    let column = usuarios_table.column(8);
    
    if (!editarUsuarios && !eliminarUsuarios) column.visible(false);
    else column.visible(true);

    if (usuarios_table) {
        usuarios_table.on('click', '.edit-usuarios', function() {
            $("#textUsuariosCreate").hide();
            $("#textUsuariosUpdate").show();
            $("#saveUsuariosLoading").hide();
            $("#updateUsuarios").show();
            $("#saveUsuarios").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, usuarios_table);
            console.log(data);
            $('#password_usuario').val('');
            $('#password_confirm').val('');
            $("#id_usuarios_up").val(data.id);
            $("#rol_usuario").val(data.id_rol).change();
            $("#usuario").val(data.username);
            $("#email_usuario").val(data.email);
            $("#firstname_usuario").val(data.firstname);
            $("#lastname_usuario").val(data.lastname);
            $("#address_usuario").val(data.address);

            if(data.id_nit) {
                var dataNit = {
                    id: data.id_nit,
                    text: data.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitUsuario.append(newOption).trigger('change');
                $comboNitUsuario.val(dataNit.id).trigger('change');
            }

            if (data.id_rol == 1) $("#div-id_nit_usuario").hide();
            else $("#div-id_nit_usuario").show();
    
            $("#usuariosFormModal").modal('show');
        });
    }

    $comboResolucionUsuario = $('#id_resolucion_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $comboBodegaUsuario = $('#id_bodega_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $('#id_nit_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
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

    $comboNitUsuario = $('#id_nit_usuario').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#usuariosFormModal'),
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

    $comboNitUsuarioFilter = $('#id_nit_usuario_filter').select2({
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

    $(document).on('change', '#rol_usuario', function () {
        var id_rol = $('#rol_usuario').val();
        if (id_rol == 1) $("#div-id_nit_usuario").hide();
        else $("#div-id_nit_usuario").show();
    });

    $(document).on('change', '#id_rol_usuario_filter', function () {
        usuarios_table.ajax.reload();
    });

    $(document).on('change', '#id_nit_usuario_filter', function () {
        usuarios_table.ajax.reload();
    });

    $(document).on('change', '#id_nit_usuario', function () {
        var data = $('#id_nit_usuario').select2('data');
        if (data.length == 0) return;
        data = data[0];
        if (!data.email) agregarToast('error', 'Nit incompleto!', 'El nit no tiene email signado');
        else $("#email_usuario").val(data.email);

        if (data.primer_nombre) $("#firstname_usuario").val(data.primer_nombre);
        if (data.primer_apellido) $("#lastname_usuario").val(data.primer_apellido);
        if (data.telefono_1) $("#telefono_usuario").val(data.telefono_1);
        if (data.direccion) $("#address_usuario").val(data.direccion);
    });

    $('.water').hide();
    usuarios_table.ajax.reload();
}

$("#searchInputUsuarios").on("input", function (e) {
    usuarios_table.context[0].jqXHR.abort();
    $('#usuariosTable').DataTable().search($("#searchInputUsuarios").val()).draw();
});

$(document).on('click', '#createUsuarios', function () {
    clearFormUsuarios();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").show();
    $("#usuariosFormModal").modal('show');
});

function clearFormUsuarios(){
    $("#textUsuariosCreate").show();
    $("#textUsuariosUpdate").hide();
    $("#saveUsuariosLoading").hide();

    $('#rol_usuario').val("2");
    $("#div-id_nit_usuario").show();
    $("#id_usuarios_up").val('');
    $("#id_nit_usuario").val('').change();
    $("#usuario").val('');
    $("#id_nit_usuario_filter").val('').change();
    $("#email_usuario").val('');
    $("#firstname_usuario").val('');
    $("#lastname_usuario").val('');
    $("#address_usuario").val('');
    $("#password_usuario").val('');
    $("#id_bodega_usuario").val('').change();
    $("#id_resolucion_usuario").val('').change();
    $("#password_confirm").val('');
    $("#telefono_usuario").val('');

}

function usuarioNombre(event){
    if (event.keyCode == 8) {
        return true;
    }

    patron = /[A-Za-z0-9]/;
    tecla_final = String.fromCharCode(event.keyCode);

    return patron.test(tecla_final);
}

$(document).on('click', '#saveUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (!validateUserPassword()) {
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        usuario: $("#usuario").val(),
        email: $("#email_usuario").val(),
        firstname: $("#firstname_usuario").val(),
        lastname: $("#lastname_usuario").val(),
        address: $("#address_usuario").val(),
        password: $("#password_usuario").val(),
        telefono: $("#telefono_usuario").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_nit: $("#id_nit_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        rol_usuario: $("#rol_usuario").val()
    }

    $.ajax({
        url: base_url + 'usuarios',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUsuarios();
            $("#saveUsuarios").show();
            $("#updateUsuarios").hide();
            $("#saveUsuariosLoading").hide();
            $("#usuariosFormModal").modal('hide');
            usuarios_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Usuario creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveUsuarios').show();
        $('#saveUsuariosLoading').hide();
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

function validateUserPassword(newPassword = true) {
    var contrasena = $("#password_usuario").val();
    var confirmarContrasena = $("#password_confirm").val();

    if (newPassword) {
        if (!contrasena || !confirmarContrasena) {
            $('#password_confirm').removeClass("is-valid");
            $('#password_confirm').addClass("is-invalid");
            $('#password-error-username').text('La contraseña es obligatoria');
            return false;
        }
    }

    if (contrasena != confirmarContrasena) {
        $('#password_confirm').removeClass("is-valid");
        $('#password_confirm').addClass("is-invalid");
        $('#password-error-username').text('Las contraseñas no coinciden');
        return false;
    }

    $('#password_confirm').addClass("is-valid");
    $('#password_confirm').removeClass("is-invalid");

    return true;
}

$(document).on('click', '#updateUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (!validateUserPassword(false)) {
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        id: $("#id_usuarios_up").val(),
        usuario: $("#usuario").val(),
        email: $("#email_usuario").val(),
        firstname: $("#firstname_usuario").val(),
        lastname: $("#lastname_usuario").val(),
        address: $("#address_usuario").val(),
        password: $("#password_usuario").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        id_nit: $("#id_nit_usuario").val(),
        telefono: $("#telefono_usuario").val(),
        rol_usuario: $("#rol_usuario").val()
    }

    $.ajax({
        url: base_url + 'usuarios',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUsuarios();
            $("#saveUsuarios").show();
            $("#updateUsuarios").hide();
            $("#saveUsuariosLoading").hide();
            $("#usuariosFormModal").modal('hide');
            usuarios_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Usuario creado con exito!', true);
        }
    }).fail((err) => {
        $('#updateUsuarios').show();
        $('#saveUsuariosLoading').hide();
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