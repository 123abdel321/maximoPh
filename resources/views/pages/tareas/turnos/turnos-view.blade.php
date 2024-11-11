<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-12 col-sm-12">
                    @can('turnos create')
                        <button type="button" class="btn btn-dark btn-sm" id="volverTurnos" style="display: none;"><i class="fas fa-step-backward back-icon-button" aria-hidden="true"></i>&nbsp;Volver</button>
                        <button type="button" class="btn btn-primary btn-sm" id="createTurno">Agregar tareas & turnos</button>
                        @endcan
                    <button type="button" class="btn btn-info btn-sm" id="detalleTurno">Ver detalle</button>
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadTurnos">
                        <i id="reloadTurnosIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                        <i id="reloadTurnosIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                </div>
            </div>
        </div>

        <div id="calendar_turnos" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                <div class="row" style="padding: 4px;">

                    @can('turnos create')
                        <div class="form-group  col-12 col-sm-4 col-md-3">
                            <label>Empleado</label>
                            <select name="id_usuario_filter_turno" id="id_usuario_filter_turno" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group  col-12 col-sm-4 col-md-3">
                            <label>Proyecto</label>
                            <select name="id_proyecto_filter_turno" id="id_proyecto_filter_turno" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>
                    @endcan

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label for="exampleFormControlSelect1">Tipo actividad</label>
                        <select class="form-control form-control-sm" id="tipo_actividad_filter_turno" name="tipo_actividad_filter_turno">
                            <option value="">TODAS</option>
                            <option value="0">TURNO</option>
                            <option value="1">TAREA</option>
                        </select>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label for="exampleFormControlSelect1">Estado</label>
                        <select class="form-control form-control-sm" id="estado_filter_turno" name="estado_filter_turno">
                            <option value="">TODOS</option>
                            <option value="0">SIN LEER</option>
                            <option value="3">VISTOS</option>
                            <option value="1">EN PROCESO</option>
                            <option value="2">CERRADOS</option>
                        </select>
                    </div>

                </div>

                <div id="turnos-fullcalender" style="flex-grow: 1; position: relative;"></div>

            </div>
        </div>

        <div id="tabla_turnos" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px; display: none;">
            <div class="card-body">

                @include('pages.tareas.turnos.turnos-table')

            </div>
        </div>
    </div>

    @include('pages.tareas.turnos.turnos-form')
    @include('pages.tareas.turnos.turnos-evento')
    
</div>

<script>
    var editarTurnos = '<?php echo auth()->user()->can('turnos update'); ?>';
    var eliminarTurnos = '<?php echo auth()->user()->can('turnos delete'); ?>';
</script>