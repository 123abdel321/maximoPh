<style>
    .table-facturacion thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-facturacion > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div id="header_facturacion_view" class="row" style="z-index: 9;">
            <div class="col-12 col-md-8 col-sm-8">
                @can('facturacion create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateFacturacion">CARGANDO FACTURACIÓN
                        <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="detenerFacturacion" style="display: none;">
                    </button>
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadFacturacion">
                        <i id="reloadFacturacionIconLoading" class="fa fa-refresh fa-spin" style="font-size: 17px; color: #2d3257; display: none;"></i>
                        <i id="reloadFacturacionIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                @endcan
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

        <div class="card mb-1" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">

                <table id="tabla_inmuebles_preview" class="table table-bordered display responsive table-facturacion" width="100%">
                    <thead>
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
                            <th>Esperado</th>
                            <th>Causado</th>
                            <th>Items</th>
                            <th>Diferencia</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Items</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

        <div class="card mb-1" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">

                <table id="tabla_extras_preview" class="table table-bordered display responsive table-facturacion" width="100%">
                    <thead>
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
                            <th>Esperado</th>
                            <th>Causado</th>
                            <th>Items</th>
                            <th>Diferencia</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Items</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

        <!-- <div class="card mb-1" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                <table id="tabla_other_preview" class="table table-bordered display responsive table-facturacion" style="color: black;" width="100%">
                    <thead >
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
                            <th>Items</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SALDO ANTICIPOS</td>
                            <td style="text-align: end;" id="count_anticipos">0</td>
                            <td style="text-align: end;" id="total_anticipos">0</td>
                        </tr>
                        <tr>
                            <td>DEUDA ACTUAL</td>
                            <td style="text-align: end;" id="count_saldo_anterior">0</td>
                            <td style="text-align: end;" id="total_saldo_anterior">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> -->

        <div class="col-12 col-sm-6 col-md-6" style="margin-top: 5px; padding-bottom: 5px;">
            <div class="card" style="height: 100%;">
                <div class="card-body p-2">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">SALDO ANTICIPOS</p>
                    <div style="display: flex;">
                        <h5 class="font-weight-bolder">
                            Items: 
                        </h5>&nbsp;
                        <h5 id="count_anticipos" class="font-weight-bolder">
                            0
                        </h5>&nbsp;
                        <h5 class="font-weight-bolder">
                            - Total: 
                        </h5>&nbsp;
                        <h5 id="total_anticipos" class="font-weight-bolder">
                            0
                        </h5>
                    </div>
                    <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                        <i class="far fa-credit-card text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-6" style="margin-top: 5px; padding-bottom: 5px;">
            <div class="card" style="height: 100%;">
                <div class="card-body p-2">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">DEUDA ACTUAL</p>
                    <div style="display: flex;">
                        <h5 class="font-weight-bolder">
                            Items: 
                        </h5>&nbsp;
                        <h5 id="count_saldo_anterior" class="font-weight-bolder">
                            0
                        </h5>&nbsp;
                        <h5 class="font-weight-bolder">
                            - Total: 
                        </h5>&nbsp;
                        <h5 id="total_saldo_anterior" class="font-weight-bolder">
                            0
                        </h5>
                    </div>
                    <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                        <i class="fas fa-wallet text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>