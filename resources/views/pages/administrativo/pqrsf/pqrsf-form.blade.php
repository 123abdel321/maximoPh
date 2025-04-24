<div class="modal fade" id="pqrsfFormModal" tabindex="-1" role="dialog" aria-labelledby="pqrsfFormModal" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textPqrsfCreate" style="display: block;">Agregar Pqrsf</h5>
                <h5 class="modal-title" id="textPqrsfUpdate" style="display: none;">Editar Pqrsf</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="modal-body">
                <form id="form-pqrsf" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_pqrs_up" id="id_pqrs_up" style="display: none;">

                        <div class="form-group col-6 col-sm-6 col-md-6">
                            <label for="tipo_pqrsf">Tipo solicitud<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="tipo_pqrsf" name="tipo_pqrsf">
                                    <option value="0">PETICIONES</option>
                                    <option value="1">QUEJAS</option>
                                    <option value="2">RECLAMOS</option>
                                    <option value="3">SOLICITUDES</option>
                                    <option value="4">FELICITACIONES</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-6 col-md-6">
                            <label for="area_pqrsf">Área</label>
                            <select class="form-control form-control-sm" id="area_pqrsf" name="area_pqrsf">
                                <option value="1">ADMINISTRACIÓN</option>
                                <option value="2">SEGURIDAD</option>
                                <option value="3">ASEO</option>
                                <option value="4">MANTENIMIENTO</option>
                                <option value="5">ZONAS COMUNES</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="asunto_pqrsf" class="form-control-label">Asunto</label>
                            <input type="text" class="form-control form-control-sm" name="asunto_pqrsf" id="asunto_pqrsf">
                        </div>

                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="mensaje_pqrsf" class="form-control-label">Mensaje<span style="color: red">*</span></label>
                            <textarea class="form-control form-control-sm" id="mensaje_pqrsf" name="mensaje_pqrsf" rows="2" required></textarea>
                        </div>

                        <div class="container">
                            <input type="file" class="filepond" id="pqrsf-files" name="images[]" multiple>
                        </div>

                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="savePqrsf" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePqrsfLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>