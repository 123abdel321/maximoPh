<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .recibo_producto_load {
        position: absolute;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        margin-left: 75% !important;
    }

    .combo-grid-nit {
        min-width: 200px !important;
    }

    .combo-grid {
        min-width: 230px !important;
    }

    .drop-row-grid {
        margin-bottom: 0rem !important;
        font-size: 12px;
        margin-top: 4px;
        border-radius: 50px;
        width: 26px;
    }

    .fa-trash-alt {
        margin-left: -3px;
        margin-top: 1px;
    }
    #documentoReferenciaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #documentoReferenciaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .btn-group {
        box-shadow: 0 0px 0px rgba(50, 50, 93, 0.1), 0 0px 0px rgba(0, 0, 0, 0.08);
    }

    .normal_input {
        border-radius: 9px !important;
    }

    #reciboTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #reciboTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .table-captura-recibo {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-recibo thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-recibo > :not(caption) > * > * {
        padding: 0.2rem 0.2rem;
    }

    @media (min-width: 768px) {
        #tabla-captura-recibo.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-recibo-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-recibo-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .form-control.is-valid {
        background-position: left 0.5rem center !important;
    }

    .form-control.is-invalid {
        background-position: left 0.5rem center !important;
    }

</style>

<div class="container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

            @include('pages.operaciones.recibo.recibo-filter')

            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-recibo" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-recibo" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                @include('pages.operaciones.recibo.recibo-table')
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto">
            
            <div class="row justify-content-between">
                
                <div class="card mb-4 col-12 col-sm-7 col-md-12 ml-auto">

                    <table class="table table-bordered table-captura-ventas" width="100%" style="margin-top: 12px;">
                        <tbody>
                            <tr id="recibo_anticipo_disp_view" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500; color: #0bb19e;">ANTICIPOS DISP: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem; color: #0bb19e;" id="recibo_anticipo_disp">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">SALDO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="recibo_saldo">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">ABONO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="recibo_abono">0.00</h6></td>
                            </tr>
                            <tr id="recibo_anticipo_view" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">ANTICIPO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="recibo_anticipo">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-weight: bold;">SALDO NUEVO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="recibo_total">0.00</h6></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="overflow: auto;">
                        <table id="reciboFormaPago" class="table table-bordered display responsive table-captura-recibo" width="100%">
                            <thead>
                                <tr style="border: 0px !important;">
                                    <th style="border-radius: 15px 0px 0px 0px !important;">Pagos</th>
                                    <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <h6 id="total_faltante_recibo_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">FALTANTE: </h6>
                        </div>
                        <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                            <h6 id="total_faltante_recibo" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0.00</h6>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>

    <script>
        var comprobantesRecibos = JSON.parse('<?php echo $comprobantes; ?>');
    </script>
    
</div>