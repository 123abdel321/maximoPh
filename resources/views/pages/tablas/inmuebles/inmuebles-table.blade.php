<div class="row" style="padding: 4px;">

    <div class="form-group  col-12 col-sm-6 col-md-3">
        <label>Cédula / Nit</label>
        <select name="id_nit_inmueble_filter" id="id_nit_inmueble_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="form-group  col-12 col-sm-6 col-md-3">
        <label>Zona</label>
        <select name="id_zona_inmueble_filter" id="id_zona_inmueble_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="form-group  col-12 col-sm-6 col-md-3">
        <label>Concepto</label>
        <select name="id_concepto_facturacion_inmueble_filter" id="id_concepto_facturacion_inmueble_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Nombre inmueble</label>
        <input type="text" id="searchInputInmuebles" class="form-control form-control-sm" placeholder="Buscar" onkeydown="searchInmuebles(event)">
    </div>
</div>

<table id="inmuebleTable" class="table table-bordered display responsive" width="100%" style="margin-top: -15px;">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Inmueble</th>
            <th>Zona</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Concepto</th>
            <th>Total %</th>
            <th>Area M2</th>
            <th>Coeficiente</th> 
            <th>Valor admon</th>
            <!-- <th>Valor total admon</th> -->
            <th>Creación registro</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>