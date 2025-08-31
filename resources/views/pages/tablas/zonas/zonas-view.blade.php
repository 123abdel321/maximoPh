<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('zona create')
                        <button type="button" class="btn btn-primary btn-sm" id="createZona">Agregar zona</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputZona" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.zonas.zonas-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.zonas.zonas-form')
    
</div>

<script>
    var editarZona = '<?php echo auth()->user()->can('zona update'); ?>';
    var eliminarZona = '<?php echo auth()->user()->can('zona delete'); ?>';
</script>