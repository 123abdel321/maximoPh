<div class="container-fluid py-2">
    <div class="row">

        <div id="totales_estado_cuenta" style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">

            @include('pages.administrativo.estado_cuenta.estado_cuenta-total')

        </div>

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-12 col-sm-12">
                <button type="button" class="btn btn-primary btn-sm" id="generatePagoEstadoCuenta" style="display: none;">
                    <i class="far fa-money-bill-alt" style="font-size: 15px; vertical-align: middle;"></i>&nbsp;&nbsp;Pasarela de pago
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="generatePagoEstadoCuentaDisabled" style="box-shadow: none; color: white;" disabled>
                    <i class="far fa-money-bill-alt" style="font-size: 15px; vertical-align: middle;"></i>&nbsp;&nbsp;Pasarela de pago
                </button>
                &nbsp;
                <button type="button" class="btn btn-dark btn-sm" id="generateComprobanteEstadoCuenta" style="display: none;">
                    <i class="fas fa-file-invoice-dollar" style="font-size: 15px; vertical-align: middle;"></i>&nbsp;&nbsp;Adjuntar comprobante
                </button>
                <button type="button" class="btn btn-dark btn-sm" id="generateComprobanteEstadoCuentaDisabled" style="box-shadow: none; color: white;" disabled>
                    <i class="fas fa-file-invoice-dollar" style="font-size: 15px; vertical-align: middle;"></i>&nbsp;&nbsp;Adjuntar comprobante
                </button>
                <button type="button" class="btn btn-light btn-sm" id="reloadEstadoCuenta" style="padding: 8px;">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <div id="table_estado_cuenta" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                
                @include('pages.administrativo.estado_cuenta.estado_cuenta-table')

            </div>
        </div>

        <div id="table_pagos_estado_cuenta" class="card mb-4" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="card-body">
                
                @include('pages.administrativo.estado_cuenta.estado_cuenta_pagos-table')

            </div>
        </div>

        <div id="table_facturas_estado_cuenta" class="card mb-4" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="card-body">
                
                @include('pages.administrativo.estado_cuenta.estado_cuenta_facturas-table')

            </div>
        </div>

        @include('pages.administrativo.estado_cuenta.estado_cuenta-form')

        <script>
            
            var $idNitEstadoCuenta = JSON.parse('<?php echo $id_nit; ?>');
            var $idComprobante = JSON.parse('<?php echo $id_comprobante; ?>');
            var $pasarela_pagos = JSON.parse('<?php echo $pasarela_pagos; ?>');
            var $idCuentaIngreso = JSON.parse('<?php echo $id_cuenta_ingreso; ?>');
            var $numeroDocumentoEstadoCuenta = JSON.parse('<?php echo $numero_documento; ?>');
            
        </script>

    </div>
</div>