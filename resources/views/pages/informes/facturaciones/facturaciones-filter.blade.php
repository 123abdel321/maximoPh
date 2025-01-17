<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtroFacturaciones">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFacturaciones" aria-expanded="false" aria-controls="collapseFacturaciones">
                Filtros de facturaciones
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseFacturaciones" class="accordion-collapse collapse show" aria-labelledby="filtroFacturaciones" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="facturacionesInformeForm" style="margin-top: 10px;">

                    <div class="row">
                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Periodo</label>
                            <select name="periodo_facturaciones" id="periodo_facturaciones" class="form-control form-control-sm">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_facturaciones" id="id_nit_facturaciones">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Zona</label>
                            <select class="form-control form-control-sm" name="id_zona_facturaciones" id="id_zona_facturaciones">
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="form-check form-switch col-6 col-sm-4 col-md-4" style="place-content: center;">
                            <input class="form-check-input" type="checkbox" name="nit_fisica_facturaciones" id="nit_fisica_facturaciones" style="height: 20px;">
                            <label class="form-check-label" for="nit_fisica_facturaciones">Facturacion fisica</label>
                        </div>

                        <div class="form-check form-switch col-6 col-sm-4 col-md-4" style="">
                            <span id="imprimirMultipleFacturacion" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; margin-right: 3px; float: right; margin-bottom: 0rem !important;">
                                <i class="fas fa-print" style="font-size: 17px;"></i>&nbsp;
                                <b style="vertical-align: text-top;">IMPRIMIR FACTURAS</b>
                            </span>
                        </div>

                        @can('facturaciones email')
                            <div class="form-check form-switch col-6 col-sm-4 col-md-4" style="">
                                <span id="enviarEmailFacturas" href="javascript:void(0)" class="btn badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; float: right; margin-bottom: 0rem !important;">
                                    <i class="fas fa-envelope" style="font-size: 17px;"></i>&nbsp;
                                    <b style="vertical-align: text-top;">ENVIAR FACTURAS</b>
                                </span>

                                <span id="enviarEmailFacturasLoading" class="btn disabled badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; float: right; margin-bottom: 0rem !important; display: none;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>&nbsp;
                                    <b style="vertical-align: text-top;">CARGANDO</b>
                                </span>
                            </div>
                        @endcan
                    </div>  
                </form>
                <div style="height: 10px;"></div>  
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <!-- <span id="generarFacturaciones" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 5px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarFacturacionesLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-right: 5px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span> -->
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelFacturaciones" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelFacturacionesDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                    <!-- BOTON ULTIMO INFORME -->
                    <!-- <span id="generarFacturacionesUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarFacturacionesUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>