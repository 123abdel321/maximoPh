<div class="modal fade" id="porteriaEventoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div  class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title" id="textPorteriaEventoCreate" style="display: block;">Agregar evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <form id="form-porteria-evento" class="row modal-body">

                <input type="text" class="form-control" name="id_porteria_evento" id="id_porteria_evento" style="display: none;">

                <div class="justify-content-center col-12 col-sm-12 col-md-12">
                    <div style="text-align: -webkit-center; margin-bottom: 10px;">
                        <img id="img_porteria_evento"  src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="height: 130px; border-radius: 5%; width: auto; object-fit: contain;">
                    </div>
                </div>

                <div class="form-group form-group col-12 col-sm-6 col-md-6">
                    <label for="exampleFormControlSelect1">Tipo evento<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_evento" id="tipo_evento" disabled>
                        <option value="4">VISITANTE</option>
                        <option value="6">DOMICILIO</option>
                        <option value="5">PAQUETE</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="personaPorteriaEventoLabel">Item porteria</label>
                    <select name="persona_porteria_evento" id="persona_porteria_evento" class="form-control form-control-sm">
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="inmueblePorteriaEventoLabel">Cedula / Nit</label>
                    <select name="id_nit_porteria_evento" id="id_nit_porteria_evento" class="form-control form-control-sm">
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="inmueblePorteriaEventoLabel">Inmueble</label>
                    <select name="inmueble_porteria_evento" id="inmueble_porteria_evento" class="form-control form-control-sm">
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6" >
                    <label for="fecha_salida_porteria_evento-input" class="form-control-label">Fecha ingreso</label>
                    <input type="datetime-local" class="form-control form-control-sm" name="fecha_ingreso_porteria_evento" id="fecha_ingreso_porteria_evento">
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6" >
                    <label for="fecha_salida_porteria_evento-input" class="form-control-label">Fecha salida</label>
                    <input type="datetime-local" class="form-control form-control-sm" name="fecha_salida_porteria_evento" id="fecha_salida_porteria_evento">
                </div>

                <div class="form-group col-12 col-sm-12 col-md-12" >
                    <label for="observacion_porteria_evento-input" class="form-control-label">Observación</label>
                    <textarea class="form-control form-control-sm" id="observacion_porteria_evento" name="observacion_porteria_evento" rows="2"></textarea>
                </div>

                <div class="container">
                    <label for="porteria-eventos-files-input" class="form-control-label">Registro de novedades</label>
                    <input type="file" class="filepond" id="porteria-eventos-files" name="images[]" multiple>
                </div>

            </form>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="savePorteriaEvento" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePorteriaEventoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>