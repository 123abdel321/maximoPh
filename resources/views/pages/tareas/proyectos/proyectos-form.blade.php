<div class="modal fade" id="proyectoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textProyectoCreate" style="display: none;">Agregar proyectos</h5>
                <h5 class="modal-title" id="textProyectoUpdate" style="display: none;">Editar proyectos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="proyectoForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_proyecto_up" id="id_proyecto_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_proyecto" id="nombre_proyecto" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" >
                            <label for="example-text-input" class="form-control-label">Fecha inicio <span style="color: red">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio_proyecto" id="fecha_inicio_proyecto">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" >
                            <label for="example-text-input" class="form-control-label">Fecha fin <span style="color: red">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_fin_proyecto" id="fecha_fin_proyecto">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Valor total<span style="color: red">*</span></label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_proyecto" id="valor_proyecto" onfocus="this.select();" value="0">
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveProyecto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateProyecto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveProyectoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>