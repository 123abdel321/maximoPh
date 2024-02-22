<div class="modal fade" id="zonaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textZonaCreate" style="display: none;">Agregar zona</h5>
                <h5 class="modal-title" id="textZonaUpdate" style="display: none;">Editar zona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="zonaForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_zona_up" id="id_zona_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_zona" id="nombre_zona" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Centro costo<span style="color: red">*</span></label>
                            <select name="id_centro_costos_zona" id="id_centro_costos_zona" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Tipo<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="tipo_zona">
                                <option value="0">Uso común</option>
                                <option value="1">Inmueble</option>
                                <option value="2">Porteria</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveZona"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateZona"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveZonaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>