<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-3" style="align-self: center;">
        <label for="exampleFormControlSelect1">Tipo evento</label>
        <select class="form-control form-control-sm" id="tipo_evento_porteria_filter" name="tipo_evento_porteria_filter">
            <option value="">Todos</option>
            <option value="0">Visita</option>
            <option value="1">Paquete</option>
            <option value="2">Minuta</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="inmueblePorteriaEventoLabel">Inmueble / Persona</label>
        <select name="inmueble_porteria_evento_filter" id="inmueble_porteria_evento_filter" class="form-control form-control-sm">
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="example-text-input" class="form-control-label">Fecha</label>
        <input name="fecha_porteria_evento_filter" id="fecha_porteria_evento_filter" class="form-control form-control-sm" type="date" required>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Buscar</label>
        <input type="text" id="searchInputPorteriaEvento" class="form-control form-control-sm" onkeydown="searchPorteriaEvento(event)">
    </div>
</div>

<table id="eventoPorteriaTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Img</th>
            <th>Tipo</th>
            <th>Inmueble</th>
            <th>Persona</th>
            <th>Fecha/Hora ingreso</th>
            <th>Fecha/Hora salida</th>
            <th>Observación</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Creación registro</th>
            <!-- <th >Ultima actualización</th> -->
        </tr>
    </thead>
</table>