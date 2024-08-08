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
            <div class="col-12 col-md-12 col-sm-12">
                @can('facturacion create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateFacturacion">CARGANDO FACTURACIÓN
                        <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                    </button>
                    <button type="button" class="btn btn-dark btn-sm" id="generateFacturacionLoading" style="display: none;">GENERANDO FACTURACIONES
                        <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="detenerFacturacion" style="display: none;">
                    </button>
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadFacturacion">
                        <i id="reloadFacturacionIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                        <i id="reloadFacturacionIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                    <button type="button" class="btn btn-info btn-sm" id="continuarFacturacion" style="display: none;">
                        CONTINUAR FACTURACION
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="confirmarFacturacion" style="display: none;">
                        Confirmar facturación
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="confirmarFacturacionDisabled" style="display: none;" disabled>
                        Confirmar facturación <i id="textLoadingFacturacionCreate"class="fas fa-spinner fa-spin"></i>
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

                <div id="progress_bar" class="progress-wrapper" style="padding: 10px; margin-top: -5px; display: none;">
                    <div class="progress-info" style="height: 10px;">
                        <div class="progress-percentage" style="text-align: center;">
                            <span id="text_progress_bar" class="text-sm font-weight-bold" style="color: black;"></span>
                        </div>
                    </div>
                    <div class="progress" style="height: 12px; margin-top: 10px;">
                        <div id="width_progress_bar"class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
                    </div>
                </div>

                <table id="tabla_inmuebles_preview" class="table table-bordered display responsive table-facturacion" width="100%">
                    <thead>
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
                            <th>Items</th>
                            <th>Saldo Ant.</th>
                            <th>Esperado</th>
                            <th>Causado</th>
                            <th>Diferencia</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Items</th>
                        </tr>
                    </thead>
                    <!-- <tfoot>
                        <tr>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Concepto</th>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Items</th>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Valor actual</th>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Causado</th>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Nuevo saldo</th>
                            <th style="background-color: #05434e; color: white; font-weight: bold; font-size: 14px; padding: 0.3rem 1.2rem !important;">Items</th>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: black;">ANTICIPOS</td>
                            <td style="text-align: end; color: black;" id="anticipos_items">0</td>
                            <td style="text-align: end; color: black;" id="anticipos_valor">0</td>
                            <td style="text-align: end; color: black;" id="anticipos_causado">0</td>
                            <td style="text-align: end; color: black;" id="anticipos_nuevo_saldo">0</td>
                            <td style="text-align: end; color: black;" id="anticipos_items_nuevo">0</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: black;">SALDO BASE</td>
                            <td style="text-align: end; color: black;" id="base_items">0</td>
                            <td style="text-align: end; color: black;" id="base_valor">0</td>
                            <td style="text-align: end; color: black;" id="base_causado">0</td>
                            <td style="text-align: end; color: black;" id="base_nuevo_saldo">0</td>
                            <td style="text-align: end; color: black;" id="base_items_nuevo">0</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: black;">SALDO ACTUAL</td>
                            <td style="text-align: end; color: black;" id="saldo_items">0</td>
                            <td style="text-align: end; color: black;" id="saldo_valor">0</td>
                            <td style="text-align: end; color: black; font-weight: bold;" id="saldo_causado">0</td>
                            <td style="text-align: end; color: black; font-weight: bold; color: #00de00;" id="saldo_nuevo_saldo">0</td>
                            <td style="text-align: end; color: black; font-weight: bold; color: #00de00;" id="saldo_items_nuevo">0</td>
                        </tr>
                    </tfoot> -->
                </table>

            </div>
        </div>

        <div class="card mb-1" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                <table id="tabla_other_preview" class="table table-bordered display responsive table-facturacion" style="color: black;" width="100%">
                    <thead >
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
                            <th>Items</th>
                            <th>Valor actual</th>
                            <th>Causado</th>
                            <th>Nuevo saldo</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Items</th>
                        </tr>
                    </thead>
                    <body >
                        <tr>
                            <td style="font-weight: 600;">ANTICIPOS</td>
                            <td style="text-align: end;" id="anticipos_items">0</td>
                            <td style="text-align: end;" id="anticipos_valor">0</td>
                            <td style="text-align: end;" id="anticipos_causado">0</td>
                            <td style="text-align: end;" id="anticipos_nuevo_saldo">0</td>
                            <td style="text-align: end;" id="anticipos_items_nuevo">0</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">BASE INTERESES</td>
                            <td style="text-align: end;" id="base_items">0</td>
                            <td style="text-align: end;" id="base_valor">0</td>
                            <td style="text-align: end;" id="base_causado">0</td>
                            <td style="text-align: end;" id="base_nuevo_saldo">0</td>
                            <td style="text-align: end;" id="base_items_nuevo">0</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">SALDO ACTUAL</td>
                            <td style="text-align: end;" id="saldo_items">0</td>
                            <td style="text-align: end;" id="saldo_valor">0</td>
                            <td style="text-align: end; font-weight: bold;" id="saldo_causado">0</td>
                            <td style="text-align: end; font-weight: bold; color: #00b700;" id="saldo_nuevo_saldo">0</td>
                            <td style="text-align: end; font-weight: bold; color: #00b700;" id="saldo_items_nuevo">0</td>
                        </tr>
                    </body>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    var causacion_mensual_rapida = JSON.parse('<?php echo $causacion_mensual_rapida; ?>');
</script>