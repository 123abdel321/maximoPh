<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.informes.facturaciones.facturaciones-filter')
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.informes.facturaciones.facturaciones-table')
        </div>
    </div>
</div>

<script>
    var periodo_facturaciones = JSON.parse('<?php echo $periodo_facturaciones; ?>');
</script>