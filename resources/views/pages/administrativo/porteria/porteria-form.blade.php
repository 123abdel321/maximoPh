<div class="modal fade" id="porteriaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <form id="form-porteria" class="" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="textPorteriaCreate" style="display: block;">Agregar en porteria</h5>
                    <h5 class="modal-title" id="textPorteriaUpdate" style="display: none;">Editar en porteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                
                <div class="row modal-body">

                    {{ csrf_field() }}
                    <input type="text" class="form-control" name="id_porteria_up" id="id_porteria_up" style="display: none;">

                    <div class="justify-content-center col-12 col-sm-6 col-md-6">
                        <div style="text-align: -webkit-center; height: 90px;">
                            <img id="default_avatar_porteria" onclick="document.getElementById('imagen_porteria').click();" src="/img/add-imagen.png" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                            <img id="new_avatar_porteria" onclick="document.getElementById('imagen_porteria').click();" src="" class="img-fluid border border-2 border-white" style="width: 80px; height: auto; cursor: pointer; border-radius: 5%;">
                        </div>
                    </div>

                    <input type="file" name="imagen_porteria" id="imagen_porteria" onchange="readURLPorteria(this);" style="display: none" />

                    <div class="form-group col-12 col-sm-6 col-md-6" style="align-self: center;">
                        <label for="exampleFormControlSelect1">Categorias<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" id="tipo_porteria_create" name="tipo_porteria_create">
                            <option value="1">PERSONA</option>
                            <option value="2">MASCOTA</option>
                            <option value="3">VEHICULO</option>
                        </select>
                    </div>

                    <div id="input_nombre_persona_porteria" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Nombre<span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="nombre_persona_porteria" id="nombre_persona_porteria" onfocus="this.select();">
                    </div>

                    <div id="input_tipo_vehiculo_porteria" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo vehiculo<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" name="tipo_vehiculo_porteria" id="tipo_vehiculo_porteria">
                            <option value="" >NINGUNO</option>
                            <option value="0">CARRO</option>
                            <option value="1">VEHICULO</option>
                            <option value="2">OTROS</option>
                        </select>
                    </div>

                    <div id="input_tipo_mascota_porteria" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo mascota<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" name="tipo_mascota_porteria" id="tipo_mascota_porteria">
                            <option value="0">CANINO</option>
                            <option value="1">FELINO</option>
                            <option value="2">OTROS</option>
                        </select>
                    </div>

                    <div id="input_placa_persona_porteria" style="display: none;" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Placa</label>
                        <input type="text" class="form-control form-control-sm" name="placa_persona_porteria" id="placa_persona_porteria" onfocus="this.select();">
                    </div>

                    <div id="input_observacion_persona_porteria" style="display: block;" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Observación</label>
                        <input type="text" class="form-control form-control-sm" name="observacion_persona_porteria" id="observacion_persona_porteria" onfocus="this.select();">
                    </div>

                    <div id="input_dias_porteria" class="form-group col-12 col-sm-12 col-md-12 row" style="place-content: center;">
                        <label for="exampleFormControlSelect1">Días </label><br/>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria0" name="diaPorteria0">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria0">Hoy</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria1" name="diaPorteria1">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria1">Lunes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria2" name="diaPorteria2">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria2">Martes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria3" name="diaPorteria3">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria3">Miercoles</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria4" name="diaPorteria4">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria4">Jueves</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria5" name="diaPorteria5">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria5">Viernes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria6" name="diaPorteria6">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria6">Sabado</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPorteria7" name="diaPorteria7">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPorteria7">Domingo</label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn bg-gradient-danger btn-sm" href="javascript:void(0)" data-bs-dismiss="modal">Cancelar</button>
                    <button id="savePorteria" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                    <button id="savePorteriaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
                
            </div>
        </form>
    </div>
</div>