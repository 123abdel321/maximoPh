<div class="modal fade" id="turnoEventoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <form id="form-turno-evento" class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="texTurnoEvento" style="display: block;">Agregar evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="row modal-body">

                {{ csrf_field() }}

                <input type="text" class="form-control" name="id_turno_evento" id="id_turno_evento" style="display: none;">

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <label for="responsable_turno" class="form-control-label">Responsable</label>
                    <input type="text" class="form-control form-control-sm" name="responsable_turno" id="responsable_turno" disabled>
                </div>

                <div class="form-group col-12 col-sm-6 col-md-6">
                    <p style="margin-bottom: 3px; font-size: 12px; font-weight: bold; color: black;">Estado</p>
                    <span id="estado_turno" class="badge" style="margin-bottom: 0rem !important; min-width: 50px; background-color: #4048e4;">Estado</span>
                </div>

                <p id="texto-eventos" style="margin-bottom: 0px; font-size: 13px; color: #445573; font-weight: 700;">Eventos</p>
                <div id="div-contenido-eventos" class="container col-12">
                    <div id="view-contenido-eventos" style="padding: 10px 25px 10px 25px; border-radius: 5px; border: solid 1px #d3d3d3;">

                        <div id="" class="row" style="padding: 5px; background-color: #defaff; border-radius: 10px;">
                            <div class="">
                                <p>Nombre aca</p>
                            </div>
                            
                            <div id="text-usuario-evento" class="col-12" style="place-self: center;">
                                NOMBRE
                            </div>
                        </div>

                    </div>
                </div>

                <div style="display: block; margin-top: 10px;" class="form-group col-12 col-sm-12 col-md-12">
                    <label for="mensaje_turno_evento" class="form-control-label">Mensaje<span style="color: red">*</span></label>
                    <textarea class="form-control form-control-sm" id="mensaje_turno_evento" name="mensaje_turno_evento" rows="2" required></textarea>
                </div>

                <div class="input-field">
                    <label class="active">Imagenes</label>
                    <div class="input-images-turno-evento" style="padding-top: .5rem;"></div>
                </div>

            </div>
            
            <div class="modal-footer">
                @if (auth()->user()->can('turnos delete'))
                    <span id="deleteTurno" href="javascript:void(0)" class="btn bg-gradient-danger btn-sm">
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </span>
                @endif
                <span href="javascript:void(0)" class="btn bg-gradient-warning btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="saveEventoTurno" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveEventoTurnoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </form>
    </div>
</div>