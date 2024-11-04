<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-3" style="align-self: center;">
        <label for="exampleFormControlSelect1">Categoria</label>
        <select class="form-control form-control-sm" id="tipo_porteria_filter" name="tipo_porteria_filter">
            <option value="">TODOS</option>
            <!-- <option value="1">INQUILINO</option> -->
            <option value="4">VISITANTE</option>
            <!-- <option value="2">MASCOTA</option>
            <option value="3">VEHICULO</option> -->
            <option value="5">PAQUETE</option>
            <option value="6">DOMICILIO</option>

        </select>
    </div>

    @if (auth()->user()->can('porteria eventos'))
        <div class="form-group  col-12 col-sm-6 col-md-3">
            <label>Cédula / Nit</label>
            <select name="id_nit_porteria_filter" id="id_nit_porteria_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
            </select>
        </div>

        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="example-text-input" class="form-control-label">Fecha</label>
            <input name="fecha_porteria_filter" id="fecha_porteria_filter" class="form-control form-control-sm" type="date" required>
        </div>
    @endif

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Buscar</label>
        <input type="text" id="searchInputPorteria" class="form-control form-control-sm" onkeydown="searchPorteria(event)">
    </div>
</div>

<table id="porteriaTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Img</th>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Placa</th>
            <th>Dias</th>
            <th>Estado</th>
            <th>Observacion</th>
            <th>Propietario / Residente</th>
            <th>Ubicacion</th>
            <th>Creación registro</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>