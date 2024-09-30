<div class="modal fade bd-example-modal-lg" id="facturacionFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textFacturacionCreate">Generar Facturacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <table class="table table-bordered facturacion-totales-preview" width="100%" style="margin-top: 12px;">
                    <tbody id="facturacion-totales-preview">
                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Inmuebles</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="validar_inmuebles_facturacion">0</h5></td>
                            <td>
                                <h6 style="margin-bottom: 0px; float: right;">
                                    <i id="validar_inmuebles_facturacion_true" class="fas fa-check-circle" style="font-size: 1.2rem; color: rgb(9, 208, 4); float: inline-end; display: none;" aria-hidden="true"></i>
                                    <i id="validar_inmuebles_facturacion_false" class="fas fa-times-circle" style="font-size: 1.2rem; color: #d00404; float: inline-end; display: none;" aria-hidden="true"></i>
                                </h6>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Area</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="validar_area_facturacion">0</h5></td>
                            <td>
                                <h6 style="margin-bottom: 0px; float: right;">
                                    <i id="validar_area_facturacion_true" class="fas fa-check-circle" style="font-size: 1.2rem; color: rgb(9, 208, 4); float: inline-end; display: none;" aria-hidden="true"></i>
                                    <i id="validar_area_facturacion_false" class="fas fa-times-circle" style="font-size: 1.2rem; color: #d00404; float: inline-end; display: none;" aria-hidden="true"></i>
                                </h6>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Coheficiente</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="validar_coeficiente_facturacion">0</h5></td>
                            <td>
                                <h6 style="margin-bottom: 0px; float: right;">
                                    <i id="validar_coeficiente_facturacion_true" class="fas fa-check-circle" style="font-size: 1.2rem; color: rgb(9, 208, 4); float: inline-end; display: none;" aria-hidden="true"></i>
                                    <i id="validar_coeficiente_facturacion_false" class="fas fa-times-circle" style="font-size: 1.2rem; color: #d00404; float: inline-end; display: none;" aria-hidden="true"></i>
                                </h6>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Presupuesto Mes</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="validar_presupuesto_facturacion">0</h5></td>
                            <td>
                                <h6 style="margin-bottom: 0px; float: right;">
                                    <i id="validar_presupuesto_facturacion_true" class="fas fa-check-circle" style="font-size: 1.2rem; color: rgb(9, 208, 4); float: inline-end; display: none;" aria-hidden="true"></i>
                                    <i id="validar_presupuesto_facturacion_false" class="fas fa-times-circle" style="font-size: 1.2rem; color: #d00404; float: inline-end; display: none;" aria-hidden="true"></i>
                                </h6>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Saldo anterior</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="validar_saldo_anterior_facturacion">0</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right;" id="text_count_saldo_anterior_facturacion"></h5></td>
                        </tr>

                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Intereses</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="text_total_intereses_facturacion">0</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right;" id="text_count_intereses_facturacion"></h5></td>
                        </tr>

                        <tr>
                            <td><h5 style="margin-bottom: 0px; font-size: 1.2rem;">Anticipos</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right; font-size: 1.2rem;" id="text_total_anticipos_facturacion">0</h5></td>
                            <td><h5 style="margin-bottom: 0px; float: right;" id="text_count_anticipos_facturacion"></h5></td>
                        </tr>
                        
                    </tbody>
                </table>

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