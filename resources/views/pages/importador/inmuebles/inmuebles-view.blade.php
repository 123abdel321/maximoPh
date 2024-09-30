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
            @include('pages.importador.inmuebles.inmuebles-header')
            </div>
        </div>

        <div id="totales_import_inmuebles" style="content-visibility: auto; overflow: auto; display: none; margin-top: -5px;">
            <div class="row">
            @include('pages.importador.inmuebles.inmuebles-totales')
            </div>
        </div>

        <div id="card-import-inmuebles" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
            @include('pages.importador.inmuebles.inmuebles-table')
            <div style="padding: 5px;"></div>
        </div>

    </div>

</div>