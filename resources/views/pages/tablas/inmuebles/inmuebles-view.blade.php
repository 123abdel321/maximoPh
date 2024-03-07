<style>
    .error {
        color: red;
    }
    .edit-inmueble {
        width: 10px;
    }
    .drop-inmueble {
        width: 10px;
    }
    .fa-inmueble {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-6 col-sm-6">
                @can('inmueble create')
                    <button type="button" class="btn btn-primary btn-sm" id="createInmuebles">Agregar inmueble</button>
                    <button type="button" class="btn btn-dark btn-sm" id="volverInmuebles" style="display: none;">Volver</button>
                    <button type="button" class="btn btn-primary btn-sm" id="createInmueblesNit" style="display: none; margin-left: 5px;">Agregar cédula / nit</button>
                @endcan
            </div>
            <div class="col-12 col-md-6 col-sm-6" >
                <input type="text" id="searchInputInmuebles" class="form-control form-control-sm search-table" placeholder="Buscar">
                <h4 id="nombre_inmueble_nit" style="display: none; float: inline-end; color: white;"></h4>
            </div>
        </div>

        <div id="totales_inmuebles_view" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
            <div class="row ">
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Inmuebles Registrados</p>
                            <div style="display: flex;">
                                <h5 id="inmuebles_registrados_inmueble" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 class="font-weight-bolder">
                                    &nbsp;de {{ number_format($numero_total_unidades) }}
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-building text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Área M2 Registrada</p>
                            <div style="display: flex;">
                                <h5 id="area2_registrados_inmueble" class="font-weight-bolder">
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

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Coeficiente %</p>
                            <div style="display: flex;">
                                <h5 id="coeficiente_registrados_inmueble" class="font-weight-bolder">
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

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Presupuesto Asignado Mensual</p>
                            <div style="display: flex;">
                                <h5 id="presupuesto_registrados_inmueble" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 class="font-weight-bolder">
                                    &nbsp;de {{ number_format($valor_total_presupuesto_year_actual / 12) }}
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

        <div id="tablas_inmuebles" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                
                @include('pages.tablas.inmuebles.inmuebles-table')

            </div>
        </div>

        <div id="tablas_inmuebles_nits" class="card mb-4" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="card-body">

                <div class="row">
                    <div class="col-12 col-md-5 col-sm-5" >
                        <h4 id="total_porcentaje_inmueble_nit" style="text-align: -webkit-center;"></h4>
                    </div>
                    <div class="col-12 col-md-2 col-sm-2" style="align-self: center; text-align: center;">
                        <i id="status_inmueble_nit_false" class="fas fa-times-circle" style="font-size: 20px; color: #d00404; display: none;"></i>
                        <i id="status_inmueble_nit_true" class="fas fa-check-circle" style="font-size: 20px; color: #09d004; display: none;"></i>
                    </div>
                    <div class="col-12 col-md-5 col-sm-5" >
                        <h4 id="total_valor_inmueble_nit" style="text-align: -webkit-center;"></h4>
                    </div>
                </div>
                        
                @include('pages.tablas.inmuebles.inmuebles-nits-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.inmuebles.inmuebles-form', [
        'valor_total_presupuesto_year_actual' => $valor_total_presupuesto_year_actual,
        'area_total_m2' => $area_total_m2
    ])

    @include('pages.tablas.inmuebles.inmuebles-nits-form')
    
</div>

<script>
    var editarInmueble = '<?php echo auth()->user()->can('inmueble update'); ?>';
    var eliminarInmueble = '<?php echo auth()->user()->can('inmueble delete'); ?>';
    var area_total_m2 = JSON.parse('<?php echo $area_total_m2; ?>');
    var editar_valor_admon_inmueble = JSON.parse('<?php echo $editar_valor_admon_inmueble; ?>');
    var valor_total_presupuesto_year_actual = JSON.parse('<?php echo $valor_total_presupuesto_year_actual; ?>');
    var numero_total_unidades = JSON.parse('<?php echo $numero_total_unidades; ?>');
</script>