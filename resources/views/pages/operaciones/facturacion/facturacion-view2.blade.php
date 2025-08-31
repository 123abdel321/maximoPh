<div class="container-fluid py-2">
    <div class="row">

        <div id="header_facturacion_view" class="row" style="z-index: 9;">
            <div class="col-12 col-md-8 col-sm-8">
                @can('facturacion create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateFacturacion">CARGANDO FACTURACIÓN
                        <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                    </button>
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadFacturacion">
                        <i id="reloadFacturacionIconLoading" class="fa fa-refresh fa-spin" style="font-size: 17px; color: #2d3257; display: none;"></i>
                        <i id="reloadFacturacionIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                @endcan
            </div>
        </div>

        <div id="header_procesando_view" class="row" style="z-index: 9; display: none;">
            <div class="col-12 col-md-12 col-sm-12">
                <button type="button" class="btn btn-dark btn-sm" id="volverFacturacion" style="display: none;">
                    <i class="fas fa-step-backward back-icon-button"></i>&nbsp;
                    Volver
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="detenerFacturacion">
                    Detener facturación
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="continuarFacturacion" style="display: none;">
                    Continuar facturación
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="reprocesarFacturacion" style="display: none;">
                    Volver a facturar
                </button>
                <button type="button" class="btn btn-success btn-sm" id="confirmarFacturacion" style="display: none;">
                    Confirmar facturación
                </button>
                <button type="button" class="btn btn-success btn-sm" id="confirmarFacturacionDisabled" style="display: none;" disabled>
                    Confirmar facturación <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>

        <div id="totales_facturacion_view" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
            <div class="row ">

                @include('pages.operaciones.facturacion.facturacion-totales', [
                    'area_total_m2' => $area_total_m2,
                    'valor_total_presupuesto' => $valor_total_presupuesto,
                    'numero_total_unidades' => $numero_total_unidades
                ])

            </div>
        </div>

        <div id="tablas_facturacion_view" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                
                @include('pages.operaciones.facturacion.facturacion-table')

            </div>
        </div>

        <div id="totales_procesando_facturacion_view" style="content-visibility: auto; overflow: auto; display: none; margin-top: -5px;">
            <div class="row ">

                @include('pages.operaciones.facturacion.facturacion-proceso-totales')

            </div>
        </div>

        <div id="tablas_procesando_view" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px; display: none;">
            <div class="card-body">
                
                @include('pages.operaciones.facturacion.facturacion-proceso-table')

            </div>
        </div>

        @include('pages.operaciones.facturacion.facturacion-form')

    </div>
</div>