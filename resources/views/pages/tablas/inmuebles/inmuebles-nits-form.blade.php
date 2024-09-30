<div class="modal fade" id="inmuebleNitFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textInmuebleNitCreate" style="display: none;">Asignar persona - inmueble</h5>
                <h5 class="modal-title" id="textInmuebleNitUpdate" style="display: none;">Editar persona - inmueble</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="inmueblesNitForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_inmueble_nit_up" id="id_inmueble_nit_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="formZonaLabel">Cédula / nit<span style="color: red">*</span></label>
                        <select name="id_nit_inmueble_nit" id="id_nit_inmueble_nit" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" id="tipo_inmueble_nit">
                            <option value="0">PROPIETARIO</option>
                            <option value="1">INQUILINO</option>
                            <option value="2">INMOBILIARIA</option>
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">% Admon</label>
                        <input type="text" class="form-control form-control-sm text-align-right" name="porcentaje_administracion_inmueble_nit" id="porcentaje_administracion_inmueble_nit" data-type="currency" onkeydown="changePorcentajeNit()" onfocus="this.select();">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Valor total</label>
                        <input type="text" class="form-control form-control-sm text-align-right" name="valor_total_inmueble_nit" id="valor_total_inmueble_nit" data-type="currency" disabled>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-md-6" style="margin-left: 12px;">
                        <input class="form-check-input" type="checkbox" name="enviar_notificaciones_mail" id="enviar_notificaciones_mail" style="height: 20px;">
                        <label class="form-check-label" for="enviar_notificaciones_mail">Notificación mail</label>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-md-6" style="margin-left: 12px;">
                        <input class="form-check-input" type="checkbox" name="enviar_notificaciones_fisica" id="enviar_notificaciones_fisica" style="height: 20px;">
                        <label class="form-check-label" for="enviar_notificaciones_fisica">Notificación fisica</label>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveInmuebleNit"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateInmuebleNit"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveInmuebleNitLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>