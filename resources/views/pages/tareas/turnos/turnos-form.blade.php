<div class="modal fade" id="turnoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-turno" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textTurnoCreate" style="display: block;">Agregar Turno & Tarea</h5>
                <!-- <h5 class="modal-title" id="textTurnoUpdate" style="display: none;">Editar Turno</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}

                <input type="text" class="form-control" name="id_turno_up" id="id_turno_up" style="display: none;">

                <div class="form-group col-6 col-sm-6 col-md-6">
                    <label for="exampleFormControlSelect1">Tipo tarea</label>
                    <select class="form-control form-control-sm" id="tipo_turno" name="tipo_turno">
                        <option value="0">TURNO</option>
                        <option value="1">TAREA</option>
                        <!-- <option value="2">EVENTO</option> -->
                    </select>
                </div>

                <div class="form-group col-6 col-sm-6 col-md-6">
                    <label for="exampleFormControlSelect1">Proyecto</label>
                    <select class="form-control form-control-sm" id="id_proyecto_turno" name="id_proyecto_turno">
                    </select>
                </div>

                <div id="input_id_usuario_turno" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="formZonaLabel">Usuario <span style="color: red">*</span></label>
                    <select name="id_usuario_turno" id="id_usuario_turno" class="form-control form-control-sm" required>
                    </select>
                </div>

                <div id="input_hora_inicio_turno" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Fecha inicio</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_inicio_turno" id="fecha_inicio_turno">
                </div>

                <div id="input_hora_fin_turno" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Fecha fin</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_fin_turno" id="fecha_fin_turno">
                </div>

                <div id="input_hora_inicio_turno" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Hora inicio</label>
                    <input type="time" class="form-control form-control-sm" name="hora_inicio_turno" id="hora_inicio_turno">
                </div>

                <div id="input_hora_fin_turno" class="form-group col-12 col-sm-6 col-md-6">
                    <label for="example-text-input" class="form-control-label">Hora fin</label>
                    <input type="time" class="form-control form-control-sm" name="hora_fin_turno" id="hora_fin_turno">
                </div>

                <div class="form-check form-switch col-12 col-sm-6 col-md-6">
                    <input class="form-check-input" type="checkbox" name="multiple_tarea_turno" id="multiple_tarea_turno" style="height: 20px;">
                    <label class="form-check-label" for="multiple_tarea_turno">
                        Multiple tarea
                    </label>
                </div>

                <div id="input_dias_turno" class="form-group col-12 col-sm-12 col-md-12 row" style="place-content: center; text-align: -webkit-center; display: none;">
                    <label for="exampleFormControlSelect1">Días </label><br/>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno1" name="diaTurno1">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno1">Lunes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno2" name="diaTurno2">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno2">Martes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno3" name="diaTurno3">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno3">Miercoles</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno4" name="diaTurno4">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno4">Jueves</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno5" name="diaTurno5">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno5">Viernes</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno6" name="diaTurno6">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno6">Sabado</label>
                    </div>
                    <div class="form-check form-check-inline col-3">
                        <input class="form-check-input" type="checkbox" id="diaTurno7" name="diaTurno7">
                        <label style="font-size: 13px; margin-left: -2px;" class="form-check-label" for="diaTurno7">Domingo</label>
                    </div>
                </div>

                <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                    <label for="example-text-input" class="form-control-label">Asunto</label>
                    <input type="text" class="form-control form-control-sm" name="asunto_turno" id="asunto_turno" onfocus="this.select();">
                </div>

                <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                    <label for="example-text-input" class="form-control-label">Mensaje<span style="color: red">*</span></label>
                    <textarea class="form-control form-control-sm" id="mensaje_turno" name="mensaje_turno" rows="2" required></textarea>
                </div>

                <div class="input-field">
                    <label class="active">Imagenes</label>
                    <div class="input-images-turno" style="padding-top: .5rem;"></div>
                </div>

            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="saveTurno" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveTurnoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>