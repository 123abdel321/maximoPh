<div class="row" style="padding: 4px;">
    <div class="form-group col-12 col-sm-6 col-md-6" >
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input type="date" class="form-control form-control-sm" name="fecha_desde_estado_cuenta_facturas" id="fecha_desde_estado_cuenta_facturas">
    </div>

    <div class="form-group col-12 col-sm-6 col-md-6" >
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input type="date" class="form-control form-control-sm" name="fecha_hasta_estado_cuenta_facturas" id="fecha_hasta_estado_cuenta_facturas">
    </div>
</div>

<table id="estadoCuentaFacturasTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Factura</th>
            <th>Total factura</th>
            <th>Fecha generada</th>
            <th>Concepto</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acción</th>
        </tr>
    </thead>
</table>