<div class="row ">

    <div class="col-12 col-sm-4 col-md-4" style="margin-top: 5px; padding-bottom: 5px;">
        <div id="button_estado_cuenta" style="height: 100%;" class="card button-totals" onclick="showViewEstadoCuenta(1)">
            <div class="card-body p-2">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Consultar estado de cuenta</p>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">

                    <h5 id="total_estado_cuenta" class="font-weight-bolder mb-0">
                        0
                    </h5>

                    <span id="descuento_wrapper" style="display:none; color:#dc3545; font-weight:600;">
                        - <span id="descuento_estado_cuenta">0</span>
                    </span>

                    <span id="total_final_wrapper" style="display:none; color:#40b900; font-weight:700; font-size: 15px;">
                        = <span id="total_final_estado_cuenta">0</span>
                    </span>

                </div>
                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                    <i class="fas fa-money-check-alt text-lg opacity-10" style="top: 5px !important; font-size: 14px !important;" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4 col-md-4" style="margin-top: 5px; padding-bottom: 5px;">
        <div id="button_historico_pagos" style="height: 100%;" class="card button-totals" onclick="showViewEstadoCuenta(2)">
            <div class="card-body p-2">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Consultar historico de pagos</p>
                <div style="display: flex;">
                    <h5 id="pagos_estado_cuenta" class="font-weight-bolder">
                        0
                    </h5>
                </div>
                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                    <i class="fas fa-receipt text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4 col-md-4" style="margin-top: 5px; padding-bottom: 5px;">
        <div id="button_historico_cxc" style="height: 100%;" class="card button-totals" onclick="showViewEstadoCuenta(3)">
            <div class="card-body p-2">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Consultar historico cuentas cobro</p>
                <div style="display: flex;">
                    <h5 id="cuenta_cobro_estado_cuenta" class="font-weight-bolder">
                        0
                    </h5>
                </div>
                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                    <i class="ni ni-building text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

</div>