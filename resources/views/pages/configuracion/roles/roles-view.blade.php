<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('roles create')
                        <button type="button" class="btn btn-primary btn-sm" id="createRol">Agregar rol</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputRol" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.configuracion.roles.roles-table')

            </div>
        </div>
    </div>

    @include('pages.configuracion.roles.roles-form', ['componentes' => $componentes])
    
</div>

<script>
    var componentesRoles = JSON.parse('<?php echo $componentes; ?>');
    var editarRol = '<?php echo auth()->user()->can('roles update'); ?>';
    var eliminarRol = '<?php echo auth()->user()->can('roles delete'); ?>';
</script>