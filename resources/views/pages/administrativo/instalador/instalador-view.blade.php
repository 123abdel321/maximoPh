<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4" style="z-index: 9;">
                @can('instalacion_empresa create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateNuevaEmpresa">
                        Crear empresa
                    </button>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="search" id="searchInputEmpresa" class="form-control form-control-sm search-table" onkeydown="searchEmpresas(event)" placeholder="Buscar">
            </div>
        </div>

    </div>

    <div id="items-tabla-empresa" class="card mb-4" style="content-visibility: auto; overflow: auto;">
        <div class="card-body">
            @include('pages.administrativo.instalador.instalador-table')
        </div>
    </div>

    @include('pages.administrativo.instalador.instalador-form')

</div>

<script>
    var crearEmpresa = '<?php echo auth()->user()->can('instalacion_empresa create'); ?>';
</script>