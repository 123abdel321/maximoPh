<div class="modal fade" id="inmuebleFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textInmuebleCreate" style="display: none;">Agregar Inmueble</h5>
                <h5 class="modal-title" id="textInmuebleUpdate" style="display: none;">Editar Inmueble</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="inmueblesForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_inmueble_up" id="id_inmueble_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="formZonaLabel">Zona</label>
                        <select name="id_zona_inmueble" id="id_zona_inmueble" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="formConceptoFacturacionLabel">Concepto facturación</label>
                        <select name="id_concepto_facturacion_inmueble" id="id_concepto_facturacion_inmueble" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_inmueble" id="nombre_inmueble" onfocus="this.select();" required>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Area (Area total {{ number_format($area_total_m2) }})</label>
                        <input type="text" class="form-control form-control-sm text-align-right" name="area_inmueble" id="area_inmueble" data-type="currency" onfocus="this.select();" onkeydown="changeArea(this)">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Coeficiente</label>
                        <input type="text" class="form-control form-control-sm text-align-right" name="coeficiente_inmueble" id="coeficiente_inmueble" data-type="currency" onfocus="this.select();">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Valor admon (Ppto mensual {{ number_format($valor_total_presupuesto_year_actual) }})</label>
                        <input type="text" class="form-control form-control-sm text-align-right" name="valor_total_administracion_inmueble" id="valor_total_administracion_inmueble" data-type="currency" onfocus="this.select();">
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveInmueble"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateInmueble"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveInmuebleLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>