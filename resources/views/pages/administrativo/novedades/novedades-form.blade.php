<div class="modal fade" id="novedadesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textNovedadesCreate" style="display: none;">Agregar novedades</h5>
                <h5 class="modal-title" id="textNovedadesUpdate" style="display: none;">Editar novedades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="novedadesForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_novedades_up" id="id_novedades_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_porteria_novedad">Responsable <span style="color: red">*</span></label>
                            <select name="id_porteria_novedad" id="id_porteria_novedad" class="form-control form-control-sm" required>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-6 col-md-6">
                            <label for="area_novedades">Tipo</label>
                            <select class="form-control form-control-sm" id="tipo_novedades" name="area_pqrsf">
                                <option value="1">MULTA</option>
                                <option value="2">NOVEDAD</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-6 col-md-6">
                            <label for="area_novedades">Área</label>
                            <select class="form-control form-control-sm" id="area_novedades" name="area_pqrsf">
                                <option value="1">ADMINISTRACIÓN</option>
                                <option value="2">SEGURIDAD</option>
                                <option value="3">ASEO</option>
                                <option value="4">MANTENIMIENTO</option>
                                <option value="5">ZONAS COMUNES</option>
                            </select>
                        </div>

                        <div id="input_hora_inicio_pqrsf" class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_novedades" class="form-control-label">Fecha<span style="color: red">*</span></label>
                            <input type="datetime-local" class="form-control form-control-sm" name="fecha_novedades" id="fecha_novedades" required>
                        </div>

                        <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                            <label for="asunto_novedades" class="form-control-label">Asunto<span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="asunto_novedades" id="asunto_novedades" onfocus="this.select();" required>
                        </div>

                        <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                            <label for="mensaje_novedades" class="form-control-label">Mensaje<span style="color: red">*</span></label>
                            <textarea class="form-control form-control-sm" id="mensaje_novedades" name="mensaje_novedades" rows="2" onfocus="this.select();" required></textarea>
                        </div>

                        <div class="container">
                            <input type="file" class="filepond" id="novedades-files" name="images[]" multiple>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNovedades"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateNovedades"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveNovedadesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>