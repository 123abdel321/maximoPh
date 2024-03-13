<div class="row" style="padding: 4px;">

    <div class="form-group form-group col-12 col-sm-6 col-md-6">
        <label for="exampleFormControlSelect1">Estado</label>
        <select class="form-control form-control-sm" id="estado_pago_comprobante">
            <option value="todos">TODOS</option>
            <option value="0">RECHAZADOS</option>
            <option value="1">APROBADOS</option>
            <option value="2">PENDIENTES</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-6" >
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