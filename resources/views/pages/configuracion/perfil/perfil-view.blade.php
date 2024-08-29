<style>
    .avatar-add-imagen {
        background-color: #0023ff;
        cursor: pointer;
    }

    .avatar-add-imagen:hover {
        background-color: #36449f;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body row">
                <div class="col-auto">

                    @if (!Auth::user()->avatar)
                        <div id="default_name_perfil" class="avatar avatar-xl position-relative avatar-add-imagen" onclick="document.getElementById('imagen_perfil').click();" style="">
                            @if (Auth::user()->firstname && Auth::user()->lastname)
                                {{ mb_substr(Auth::user()->firstname, 0, 1) }}{{ mb_substr(Auth::user()->lastname, 0, 1) }}
                            @elseif (Auth::user()->firstname)
                                {{ mb_substr(Auth::user()->firstname, 0, 2) }}
                            @else
                                {{ mb_substr(Auth::user()->username, 0, 1) }}
                            @endif
                        </div>
                        <img id="default_avatar_perfil" onclick="document.getElementById('imagen_perfil').click();" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ Auth::user()->avatar }}" class="img-fluid border border-2 border-white" style="width: 70px; height: auto; cursor: pointer; border-radius: 5%; display: none;">
                    @else
                        <img id="default_avatar_perfil" onclick="document.getElementById('imagen_perfil').click();" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ Auth::user()->avatar }}" class="img-fluid border border-2 border-white" style="width: 70px; height: auto; cursor: pointer; border-radius: 5%;">
                    @endif
                </div>

                <div class="col-auto my-auto">
                    <div class="h-100">
                        <h5 class="mb-1" style="font-size: 18px;">
                            {{ $usuario_nit ? $usuario_nit->nombre_completo : 'SIN NIT REGISTRADO' }}
                        </h5>
                        <p class="mb-0 font-weight-bold text-sm">
                            {{ $nombre_rol }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body row">

                <form class="col-12 col-sm-4 col-md-3" id="perfil-imagen" style="margin-top: 10px;" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div>
                        <input type="file" name="imagen_perfil" id="imagen_perfil" onchange="readURLperfil(this);" style="display: none" />
                    </div>
                </form>

                <h4 style="font-size: 15px; color: #7b7b7b; margin-bottom: 15px;">INFORMACIÓN PERSONAL</h4>

                @if ($usuario_nit)

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

                    <div class="form-group col-12 col-sm-4 col-md-3" style="align-self: center;">
                        <label for="example-text-input" class="form-control-label">Razon social</label>
                        <input type="text" class="form-control form-control-sm" name="razon_social_perfil" id="razon_social_perfil" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>
                @endif


                <form class="justify-content-center col-12 col-sm-4 col-md-3" id="fondo-imagen-perfil" style="margin-top: 10px;" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <label for="example-text-input" class="form-control-label">Fondo de sistema</label>
                    <div style="height: 80px;">
                        <img id="default_fondo_sistema" onclick="document.getElementById('newFondoSistema').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        <img id="empresa_fondo_sistema" onclick="document.getElementById('newFondoSistema').click();" src="" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        <input type="file" name="newFondoSistema" id="newFondoSistema" onchange="readURLFondoSistemaPerfil(this);" style="display: none" />
                    </div>
                </form>

                @if ($usuario_nit)

                    <h4 style="font-size: 15px; color: #7b7b7b; margin-bottom: 15px;">NOTIFICACIONES DE FACTURA</h4>

                    <div class="form-check form-switch col-12 col-sm-6 col-md-6">
                        <input class="form-check-input" type="checkbox" name="enviar_notificaciones_mail" id="enviar_notificaciones_mail" style="height: 20px; margin-left: -30px;">
                        <label class="form-check-label" for="enviar_notificaciones_mail">Notificación mail</label>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-md-6">
                        <input class="form-check-input" type="checkbox" name="enviar_notificaciones_fisica" id="enviar_notificaciones_fisica" style="height: 20px; margin-left: -30px;">
                        <label class="form-check-label" for="enviar_notificaciones_fisica">Notificación fisica</label>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" style="margin-top: 10px;">
                        <label for="example-text-input" class="form-control-label">Email notificación 1</label>
                        <input type="email" class="form-control form-control-sm" name="email_1_perfil" id="email_1_perfil" >
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" style="margin-top: 10px;">
                        <label for="example-text-input" class="form-control-label">Email notificación 2</label>
                        <input type="email" class="form-control form-control-sm" name="email_2_perfil" id="email_2_perfil" >
                    </div>

                @endif

                

                <h4 style="font-size: 15px; color: #7b7b7b; margin-bottom: 15px; margin-top: 10px;">CAMBIAR CONTRASEÑA</h4>

                <form class="justify-content-center row" autocomplete="off">
                    <div class="form-group form-group col-12 col-sm-6 col-md-6" style="align-self: center;">
                        <label for="example-text-input" class="form-control-label">Contraseña</label>
                        <input type="password" class="form-control form-control-sm" name="password_usuario_perfil" id="password_usuario_perfil" autocomplete="off" aria-autocomplete="none">
                    </div>
    
                    <div class="form-group form-group col-12 col-sm-6 col-md-6" style="align-self: center;">
                        <label for="example-text-input" class="form-control-label">Confirmar contraseña</label>
                        <input type="password" class="form-control form-control-sm" name="password_confirm_perfil" id="password_confirm_perfil" autocomplete="off" aria-autocomplete="none" onfocusout="validateUserPassword()">
                        <div class="invalid-feedback" id="password-error-perfil">
                            Las contraseñas no coinciden
                        </div>
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