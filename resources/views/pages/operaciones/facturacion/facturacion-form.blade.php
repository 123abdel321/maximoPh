<div class="modal fade" id="facturacionFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textFacturacionCreate">Generar Facturacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12" >
                        <h5 id="validar_inmuebles_facturacion" style="font-size: 15px; border-bottom: solid 1px #ebebeb; padding: 5px;"><h5>
                        <i id="validar_inmuebles_facturacion_true" class="fas fa-check-circle" style="font-size: 20px; color: rgb(9, 208, 4); float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                        <i id="validar_inmuebles_facturacion_false" class="fas fa-times-circle" style="font-size: 20px; color: #d00404; float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12" >
                        <h5 id="validar_area_facturacion" style="font-size: 15px; border-bottom: solid 1px #ebebeb; padding: 5px;"><h5>
                        <i id="validar_area_facturacion_true" class="fas fa-check-circle" style="font-size: 20px; color: rgb(9, 208, 4); float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                        <i id="validar_area_facturacion_false" class="fas fa-times-circle" style="font-size: 20px; color: #d00404; float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12">
                        <h5 id="validar_coeficiente_facturacion" style="font-size: 15px; border-bottom: solid 1px #ebebeb; padding: 5px;"><h5>
                        <i id="validar_coeficiente_facturacion_true" class="fas fa-check-circle" style="font-size: 20px; color: rgb(9, 208, 4); float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                        <i id="validar_coeficiente_facturacion_false" class="fas fa-times-circle" style="font-size: 20px; color: #d00404; float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12">
                        <h5 id="validar_presupuesto_facturacion" style="font-size: 15px; border-bottom: solid 1px #ebebeb; padding: 5px;"><h5>
                        <i id="validar_presupuesto_facturacion_true" class="fas fa-check-circle" style="font-size: 20px; color: rgb(9, 208, 4); float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                        <i id="validar_presupuesto_facturacion_false" class="fas fa-times-circle" style="font-size: 20px; color: #d00404; float: inline-end; margin-top: -35px; display: none;" aria-hidden="true"></i>
                    </div>

                    <div id="div_total_multas_facturacion" class="col-12 col-sm-12 col-md-12">
                        <h5 id="text_total_multas_facturacion" style="font-size: 15px; border-bottom: solid 1px #ebebeb; padding: 5px;"><h5>
                        <i class="fas fa-check-circle" style="font-size: 20px; color: rgb(9, 208, 4); float: inline-end; margin-top: -35px;" aria-hidden="true"></i>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button id="cancelFacturacion" type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveFacturacion" type="button" class="btn bg-gradient-success btn-sm">Generar facturación</button>
                <button id="saveFacturacionLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Generando facturación
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>