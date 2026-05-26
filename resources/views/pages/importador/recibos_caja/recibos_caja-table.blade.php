<div class="mt-2" id="uploadStatusRecibos" style="display: none;">
    <div class="d-flex align-items-center mb-2">
        <div class="progress flex-grow-1 me-2" style="height: 6px;">
            <div id="uploadProgressRecibos" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                 role="progressbar" style="width: 0%"></div>
        </div>
        <small id="progressTextRecibos" class="text-muted small fw-bold">0%</small>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span id="statusTextRecibos" class="badge bg-info text-dark bg-opacity-10 border border-info border-opacity-25">
                <i class="fas fa-spinner fa-spin me-1"></i>
                <span id="statusMessageRecibos">Preparando carga...</span>
            </span>
        </div>
        <div>
            <small id="statsTextRecibos" class="text-muted small">
                <span id="processedRowsRecibos">0</span> / <span id="totalRowsRecibos">0</span> registros
                <i class="fas fa-file-excel ms-1 text-success"></i>
            </small>
        </div>
    </div>
</div>

<table id="importRecibos" class="table-import-recibos table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Row</th>
            <th style="border-radius: 15px 0px 0px 0px !important;">Row</th>
            <th>Inmueble</th>
            <th>Concepto Fac.</th>
            <th>Documento</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Fecha manual</th>
            <th>Saldo Actual</th>
            <th>Valor Pago</th>
            <th>Valor Descuento</th>
            <th>Faltante Descuento</th>
            <th>Nuevo Saldo</th>
            <th>Nuevo Anticipo</th>
            <th>Comprobante</th>
            <th>Consecutivo</th>
            <th>Concepto</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Estado</th>
        </tr>
    </thead>
</table>