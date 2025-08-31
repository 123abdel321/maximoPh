<style>
    .table-import-recibos thead {
        padding: 0.3rem 1.2rem !important;
    }
    .table-import-recibos > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }
    .table-import-recibos {
        max-height: 320px;
        overflow: auto;
    }
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
            @include('pages.importador.cuotas_multas.cuotas_multas-header')
            </div>
        </div>

        <div id="totales_import_cuuotas_multas" style="content-visibility: auto; overflow: auto; display: none; margin-top: -5px;">
            <div class="row">
            @include('pages.importador.cuotas_multas.cuotas_multas-totales')
            </div>
        </div>

        <div id="card-import-producto-precios" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
            @include('pages.importador.cuotas_multas.cuotas_multas-table')
            <div style="padding: 5px;"></div>
        </div>

    </div>

</div>