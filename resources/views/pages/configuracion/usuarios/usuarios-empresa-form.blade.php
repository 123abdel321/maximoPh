<div class="modal fade" id="usuariosEmpresaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asociar empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="usuariosEmpresaForm" autocomplete="off" style="margin-top: 10px;">

                    <div class="row">

                        <div class="form-group col-12">
                            <label for="id_empresa_usuario_create">Empresa<span style="color: red">*</span></label>
                            <select name="id_empresa_usuario_create" id="id_empresa_usuario_create" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                La empresa es obligatoria
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="id_rol_usuario_create">Rol<span style="color: red">*</span></label>
                            <select name="id_rol_usuario_create" id="id_rol_usuario_create" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                La empresa es obligatoria
                            </div>
                        </div>

                    </div> 

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="usuariosEmpresaCreate"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="usuariosEmpresaCreateLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>