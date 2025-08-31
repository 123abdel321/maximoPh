<style>
    .search-table {
        margin-right: -50px !important;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.operaciones.pago_transferencia.pago_transferencia-table')

            </div>
        </div>
    </div>

    @include('pages.operaciones.pago_transferencia.pago_transferencia-form')
    
</div>