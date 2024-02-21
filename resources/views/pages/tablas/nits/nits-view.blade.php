<style>
    .error {
        color: red;
    }
    .edit-comprobante {
        width: 10px;
    }
    .drop-comprobante {
        width: 10px;
    }
    .fa-comprobante {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('cedulas_nits create')
                    <button type="button" class="btn btn-primary btn-sm" id="createNits">Agregar cedula nit</button>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputNits" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                
                @include('pages.tablas.nits.nits-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.nits.nits-form')
    
</div>

<script>
    var editarUsuario = '<?php echo auth()->user()->can('cedulas_nits update'); ?>';
    var eliminarUsuario = '<?php echo auth()->user()->can('cedulas_nits delete'); ?>';
</script>