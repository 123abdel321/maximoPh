<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-4 col-md-4" style="align-self: center;">
        <label for="exampleFormControlSelect1">Categoria</label>
        <select class="form-control form-control-sm" id="tipo_familia_filter" name="tipo_familia_filter">
            <option value="">TODOS</option>
            <option value="1">FAMILIA</option>
            <option value="2">MASCOTA</option>
            <option value="3">VEHICULO</option>

        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label>Cédula / Nit</label>
        <select name="id_nit_familia_filter" id="id_nit_familia_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4" >
        <label for="example-text-input" class="form-control-label">Buscar</label>
        <input type="text" id="searchInputFamilia" class="form-control form-control-sm">
    </div>
</div>

<table id="familiaTable" class="table table-bordered display responsive" width="100%">
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