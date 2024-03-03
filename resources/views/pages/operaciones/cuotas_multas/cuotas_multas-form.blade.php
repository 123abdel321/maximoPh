<div class="modal fade" id="cuotaMultasFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCuotaMultaCreate" style="display: none;">Agregar cuotas / multas</h5>
                <h5 class="modal-title" id="textCuotaMultaUpdate" style="display: none;">Editar cuotas / multas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="cuotaMultasForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_cuota_multa_up" id="id_cuota_multa_up" style="display: none;">

                        <div id="input_masivo_cuotas_multas" class="form-check form-switch col-12 col-sm-6 col-md-4" style="padding-left: 4rem;">
                            <input class="form-check-input" type="checkbox" name="masivo_cuotas_multas" id="masivo_cuotas_multas" style="height: 20px;">
                            <label class="form-check-label" for="masivo_cuotas_multas">Agregar masivamente</label>
                        </div>

                        <div id="input_tipo_concepto_cuotas_multas" style="display: none;" class="form-group form-group col-12 col-sm-6 col-md-4">
                            <label for="tipo_concepto_cuotas_multas">Tipo concepto<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="tipo_concepto_cuotas_multas">
                                <option value="0">POR COEFICIENTE</option>
                                <option value="1">POR VALOR INDIVIDUAL</option>
                            </select>
                        </div>

                        <div id="input_id_concepto_tipo_facturacion_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="id_concepto_tipo_facturacion_cuotas_multas">Concepto facturacion</label>
                            <select name="id_concepto_tipo_facturacion_cuotas_multas" id="id_concepto_tipo_facturacion_cuotas_multas" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_zona_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="formZonaLabel">Zona</label>
                            <select name="id_zona_cuotas_multas" id="id_zona_cuotas_multas" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_inmueble_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Inmueble<span style="color: red">*</span></label>
                            <select name="id_inmueble_cuotas_multas" id="id_inmueble_cuotas_multas" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_nit_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="formZonaLabel">Cédula / nit<span style="color: red">*</span></label>
                            <select name="id_nit_cuotas_multas" id="id_nit_cuotas_multas" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_fecha_inicio_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4" >
                            <label for="example-text-input" class="form-control-label">Fecha inicio<span style="color: red">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio_cuotas_multas" id="fecha_inicio_cuotas_multas">
                        </div>

                        <div id="input_fecha_fin_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4" >
                            <label for="example-text-input" class="form-control-label">Fecha fin<span style="color: red">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="fecha_fin_cuotas_multas" id="fecha_fin_cuotas_multas">
                        </div>

                        <div id="input_id_concepto_facturacion_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Concepto<span style="color: red">*</span></label>
                            <select name="id_concepto_facturacion_cuotas_multas" id="id_concepto_facturacion_cuotas_multas" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_valor_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="example-text-input" class="form-control-label">Valor<span style="color: red">*</span></label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_cuotas_multas" id="valor_cuotas_multas" value="0">
                        </div>

                        <div id="input_observacion_cuotas_multas" style="display: none;" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="example-text-input" class="form-control-label">Observación<span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="observacion_cuotas_multas" id="observacion_cuotas_multas">
                        </div>



                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="cancelCuotaMulta" type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveCuotaMulta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateCuotaMulta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveCuotaMultaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>