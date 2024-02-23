<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-8 col-sm-8">
                @can('facturacion create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateFacturacion">Generar facturación
                        <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                    </button>
                @endcan
            </div>
            <div class="col-12 col-md-4 col-sm-4" >
                <input type="text" id="searchInputFacturacion" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div id="totales_facturacion_view" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
            <div class="row ">
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Inmuebles Registrados</p>
                            <div style="display: flex;">
                                <h5 id="inmuebles_registrados_facturacion" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 id="inmuebles_registrados_facturacion_text" class="font-weight-bolder">
                                    &nbsp;de {{ number_format($numero_total_unidades) }}
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-building text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Área M2 Registrada</p>
                            <div style="display: flex;">
                                <h5 id="aream2_registrados_facturacion" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 class="font-weight-bolder">
                                    &nbsp;de {{ number_format($area_total_m2) }}
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-success shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-text-height text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Coeficiente %</p>
                            <div style="display: flex;">
                                <h5 id="coeficiente_registrados_facturacion" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 class="font-weight-bolder">
                                    %&nbsp;de 100%
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-warning shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-calculator text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Presupuesto Asignado Mensual</p>
                            <div style="display: flex;">
                                <h5 id="presupuesto_registrados_facturacion" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 class="font-weight-bolder">
                                    &nbsp;de {{ number_format($valor_total_presupuesto / 12) }}
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-danger shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-money-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tablas_facturacion" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                
                @include('pages.operaciones.facturacion.facturacion-table')

            </div>
        </div>

        @include('pages.operaciones.facturacion.facturacion-form')

    </div>
</div>