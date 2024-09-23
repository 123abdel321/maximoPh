<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('proyectos create')
                        <button type="button" class="btn btn-primary btn-sm" id="createProyecto">Agregar proyectos</button>
                    @endcan
                </div>
                <!-- <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputProyectos" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div> -->
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tareas.proyectos.proyectos-table')

            </div>
        </div>
    </div>

    @include('pages.tareas.proyectos.proyectos-form')
    
</div>

<script>
    var editarProyectos = '<?php echo auth()->user()->can('proyectos update'); ?>';
    var eliminarProyectos = '<?php echo auth()->user()->can('proyectos delete'); ?>';
</script>