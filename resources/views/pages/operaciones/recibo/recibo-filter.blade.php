<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReciboGeneral" aria-expanded="false" aria-controls="collapseReciboGeneral">
                Datos recibos de caja
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseReciboGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="reciboFilterForm" class="needs-validation row" style="margin-top: 10px;" novalidate>

                    <input type="text" class="form-control" name="id_nit_recibo_hide" id="id_nit_recibo_hide" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-md-3">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_recibo" id="id_nit_recibo" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-3">
                        <label>Comprobante <span style="color: red">*</span></label>
                        <select name="id_comprobante_recibo" id="id_comprobante_recibo" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El comprobante es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2" >
                        <label for="example-text-input" class="form-control-label">Total pago</label>
                        <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_abono_recibo" id="total_abono_recibo" onfocus="this.select();" onkeypress="changeTotalAbonoRecibo(event)"  value="0">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_recibo" id="fecha_manual_recibo" class="form-control form-control-sm" type="date" required>
                        <div class="invalid-feedback">
                            La fecha es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2">
                        <label for="example-text-input" class="form-control-label">Consecutivo</label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_recibo" id="documento_referencia_recibo" disabled required>
                    </div>

                    <div id="input_anticipos_recibo" class="form-group col-6 col-sm-4 col-md-2" style="display: none;">
                        <label for="example-text-input" class="form-control-label">Anticipos <span style="color: red">*</span></label>
                        <input name="saldo_anticipo_recibo" id="saldo_anticipo_recibo" class="form-control form-control-sm" type="text" disabled style="text-align: right;">
                        <div class="invalid-feedback" id="error-anticipo-cliente-venta">
                            Valor superado
                        </div>
                    </div>

                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaRecibo" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR RECIBO</b>
                    </span>
                    <span id="iniciarCapturaReciboLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaRecibo" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR RECIBO</b>
                    </span>
                    <span id="crearCapturaReciboDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR RECIBO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaRecibo" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR RECIBO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>