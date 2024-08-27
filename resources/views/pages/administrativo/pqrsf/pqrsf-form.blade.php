<div class="modal fade" id="pqrsfFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-pqrsf" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textPqrsfCreate" style="display: block;">Agregar Pqrsf</h5>
                <h5 class="modal-title" id="textPqrsfUpdate" style="display: none;">Editar Pqrsf</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}

                <input type="text" class="form-control" name="id_pqrsf_up" id="id_pqrsf_up" style="display: none;">

                <div class="form-group col-6 col-sm-6 col-md-6">
                    <label for="exampleFormControlSelect1">Tipo solicitud<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" id="tipo_pqrsf" name="tipo_pqrsf">
                        @if ($usuario_empresa->id_rol == 1 || $usuario_empresa->id_rol == 2)
                            <option value="5">TAREA</option>
                        @else
                            <option value="0">PETICIONES</option>
                            <option value="1">QUEJAS</option>
                            <option value="2">RECLAMOS</option>
                            <option value="3">SOLICITUDES</option>
                            <option value="4">FELICITACIONES</option>
                        @endif
                    </select>
                </div>

                @if ($usuario_empresa->id_rol == 1 || $usuario_empresa->id_rol == 2)
                    <div id="input_id_usuario_pqrsf" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="formZonaLabel">Usuario <span style="color: red">*</span></label>
                        <select name="id_usuario_pqrsf" id="id_usuario_pqrsf" class="form-control form-control-sm" required>
                        </select>
                    </div>

                    <div id="input_hora_inicio_pqrsf" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Hora inicio</label>
                        <input type="time" class="form-control form-control-sm" name="hora_inicio_pqrsf" id="hora_inicio_pqrsf">
                    </div>

                    <div id="input_hora_fin_pqrsf" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Hora fin</label>
                        <input type="time" class="form-control form-control-sm" name="hora_fin_pqrsf" id="hora_fin_pqrsf">
                    </div>

                    <div id="input_dias_pqrsf" class="form-group col-12 col-sm-12 col-md-12 row" style="place-content: center; text-align: -webkit-center;">
                        <label for="exampleFormControlSelect1">Días </label><br/>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf0" name="diaPqrsf0">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf0">Hoy</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf1" name="diaPqrsf1">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf1">Lunes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf2" name="diaPqrsf2">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf2">Martes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf3" name="diaPqrsf3">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf3">Miercoles</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf4" name="diaPqrsf4">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf4">Jueves</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf5" name="diaPqrsf5">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf5">Viernes</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf6" name="diaPqrsf6">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf6">Sabado</label>
                        </div>
                        <div class="form-check form-check-inline col-2">
                            <input class="form-check-input" type="checkbox" id="diaPqrsf7" name="diaPqrsf7">
                            <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaPqrsf7">Domingo</label>
                        </div>
                    </div>
                @endif

                <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                    <label for="example-text-input" class="form-control-label">Asunto</label>
                    <input type="text" class="form-control form-control-sm" name="asunto_pqrsf" id="asunto_pqrsf" onfocus="this.select();">
                </div>

                <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                    <label for="example-text-input" class="form-control-label">Mensaje<span style="color: red">*</span></label>
                    <textarea class="form-control form-control-sm" id="mensaje_pqrsf" name="mensaje_pqrsf" rows="2" required></textarea>
                </div>

                <div class="input-field">
                    <label class="active">Imagenes</label>
                    <div class="input-images-pqrsf" style="padding-top: .5rem;"></div>
                </div>

            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="savePqrsf" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePqrsfLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>