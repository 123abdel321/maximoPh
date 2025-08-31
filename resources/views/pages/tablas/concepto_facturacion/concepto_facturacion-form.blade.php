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
                            <label for="codigo_concepto_facturacion" class="form-control-label">Codigo <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="codigo_concepto_facturacion" id="codigo_concepto_facturacion" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_concepto_facturacion" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_concepto_facturacion" id="nombre_concepto_facturacion" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_nit_ingreso_concepto_facturacion">Nit ingreso</label>
                            <select name="id_nit_ingreso_concepto_facturacion" id="id_nit_ingreso_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div> 

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_ingreso_concepto_facturacion">Cuenta ingreso</label>
                            <select name="id_cuenta_ingreso_concepto_facturacion" id="id_cuenta_ingreso_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_cobrar_concepto_facturacion">Cuenta cobrar</label>
                            <select name="id_cuenta_cobrar_concepto_facturacion" id="id_cuenta_cobrar_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_interes_concepto_facturacion">Cuenta intereses</label>
                            <select name="id_cuenta_interes_concepto_facturacion" id="id_cuenta_interes_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>
                        
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_iva_concepto_facturacion">Cuenta iva</label>
                            <select name="id_cuenta_iva_concepto_facturacion" id="id_cuenta_iva_concepto_facturacion" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="valor_concepto_facturacion" class="form-control-label">Valor</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_concepto_facturacion" id="valor_concepto_facturacion" value="0">
                        </div>

                        <div id="input_tipo_concepto_facturacion" class="form-group form-group col-6 col-sm-6 col-md-6">
                            <label for="tipo_concepto_facturacion">Tipo concepto</label>
                            <select class="form-control form-control-sm" id="tipo_concepto_facturacion">
                                <option value="0">Facturación inmuebles</option>
                                <option value="1">Cuotas extras & multas</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="orden_concepto_facturacion" class="form-control-label">Orden</label>
                            <input type="input" class="form-control form-control-sm text-align-right" name="orden_concepto_facturacion" id="orden_concepto_facturacion" value="0">
                        </div>

                        <div class="form-check form-switch col-6 col-sm-6 col-md-6" style="">
                            <input class="form-check-input" type="checkbox" name="intereses_concepto_facturacion" id="intereses_concepto_facturacion" style="height: 20px;">
                            <label class="form-check-label" for="intereses_concepto_facturacion">Intereses</label>
                        </div>

                        <div class="form-check form-switch col-6 col-sm-6 col-md-6" style="">
                            <input class="form-check-input" type="checkbox" name="pronto_pago_concepto_facturacion" id="pronto_pago_concepto_facturacion" style="height: 20px;">
                            <label class="form-check-label" for="pronto_pago_concepto_facturacion">Pronto pago</label>
                        </div>

                        <div class="form-group col-6 col-sm-6 col-md-6" id="input-id_cuenta_pronto_pago_gasto" style="display: none;">
                            <label for="id_cuenta_pronto_pago_gasto">Cuenta pronto pago gasto</label>
                            <select name="id_cuenta_pronto_pago_gasto" id="id_cuenta_pronto_pago_gasto" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-6 col-md-6" id="input-id_cuenta_pronto_pago_anticipo" style="display: none;">
                            <label for="id_cuenta_pronto_pago_anticipo">Cuenta pronto pago anticipo</label>
                            <select name="id_cuenta_pronto_pago_anticipo" id="id_cuenta_pronto_pago_anticipo" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-3 col-sm-6 col-md-6" id="input-dias_concepto_facturacion" style="display: none;">
                            <label for="dias_concepto_facturacion" class="form-control-label">Días pronto pago</label>
                            <input type="number" class="form-control form-control-sm" name="dias_concepto_facturacion" id="dias_concepto_facturacion">
                        </div>
                        
                        <div class="form-group col-6 col-sm-6 col-md-6" id="input-porcentaje_descuento_concepto_facturacion" style="display: none;">
                            <label for="porcentaje_descuento_concepto_facturacion" class="form-control-label">Porcentaje descuento</label>
                            <input type="number" class="form-control form-control-sm" name="porcentaje_descuento_concepto_facturacion" id="porcentaje_descuento_concepto_facturacion">
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