<div class="col-12 col-sm-6 col-md-2" style="margin-top: 5px; padding-bottom: 5px;">
    <div class="card" style="height: 100%;">
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

<div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
    <div class="card" style="height: 100%;">
        <div class="card-body p-2">
            <p class="text-sm mb-0 text-uppercase font-weight-bold">Área M2 Registrada</p>
            <div style="display: flex;">
                <h5 id="area2_registrados_facturacion" class="font-weight-bolder">
                    0
                </h5>
                <h5 class="font-weight-bolder">
                    &nbsp;de {{ number_format($area_total_m2, 2) }}
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

<div class="col-12 col-sm-6 col-md-4" style="margin-top: 5px; padding-bottom: 5px;">
    <div class="card" style="height: 100%;">
        <div class="card-body p-2">
            <p class="text-sm mb-0 text-uppercase font-weight-bold">Presupuesto Mensual</p>
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