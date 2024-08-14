<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.informes.impuestos_iva.impuestos_iva-filter')
            </div>
        </div>

        <!-- <div class="card cardTotalImpuestoIva" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-3 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">SALDO ANTERIOR</p>
                    <h6 id="impuesto_iva_anterior" style="margin-top: -15px; color: #0002ff">$0</h6>
                </div>
                <div class="col-3 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">TOTAL FACTURAS</p>
                    <h6 id="impuesto_iva_facturas" style="margin-top: -15px; color: #0002ff">$0</h6>
                </div>
                <div class="col-3 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">TOTAL ABONOS</p>
                    <h6 id="impuesto_iva_abonos" style="margin-top: -15px; color: #0002ff">$0</h6>
                </div>
                <div class="col-3 col-md-3 col-sm-3">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">SALDO FINAL</p>
                    <h6 id="impuesto_iva_diferencia" style="margin-top: -15px; color: #0002ff">$0</h6>
                </div>
            </div>
        </div> -->
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.informes.impuestos_iva.impuestos_iva-table')
        </div>
    </div>

</div>

