<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtroCartera">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCartera" aria-expanded="false" aria-controls="collapseCartera">
                Filtros de cartera
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseCartera" class="accordion-collapse collapse show" aria-labelledby="filtroCartera" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="carteraInformeForm" style="margin-top: 10px;">
                    <input name="id_cartera_cargado" id="id_cartera_cargado" class="form-control form-control-sm" type="text" style="display: none;">
                    <div class="row">
                        <div class="form-group form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Tipo informe</label>
                            <select class="form-control form-control-sm tipo_cartera" id="tipo_informe_cartera" name="tipo_informe_cartera">
                                <option value="">Ambos</option>
                                <option value="por_cobrar">Cuentas por cobrar</option>
                                <option value="por_pagar">Cuentas por pagar</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Cuenta</label>
                            <select name="id_cuenta_cartera" id="id_cuenta_cartera" class="form-control form-control-sm">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_cartera" id="id_nit_cartera">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="form-group form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1">Agrupar por:</label>
                            <select class="form-control form-control-sm agrupar_cartera" id="agrupar_cartera" name="agrupar_cartera">
                                <option value="id_cuenta">Cuenta</option>
                                <option value="id_nit">Cedula/Nits</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde_cartera" id="fecha_desde_cartera" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta_cartera" id="fecha_hasta_cartera" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Niveles</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_cartera" id="nivel_cartera1" style="font-size: 11px;">
                                <label class="form-check-label" for="nivel_cartera1" style="font-size: 11px;">
                                    Grupos
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_cartera" id="nivel_cartera2" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="nivel_cartera2" style="font-size: 11px;">
                                    Sub-grupos
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_cartera" id="nivel_cartera3" style="font-size: 11px;">
                                <label class="form-check-label" for="nivel_cartera3" style="font-size: 11px;">
                                    Detalle
                                </label>
                            </div>
                        </div>
                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarCartera" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 5px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarCarteraLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-right: 5px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelCartera" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelCarteraDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarCarteraUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarCarteraUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>