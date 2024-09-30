<div class="modal fade" id="pagoTranferenciaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textZonaCreate">Comprobante de pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <input type="text" class="form-control" name="id_pago_tranferencia" id="id_pago_tranferencia" style="display: none;">

                    <img id="imagen_pago_transferencia" src="" class="img-fluid rounded mx-auto d-bloc" style="width: auto; height: 100%; cursor: pointer; border-radius: 10%;">

                    <div id="input_observacion_pago_transferencia" class="form-group col-12 col-sm-12 col-md-12" >
                        <label for="example-text-input" class="form-control-label">Observación</label>
                        <input name="observacion_pago_transferencia" id="observacion_pago_transferencia" class="form-control form-control-sm" type="text">
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelarPagoTransferencia" type="button" class="btn bg-gradient-primary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button id="rechazarPagoTransferencia" type="button" class="btn bg-gradient-danger btn-sm">Rechazar</button>
                <button id="aprobarPagoTransferencia" type="button" class="btn bg-gradient-success btn-sm">Aprobar</button>
                <button id="PagoTransferenciaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>