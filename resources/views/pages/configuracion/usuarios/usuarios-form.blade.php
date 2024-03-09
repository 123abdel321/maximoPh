<div class="modal fade" id="usuariosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textUsuariosCreate" style="display: none;">Agregar usuario</h5>
                <h5 class="modal-title" id="textUsuariosUpdate" style="display: none;">Editar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="usuariosForm" class="row" autocomplete="off" style="margin-top: 10px;">

                    <input type="text" class="form-control" name="id_usuarios_up" id="id_usuarios_up" style="display: none;">
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Usuario</label>
                        <input type="text" class="form-control form-control-sm" name="usuario" id="usuario" onkeypress="return usuarioNombre(event)" required>
                        <div class="invalid-feedback">
                            El usuario es requerido
                        </div>
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Correo</label>
                        <input type="email" class="form-control form-control-sm" name="email_usuario" id="email_usuario" required>
                        <div class="invalid-feedback">
                            El correo es requerido
                        </div>
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Contraseña</label>
                        <input type="password" class="form-control form-control-sm" name="password_usuario" id="password_usuario" autocomplete="false" aria-autocomplete="none">
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Confirmar contraseña</label>
                        <input type="password" class="form-control form-control-sm" name="password_confirm" id="password_confirm" autocomplete="off" aria-autocomplete="none" onfocusout="validateUserPassword()">
                        <div class="invalid-feedback" id="password-error-username">
                            Las contraseñas no coinciden
                        </div>
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Nombres</label>
                        <input type="text" class="form-control form-control-sm" name="firstname_usuario" id="firstname_usuario">
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Apellidos</label>
                        <input type="text" class="form-control form-control-sm" name="lastname_usuario" id="lastname_usuario">
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Telefono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono_usuario" id="telefono_usuario">
                    </div>
                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Dirección</label>
                        <input type="text" class="form-control form-control-sm" name="address_usuario" id="address_usuario">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label>Rol usuario</label>
                        <select name="rol_usuario" id="rol_usuario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveUsuarios"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateUsuarios"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveUsuariosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>