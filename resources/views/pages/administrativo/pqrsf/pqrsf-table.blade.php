<div class="row" style="padding: 4px;">

    @can('pqrsf responder')
        <div class="form-group  col-12 col-sm-4 col-md-4">
            <label>Cédula / Nit</label>
            <select name="id_nit_pqrsf_filter" id="id_nit_pqrsf_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
            </select>
        </div>
    @endif

    <div class="form-group col-6 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Tipo<span style="color: red">*</span></label>
        <select class="form-control form-control-sm" id="tipo_pqrsf_filter" name="tipo_pqrsf_filter">
            <option value="">TODOS</option>
            <!-- <option value="5">TAREA</option> -->
            <option value="0">PETICIONES</option>
            <option value="1">QUEJAS</option>
            <option value="2">RECLAMOS</option>
            <option value="3">SOLICITUDES</option>
            <option value="4">FELICITACIONES</option>
        </select>
    </div>

    <div class="form-group col-6 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Área</label>
        <select class="form-control form-control-sm" id="area_pqrsf_filter" name="area_pqrsf_filter">
            <option value="">TODAS</option>
            <option value="1">ADMINISTRACIÓN</option>
            <option value="2">SEGURIDAD</option>
            <option value="3">ASEO</option>
            <option value="4">MANTENIMIENTO</option>
            <option value="5">ZONAS COMUNES</option>
        </select>
    </div>

    <div class="form-group col-6 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Estado</label>
        <select class="form-control form-control-sm" id="estado_pqrsf_filter" name="estado_pqrsf_filter">
            <option value="">TODOS</option>
            <option value="0">SIN LEER</option>
            <option value="3">VISTOS</option>
            <option value="1">EN PROCESO</option>
            <option value="2">CERRADOS</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input name="fecha_desde_pqrsf_filter" id="fecha_desde_pqrsf_filter" class="form-control form-control-sm" type="date">
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input name="fecha_hasta_pqrsf_filter" id="fecha_hasta_pqrsf_filter" class="form-control form-control-sm" type="date">
    </div>

</div>

<table id="pqrsfTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Código</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Area</th>
            <th>Destinatario</th>
            <th>Remitente</th>
            <th>Ubicación</th>
            <th>Cedula / Nit</th>
            <th>Asunto</th>
            <th style="width: 500px !important;">Descripción</th>
            <th>Fecha/hora creación</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>