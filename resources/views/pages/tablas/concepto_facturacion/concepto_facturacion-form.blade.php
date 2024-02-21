<div class="modal fade" id="conceptoFacturacionFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textConceptoFacturacionCreate" style="display: none;">Agregar concepto facturación</h5>
                <h5 class="modal-title" id="textConceptoFacturacionUpdate" style="display: none;">Editar concepto facturación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="conceptoFacturacionForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_concepto_facturacion_up" id="id_concepto_facturacion_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_concepto_facturacion" id="nombre_concepto_facturacion" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta ingreso</label>
                            <select name="id_cuenta_ingreso_concepto_facturacion" id="id_cuenta_ingreso_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta cobrar</label>
                            <select name="id_cuenta_cobrar_concepto_facturacion" id="id_cuenta_cobrar_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta intereses</label>
                            <select name="id_cuenta_interes_concepto_facturacion" id="id_cuenta_interes_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>
                        
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta iva</label>
                            <select name="id_cuenta_iva_concepto_facturacion" id="id_cuenta_iva_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Valor</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_concepto_facturacion" id="valor_concepto_facturacion" value="0">
                        </div>

                        <div class="form-check form-switch col-12 col-sm-6 col-md-6" style="margin-left: 12px;">
                            <input class="form-check-input" type="checkbox" name="intereses_concepto_facturacion" id="intereses_concepto_facturacion" style="height: 20px;">
                            <label class="form-check-label" for="intereses_concepto_facturacion">Intereses</label>
                        </div>
                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveConceptoFacturacion"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateConceptoFacturacion"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveConceptoFacturacionLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>