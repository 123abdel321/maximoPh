<div class="modal fade" id="estadoCuentaPagoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textEstadoCuentaPagoCreate" style="display: block;">Agregar pago total: </h5>
                <h5 class="modal-title" id="textEstadoCuentaPagoUpdate" style="display: none;">Editar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <img src="img/formas_pago.png" style="width: 100%;"/>

                <form id="estadoCuentaPagoForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_recibo_estado_cuenta_up" id="id_recibo_estado_cuenta_up" style="display: none;">

                    <div id="input_fecha_pago_estado_cuenta" style="display: none;" class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Fecha pago<span style="color: red">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="fecha_pago_estado_cuenta" id="fecha_pago_estado_cuenta">
                    </div>

                    <div id="input_valor_comprobante_estado_cuenta" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Valor comprobante<span style="color: red">*</span></label>
                        <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_comprobante_estado_cuenta" id="valor_comprobante_estado_cuenta" value="0" onfocus="this.select();">
                    </div>

                    <div id="input_valor_pago_estado_cuenta" style="display: none;" class="form-group col-12 col-sm-12 col-md-12">
                        <label for="example-text-input" class="form-control-label">Valor pago<span style="color: red">*</span></label>
                        <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_pago_estado_cuenta" id="valor_pago_estado_cuenta" value="0" onfocus="this.select();">
                    </div>

                    <div id="input_imagen_comprobante_estado_cuenta" style="display: none;" class="form-group col-12 col-sm-12 col-md-12">
                        <label for="example-text-input" class="form-control-label">Adjuntar probante<span style="color: red">*</span></label><br/>
                        <input type="file" name="imagen_comprobante_estado_cuenta" id="imagen_comprobante_estado_cuenta" onchange="readFileEstadoCuenta(this);" />
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveEstadoCuentaPago"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateEstadoCuentaPago"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveEstadoCuentaPagoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>