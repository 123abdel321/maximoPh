<div class="row" style="padding: 4px;">
    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Periodo</label>
        <input type="month" class="form-control form-control-sm" name="periodo_cuotas_multas" id="periodo_cuotas_multas">
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="id_concepto_filter_cuotas_multas">Concepto</label>
        <select name="id_concepto_filter_cuotas_multas" id="id_concepto_filter_cuotas_multas" class="form-control form-control-sm">
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="formZonaLabel">Cédula / nit</label>
        <select name="id_nit_filter_cuotas_multas" id="id_nit_filter_cuotas_multas" class="form-control form-control-sm">
        </select>
    </div>

    <div class="form-group col-12 col-sm-3 col-md-2 row" style="margin-bottom: 0.1rem !important;">
        <label for="example-text-input" class="form-control-label">Niveles</label>
        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
            <input class="form-check-input" type="radio" name="nivel_cuotas_multas" id="nivel_cuotas_multas1" style="font-size: 11px;">
            <label class="form-check-label" for="nivel_cuotas_multas1" style="font-size: 11px;">
                Grupos
            </label>
        </div>
        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
            <input class="form-check-input" type="radio" name="nivel_cuotas_multas" id="nivel_cuotas_multas2" style="font-size: 11px;" checked>
            <label class="form-check-label" for="nivel_cuotas_multas2" style="font-size: 11px;">
                Detalle
            </label>
        </div>
    </div>

</div>

<table id="cuotaMultaTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">id</th>
            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Total</th>
            <th>Zona</th>
            <th>Inmueble</th>
            <th>Area Mt2</th>
            <th>Coeficiente %</th>
            <th>Observación</th>
            <th>Creación registro</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>