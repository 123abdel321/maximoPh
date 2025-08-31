<div class="row" style="padding: 4px;">

    @can('turnos create')
        <div class="form-group  col-12 col-sm-4 col-md-4">
            <label>Empleado</label>
            <select name="id_usuario_filter_turno_table" id="id_usuario_filter_turno_table" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
            </select>
        </div>
    @endcan

    <div class="form-group  col-12 col-sm-4 col-md-4">
        <label>Proyecto</label>
        <select name="id_proyecto_filter_turno_table" id="id_proyecto_filter_turno_table" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="form-group col-6 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Tipo actividad</label>
        <select class="form-control form-control-sm" id="tipo_actividad_filter_turno_table" name="tipo_actividad_filter_turno_table">
            <option value="">TODAS</option>
            <option value="0">TURNO</option>
            <option value="1">TAREA</option>
        </select>
    </div>

    <div class="form-group col-6 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Estado</label>
        <select class="form-control form-control-sm" id="estado_turnos_filter_table" name="estado_turnos_filter_table">
            <option value="">TODOS</option>
            <option value="0">SIN LEER</option>
            <option value="3">VISTOS</option>
            <option value="1">EN PROCESO</option>
            <option value="2">CERRADOS</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input name="fecha_desde_turnos_filter" id="fecha_desde_turnos_filter" class="form-control form-control-sm" type="date">
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input name="fecha_hasta_turnos_filter" id="fecha_hasta_turnos_filter" class="form-control form-control-sm" type="date">
    </div>

</div>

<table id="turnosTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Código</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Empleado</th>
            <th>Asunto</th>
            <th style="width: 500px !important;">Descripción</th>
            <th>Fecha/hora inicio</th>
            <th>Fecha/hora fin</th>
            <th>Fecha/hora creación</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>