<div class="modal fade" id="nitFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textNitCreate" style="display: none;">Agregar Cedulas nit</h5>
                <h5 class="modal-title" id="textNitUpdate" style="display: none;">Editar Cedulas nit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="nitsForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_nit_up" id="id_nit_up" style="display: none;">

                    <div class="justify-content-center col-12 col-sm-6 col-md-6">
                        <div style="text-align: -webkit-center; height: 100px;">
                            <img id="default_avatar" onclick="document.getElementById('newAvatar').click();" src="/img/add_profile_img.png" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                            <img id="new_avatar" onclick="document.getElementById('newAvatar').click();" src="" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        </div>
                    </div>

                    <input type="file" name="newAvatar" id="newAvatar" onchange="readURL(this);" style="display: none" />

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormid_tipo_documento">Tipo documento </label>
                        <select name="id_tipo_documento" id="id_tipo_documento" class="form-control form-control-sm" required>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_numero_documento">
                        <label for="example-text-input" class="form-control-label">Numero documento </label>
                        <input type="number" class="form-control form-control-sm input_decimal only-numbers" name="numero_documento" id="numero_documento" required>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_primer_nombre">
                        <label for="example-text-input" class="form-control-label">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm only-lyrics" name="primer_nombre" id="primer_nombre" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_otros_nombres">
                        <label for="example-text-input" class="form-control-label">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm only-lyrics" name="otros_nombres" id="otros_nombres" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_primer_apellido">
                        <label for="example-text-input" class="form-control-label">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm only-lyrics" name="primer_apellido" id="primer_apellido" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_segundo_apellido">
                        <label for="example-text-input" class="form-control-label">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm only-lyrics" name="segundo_apellido" id="segundo_apellido" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_razon_social">
                        <label for="example-text-input" class="form-control-label">Razon social</label>
                        <input type="text" class="form-control form-control-sm only-lyrics" name="razon_social" id="razon_social" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_direccion">
                        <label for="example-text-input" class="form-control-label">Dirección </label>
                        <input type="text" class="form-control form-control-sm" name="direccion" id="direccion" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_email">
                        <label for="example-text-input" class="form-control-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" id="email" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_telefono_1">
                        <label for="example-text-input" class="form-control-label">Telefono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono_1" id="telefono_1" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_id_ciudad">
                        <label for="exampleFormControlSelect1" style=" width: 100%;">Ciudad</label>
                        <select class="form-control form-control-sm" name="id_ciudad" id="id_ciudad">
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_id_vendedor_nit">
                        <label for="exampleFormControlSelect1" style=" width: 100%;">Vendedor</label>
                        <select class="form-control form-control-sm" name="id_vendedor_nit" id="id_vendedor_nit">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" id="div_observaciones">
                        <label for="example-text-input" class="form-control-label">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" name="observaciones" id="observaciones">
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-12 col-sm-6 col-md-6" id="div_declarante">
                        <input class="form-check-input" type="checkbox" name="declarante_nit" id="declarante_nit" style="height: 20px;" checked>
                        <label class="form-check-label" for="declarante_nit">Declara Renta</label>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNit"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateNit"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveNitLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>