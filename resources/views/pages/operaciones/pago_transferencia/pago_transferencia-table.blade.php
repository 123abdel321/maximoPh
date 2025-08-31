<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Fecha desde</label>
        <input type="date" class="form-control form-control-sm" name="fecha_desde_estado_pago_comprobante" id="fecha_desde_estado_pago_comprobante">
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Fecha hasta</label>
        <input type="date" class="form-control form-control-sm" name="fecha_hasta_estado_pago_comprobante" id="fecha_hasta_estado_pago_comprobante">
    </div>

    <div class="form-group form-group col-12 col-sm-6 col-md-3">
        <label for="exampleFormControlSelect1">Estado</label>
        <select class="form-control form-control-sm" id="estado_pago_comprobante">
            <option value="todos">TODOS</option>
            <option value="0">RECHAZADOS</option>
            <option value="1">APROBADOS</option>
            <option value="2">PENDIENTES</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3" >
        <label for="example-text-input" class="form-control-label">Buscar</label>
        <input type="text" id="searchInputPagosTranferencia" class="form-control form-control-sm" onkeydown="searchPagoTranferencia(event)">
    </div>
</div>

<table id="pagoTransferenciaTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Documento</th>
            <th>Nombre</th>
            <th>Fecha recibo</th>
            <th>Valor</th>
            <th>Comprobante</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Estado</th>
        </tr>
    </thead>
</table>