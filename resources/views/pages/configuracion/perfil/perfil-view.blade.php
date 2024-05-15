<style>

</style>


<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body row">

                <form class="col-12 col-sm-4 col-md-3" id="perfil-imagen" style="margin-top: 10px;" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div>
                        <label for="example-text-input" class="form-control-label">Imagen de perfil</label>
                        <div style="text-align: -webkit-center; height: 100px;">
                            <img id="default_avatar_perfil" onclick="document.getElementById('imagen_perfil').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 100px; height: auto; cursor: pointer; border-radius: 5%;">
                            <img id="new_avatar_perfil" onclick="document.getElementById('imagen_perfil').click();" src="" class="img-fluid border border-2 border-white" style="width: 100px; height: auto; cursor: pointer; border-radius: 5%;">
                        </div>
    
                        <input type="file" name="imagen_perfil" id="imagen_perfil" onchange="readURLperfil(this);" style="display: none" />
                    </div>
                </form>

                <div class="form-group form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Contraseña</label>
                    <input type="password" class="form-control form-control-sm" name="password_usuario_perfil" id="password_usuario_perfil" autocomplete="false" aria-autocomplete="none">
                </div>

                <div class="form-group form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Confirmar contraseña</label>
                    <input type="password" class="form-control form-control-sm" name="password_confirm_perfil" id="password_confirm_perfil" autocomplete="off" aria-autocomplete="none" onfocusout="validateUserPassword()">
                    <div class="invalid-feedback" id="password-error-perfil">
                        Las contraseñas no coinciden
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="exampleFormid_tipo_documento">Tipo documento </label>
                    <select name="id_tipo_documento_perfil" id="id_tipo_documento_perfil" class="form-control form-control-sm" required>
                    </select>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Numero documento </label>
                    <input type="number" class="form-control form-control-sm input_decimal only-numbers" name="numero_documento_perfil" id="numero_documento_perfil" required>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Primer nombre</label>
                    <input type="text" class="form-control form-control-sm only-lyrics" name="primer_nombre_perfil" id="primer_nombre_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Segundo nombre</label>
                    <input type="text" class="form-control form-control-sm only-lyrics" name="otros_nombres_perfil" id="otros_nombres_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Primer apellido</label>
                    <input type="text" class="form-control form-control-sm only-lyrics" name="primer_apellido_perfil" id="primer_apellido_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Segundo apellido</label>
                    <input type="text" class="form-control form-control-sm only-lyrics" name="segundo_apellido_perfil" id="segundo_apellido_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Email</label>
                    <input type="email" class="form-control form-control-sm" name="email_perfil" id="email_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                    <label for="example-text-input" class="form-control-label">Telefono</label>
                    <input type="text" class="form-control form-control-sm" name="telefono_1_perfil" id="telefono_1_perfil" >
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <form class="justify-content-center col-12 col-sm-4 col-md-3" id="fondo-imagen-perfil" style="margin-top: 10px;" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <label for="example-text-input" class="form-control-label">Fondo de sistema</label>
                    <div style="height: 80px;">
                        <img id="default_fondo_sistema" onclick="document.getElementById('newFondoSistema').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        <img id="empresa_fondo_sistema" onclick="document.getElementById('newFondoSistema').click();" src="" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        <input type="file" name="newFondoSistema" id="newFondoSistema" onchange="readURLFondoSistemaPerfil(this);" style="display: none" />
                    </div>
                </form>

                <br/>

                <button id="updatePerfil"type="button" class="btn bg-gradient-primary btn-sm">Guardar</button>
                <button id="updatePerfilLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>

            </div>
        </div>

    </div>
</div>