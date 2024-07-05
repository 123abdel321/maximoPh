<div class="modal fade" id="porteriaEventoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-porteria-evento" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textPorteriaCreate" style="display: block;">Agregar evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}

                <div class="justify-content-center col-12 col-sm-12 col-md-12">
                    <div style="text-align: -webkit-center; height: 120px;">
                        <img id="default_avatar_evento" onclick="document.getElementById('imagen_evento').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 110px; height: auto; cursor: pointer; border-radius: 5%;">
                        <img id="new_avatar_evento" onclick="document.getElementById('imagen_evento').click();" src="" class="img-fluid border border-2 border-white" style="width: 110px; height: auto; cursor: pointer; border-radius: 5%;">
                    </div>
                </div>

                <input type="file" name="imagen_evento" id="imagen_evento" onchange="readURLEvento(this);" style="display: none" />

                <div class="form-group form-group col-12 col-sm-12 col-md-6">
                    <label for="exampleFormControlSelect1">Tipo evento<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" name="tipo_evento" id="tipo_evento">
                        <option value="0">Visita</option>
                        <option value="3">Domicilio</option>
                        <option value="1">Paquete</option>
                        <option value="2">Minuta</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="personaPorteriaEventoLabel">Persona / otros</label>
                    <select name="persona_porteria_evento" id="persona_porteria_evento" class="form-control form-control-sm">
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

            </div>
            
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
        </form>
    </div>
</div>