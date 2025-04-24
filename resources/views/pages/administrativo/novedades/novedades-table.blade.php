<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-4 col-md-3">
        <label for="id_porteria_novedad_filter">Responsable </label>
        <select name="id_porteria_novedad_filter" id="id_porteria_novedad_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-3">
        <label for="area_novedades_filter">Tipo</label>
        <select class="form-control form-control-sm" id="tipo_novedades_filter" name="area_pqrsf_filter">
            <option value="">TODOS</option>
            <option value="1">MULTA</option>
            <option value="2">NOVEDAD</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-3">
        <label for="area_novedades_filter">Área</label>
        <select class="form-control form-control-sm" id="area_novedades_filter" name="area_pqrsf_filter">
            <option value="">TODOS</option>
            <option value="1">ADMINISTRACIÓN</option>
            <option value="2">SEGURIDAD</option>
            <option value="3">ASEO</option>
            <option value="4">MANTENIMIENTO</option>
            <option value="5">ZONAS COMUNES</option>
        </select>
    </div>

    <div id="input_fecha_desde_novedades" class="form-group col-12 col-sm-4 col-md-3">
        <label for="fecha_desde_novedades" class="form-control-label">Fecha</label>
        <input type="datetime-local" class="form-control form-control-sm" name="fecha_desde_novedades" id="fecha_desde_novedades">
    </div>

    <div id="input_fecha_hasta_novedades" class="form-group col-12 col-sm-4 col-md-3">
        <label for="fecha_hasta_novedades" class="form-control-label">Fecha</label>
        <input type="datetime-local" class="form-control form-control-sm" name="fecha_hasta_novedades" id="fecha_hasta_novedades">
    </div>

</div>

<table id="novedadesTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;"></th>
            <th>Nombre</th>
            <th>Propietario</th>
            <th>Ubicacion</th>
            <th>Tipo</th>
            <th>Área</th>
            <th>Fecha</th>
            <th>Asunto</th>
            <th>Mensaje</th>
            <th>Archivos</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>