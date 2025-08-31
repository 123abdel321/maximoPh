<div class="row" style="padding: 4px;">
    <div class="form-group col-12 col-sm-4 col-md-4" >
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input type="date" class="form-control form-control-sm" name="fecha_desde_estado_cuenta_pagos" id="fecha_desde_estado_cuenta_pagos">
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4" >
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input type="date" class="form-control form-control-sm" name="fecha_hasta_estado_cuenta_pagos" id="fecha_hasta_estado_cuenta_pagos">
    </div>

    <div class="form-group form-group col-12 col-sm-4 col-md-4">
        <label for="exampleFormControlSelect1">Estado</label>
        <select class="form-control form-control-sm" id="estado_estado_cuenta_pagos">
            <option value="">TODOS</option>
            <option value="0">RECHAZADOS</option>
            <option value="1">APROBADOS</option>
            <option value="2">PENDIENTES</option>
        </select>
    </div>
</div>

<table id="estadoCuentaPagosTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Fecha pago</th>
            <th>Total recibo</th>
            <th>estado</th>
            <th>Metodo pago</th>
            <th>Observación</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acción</th>
        </tr>
    </thead>
</table>