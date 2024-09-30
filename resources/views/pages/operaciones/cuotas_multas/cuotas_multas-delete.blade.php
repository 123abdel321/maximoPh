<div class="modal fade" id="cuotaMultasDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar cuotas / multas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="cuotaMultasDelete" style="margin-top: 10px;">
                    <div class="row">

                        <div id="input_id_concepto_tipo_facturacion_cuotas_multas" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="id_concepto_tipo_facturacion_cuotas_multas_delete">Concepto facturacion</label>
                            <select name="id_concepto_tipo_facturacion_cuotas_multas_delete" id="id_concepto_tipo_facturacion_cuotas_multas_delete" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_zona_cuotas_multas" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="id_zona_cuotas_multas_delete">Zona</label>
                            <select name="id_zona_cuotas_multas_delete" id="id_zona_cuotas_multas_delete" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_inmueble_cuotas_multas" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="id_inmueble_cuotas_multas_delete">Inmueble</label>
                            <select name="id_inmueble_cuotas_multas_delete" id="id_inmueble_cuotas_multas_delete" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_id_nit_cuotas_multas" class="form-group col-12 col-sm-6 col-md-4">
                            <label for="id_nit_cuotas_multas_delete">Cédula / nit</label>
                            <select name="id_nit_cuotas_multas_delete" id="id_nit_cuotas_multas_delete" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div id="input_fecha_inicio_cuotas_multas" class="form-group col-12 col-sm-6 col-md-4" >
                            <label for="fecha_inicio_cuotas_multas_delete" class="form-control-label">Mes</label>
                            <input type="month" class="form-control form-control-sm" name="fecha_inicio_cuotas_multas_delete" id="fecha_inicio_cuotas_multas_delete">
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="cancelDeleteCuotaMulta" type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="deleteCuotaMultasMasivo"type="button" class="btn bg-gradient-warning btn-sm">Eliminar</button>
                <button id="deleteCuotaMultaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>