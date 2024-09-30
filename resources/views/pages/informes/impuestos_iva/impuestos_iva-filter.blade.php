<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtroImpuestoIva">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImpuestoIva" aria-expanded="false" aria-controls="collapseImpuestoIva">
                Filtros de impuesto_iva
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseImpuestoIva" class="accordion-collapse collapse show" aria-labelledby="filtroImpuestoIva" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="impuestoIvaInformeForm" style="margin-top: 10px;">

                    <div class="row">
                    
                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde_impuesto_iva" id="fecha_desde_impuesto_iva" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta_impuesto_iva" id="fecha_hasta_impuesto_iva" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Centro costo</label>
                            <select name="id_cecos_impuesto_iva" id="id_cecos_impuesto_iva" class="form-control form-control-sm">
                            </select>
                        </div>
                        
                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Cuenta</label>
                            <select name="id_cuenta_impuesto_iva" id="id_cuenta_impuesto_iva" class="form-control form-control-sm">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_impuesto_iva" id="id_nit_impuesto_iva">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="form-group form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Agrupar por:</label>
                            <select class="form-control form-control-sm agrupar_impuesto_iva" id="agrupar_impuesto_iva" name="agrupar_impuesto_iva">
                                <option value="id_nit">Cedula/Nits</option>
                                <option value="id_cuenta">Cuenta</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Detallar</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detallar_impuesto_iva" id="detallar_impuesto_iva1" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="detallar_impuesto_iva1" style="font-size: 11px;">
                                    No
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detallar_impuesto_iva" id="detallar_impuesto_iva2" style="font-size: 11px;">
                                <label class="form-check-label" for="detallar_impuesto_iva2" style="font-size: 11px;">
                                    Si
                                </label>
                            </div>
                        </div>

                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarImpuestoIva" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 5px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarImpuestoIvaLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-right: 5px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelImpuestoIva" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelImpuestoIvaDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarImpuestoIvaUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarImpuestoIvaUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>