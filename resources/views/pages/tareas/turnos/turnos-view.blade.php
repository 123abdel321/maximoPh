<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-12 col-sm-12">
                    @can('turnos create')
                        <button type="button" class="btn btn-primary btn-sm" id="createProyecto">Agregar turnos</button>
                    @endcan
                    <!-- @can('turnos delete')
                        <button type="button" class="btn btn-danger btn-sm" id="createProyecto">Eliminar turnos</button>
                    @endcan -->
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadTurnos">
                        <i id="reloadTurnosIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                        <i id="reloadTurnosIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                <div id="turnos-fullcalender" style="flex-grow: 1; position: relative;"></div>

            </div>
        </div>
    </div>

    @include('pages.tareas.turnos.turnos-form')
    @include('pages.tareas.turnos.turnos-evento')
    
</div>

<script>
    var editarTurnos = '<?php echo auth()->user()->can('turnos update'); ?>';
    var eliminarTurnos = '<?php echo auth()->user()->can('turnos delete'); ?>';
</script>