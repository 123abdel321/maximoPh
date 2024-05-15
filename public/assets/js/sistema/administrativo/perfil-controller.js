var $comboCiudadPerfil = null
var $comboTipoDocumentoPerfil = null;

function perfilInit() {
    $comboTipoDocumentoPerfil = $('#id_tipo_documento_perfil').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitFormModal'),
        delay: 250,
        ajax: {
            url: base_url_erp+'nit/combo-tipo-documento',
            headers: headersERP,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $.ajax({
        url: base_url + 'perfil',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.data) {
            var data = res.data;

            if(data.tipo_documento){
                var dataCuenta = {
                    id: data.tipo_documento.id,
                    text: data.tipo_documento.codigo + ' - ' + data.tipo_documento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboTipoDocumentoPerfil.append(newOption).trigger('change');
                $comboTipoDocumentoPerfil.val(dataCuenta.id).trigger('change');
            }

            $("#numero_documento_perfil").val(data.numero_documento);
            $("#primer_nombre_perfil").val(data.primer_apellido);
            $("#otros_nombres_perfil").val(data.otros_nombres);
            $("#primer_apellido_perfil").val(data.primer_nombre);
            $("#segundo_apellido_perfil").val(data.segundo_apellido);
            $("#email_perfil").val(data.email);
            $("#telefono_1_perfil").val(data.telefono_1);
        }
    }).fail((err) => {
    });
}

function readURLperfil(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_perfil').attr('src', e.target.result);
            $('#new_avatar_perfil').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_perfil').hide();
        $('#new_avatar_perfil').show();

        $("#updatePerfil").hide();
        $("#updatePerfilLoading").show();

        var ajxForm = document.getElementById("perfil-imagen");
        var data = new FormData(ajxForm);
        var xhr = new XMLHttpRequest();

        xhr.open("POST", "perfil-avatar");
        xhr.send(data);
        xhr.onload = function(res) {
            var responseData = JSON.parse(res.currentTarget.response);
            $("#updatePerfil").show();
            $("#updatePerfilLoading").hide();
        };
        xhr.onerror = function (res) {
            $("#updatePerfil").show();
            $("#updatePerfilLoading").hide();
            console.log('res: ',res);
        };
    }
}

function readURLFondoSistemaPerfil(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            fondoSistema = e.target.result;
            $('#empresa_fondo_sistema').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_fondo_sistema').hide();
        $('#empresa_fondo_sistema').show();

        $("#updatePerfil").hide();
        $("#updatePerfilLoading").show();

        var ajxForm = document.getElementById("fondo-imagen-perfil");
        var data = new FormData(ajxForm);
        var xhr = new XMLHttpRequest();

        xhr.open("POST", "perfil-fondo");
        xhr.send(data);
        xhr.onload = function(res) {
            var responseData = JSON.parse(res.currentTarget.response);
            $("#updatePerfil").show();
            $("#updatePerfilLoading").hide();

            setTimeout(function(){
                $(".fondo-sistema").css('background-image', 'url(' +bucketUrl + responseData.url+ ')');
            },100);
        };
        xhr.onerror = function (res) {
            $("#updatePerfil").show();
            $("#updatePerfilLoading").hide();
            console.log('res: ',res);
        };
    }
}

function validateUserPasswordPerfil(newPassword = true) {
    var contrasena = $("#password_usuario_perfil").val();
    var confirmarContrasena = $("#password_confirm_perfil").val();

    if (newPassword) {
        if (!contrasena || !confirmarContrasena) {
            $('#password_confirm_perfil').removeClass("is-valid");
            $('#password_confirm_perfil').addClass("is-invalid");
            $('#password-error-perfil').text('La contraseña es obligatoria');
            return false;
        }
    }

    if (contrasena != confirmarContrasena) {
        $('#password_confirm_perfil').removeClass("is-valid");
        $('#password_confirm_perfil').addClass("is-invalid");
        $('#password-error-perfil').text('Las contraseñas no coinciden');
        return false;
    }

    $('#password_confirm_perfil').addClass("is-valid");
    $('#password_confirm_perfil').removeClass("is-invalid");

    return true;
}

$(document).on('click', '#updatePerfil', function () {

    if (!validateUserPasswordPerfil(false)) {
        return;
    }

    $("#updatePerfilLoading").show();
    $("#updatePerfil").hide();

    let data = {
        id_tipo_documento: $("#id_tipo_documento_perfil").val(),
        numero_documento: $('#numero_documento_perfil').val(),
        primer_nombre: $('#primer_nombre_perfil').val(),
        otros_nombres: $('#otros_nombres_perfil').val(),
        primer_apellido: $('#primer_apellido_perfil').val(),
        segundo_apellido: $('#segundo_apellido_perfil').val(),
        email: $('#email_perfil').val(),
        telefono_1: $('#telefono_1_perfil').val(),
    }

    $.ajax({
        url: base_url + 'perfil',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#updatePerfil').show();
            $('#updatePerfilLoading').hide();
            agregarToast('exito', 'Actualización exitosa', 'Perfil actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updatePerfil').show();
        $('#updatePerfilLoading').hide();
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