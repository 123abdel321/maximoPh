<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">

            <div class="col-12 col-md-6 col-sm-6" style="z-index: 9;">
                @can('cuotas_multas create')
                    <button type="button" class="btn btn-primary btn-sm" id="createCuotasMultas" style="margin-bottom: 0.3rem !important;">Agregar cuotas extras & multas</button>
                    <button type="button" class="btn btn-danger btn-sm" id="createCuotasMultas" style="margin-bottom: 0.3rem !important;">Eliminar cuotas extras & multas</button>
                    <button type="button" class="btn btn-sm badge btn-light" id="reloadCuotasMultas" style="vertical-align: middle; height: 30px; margin-bottom: 0.3rem !important;">
                        <i id="reloadCuotasMultasIconLoading" class="fa fa-refresh fa-spin" style="font-size: 17px; color: #2d3257; display: none;"></i>
                        <i id="reloadCuotasMultasIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                @endcan
            </div>

            <div class="col-12 col-md-6 col-sm-6" id="totales_multas_view" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
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

        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
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