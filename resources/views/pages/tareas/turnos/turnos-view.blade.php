<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('turnos create')
                        <button type="button" class="btn btn-primary btn-sm" id="createProyecto">Agregar turnos</button>
                    @endcan
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