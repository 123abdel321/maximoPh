<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('cuotas_multas create')
                        <button type="button" class="btn btn-primary btn-sm" id="createCuotasMultas">Agregar cuotas / multas</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputCuotasMultas" class="form-control form-control-sm search-table" onkeydown="searchCuotaMulta(event)" placeholder="Buscar">
                </div>
            </div>
        </div>

        <div id="totales_multas_view" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
            <div class="row ">
            
            
                <div class="col-12 col-sm-3 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total cuotas/multas</p>
                            <div style="display: flex;">
                                <h5 id="total_valor_cuotasmultas" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-building text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-9 col-md-9" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="row" style="padding: 4px;">
                            <div class="form-group col-4 col-sm-4 col-md-4" >
                                <label for="example-text-input" class="form-control-label">Fecha desde</label>
                                <input type="month" class="form-control form-control-sm" name="fecha_desde_cuotas_multas" id="fecha_desde_cuotas_multas">
                            </div>

                            <div class="form-group col-4 col-sm-4 col-md-4" >
                                <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                                <input type="month" class="form-control form-control-sm" name="fecha_hasta_cuotas_multas" id="fecha_hasta_cuotas_multas">
                            </div>

                            <div class="form-group col-4 col-sm-4 col-md-4" >
                                <label for="id_concepto_filter_cuotas_multas">Concepto</label>
                                <select name="id_concepto_filter_cuotas_multas" id="id_concepto_filter_cuotas_multas" class="form-control form-control-sm">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.operaciones.cuotas_multas.cuotas_multas-table')

            </div>
        </div>
    </div>

    @include('pages.operaciones.cuotas_multas.cuotas_multas-form')
    
</div>

<script>
    var editarCuotaMulta = '<?php echo auth()->user()->can('cuotas_multas update'); ?>';
    var eliminarCuotaMulta = '<?php echo auth()->user()->can('cuotas_multas delete'); ?>';
</script>