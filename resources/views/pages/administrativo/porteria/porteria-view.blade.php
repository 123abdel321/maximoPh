<style>
    /* COL-12 */
    @media (max-width: 576px) { 
        .img-porteria {
            height: 100% !important;
        }
    }
    /* COL-12 */
    @media (max-width: 576px) {
        .img-porteria {
            max-height: 300PX !important;
        }
    }

    .ribbon {
        color: white;
        position: absolute;
        transform: rotate(45deg);
        text-align: center;
        right: -75px;
        top: 8%;
        width: 200px;
    }

    .text-max-line-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .text-max-line-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
    }

    .status-autorizado-position {
        position: absolute;
        margin: 0;
        position: absolute;
        left: 50%;
        transform: translate(-50%, -0%);
        margin-top: -20px;
    }

    .card-item-porteria {
        transition: .2s;
        cursor: pointer;
    }

    .card-item-porteria:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px #1096ff;
    }

    .text-max-line-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .imagen-porteria-preview {
        background-position: center;
        background-size: contain;
        background-repeat: no-repeat;
        background-attachment: local;
        height: 500px;
    }
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-8 col-sm-8">
                
                @can('porteria create')
                    <button type="button" class="btn btn-primary btn-sm" id="generatePorteriaNueva">
                        Nuevo registro
                    </button>
                @endcan
                @can('porteria eventos')
                    <button type="button" class="btn btn-dark btn-sm" id="volverEventoPorteria" style="width: 10px; height: 34px; display: none;">
                        <i style="margin-left: -8px; color: white; font-size: 15px; margin-top: 2px;" class="fas fa-fast-backward"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="generateEventoPorteria" style="display: none;">
                        Agregar evento
                    </button>
                    <button type="button" class="btn btn-info btn-sm" id="verEventoPorteria">
                        Ver minuta
                    </button>
                @endcan
                <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadPorteria">
                    <i id="reloadPorteriaIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadPorteriaIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>

                <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px; display: none;" id="reloadPorteriaEvento">
                    <i id="reloadPorteriaEventoIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadPorteriaEventoIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>
            </div>
        </div>

        <div id="tabla-porteria" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.administrativo.porteria.porteria-table', ['usuario_rol' => $usuario_rol])

            </div>
        </div>

        <div id="items-tabla-porteria" class="card mb-4" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="card-body">

                @include('pages.administrativo.porteria.porteria_evento-table')

            </div>
        </div>

        @include('pages.administrativo.porteria.porteria_preview')
        @include('pages.administrativo.porteria.porteria_evento-form')
        @include('pages.administrativo.porteria.porteria_evento-show')
        @include('pages.administrativo.porteria.porteria-form', ['usuario_rol' => $usuario_rol])

    </div>
</div>

<script>
    var crearPorteria = '<?php echo auth()->user()->can('porteria create'); ?>';
    var updatePorteria = '<?php echo auth()->user()->can('porteria update'); ?>';
    var deletePorteria = '<?php echo auth()->user()->can('porteria delete'); ?>';
    var eventoPorteria = '<?php echo auth()->user()->can('porteria eventos'); ?>';
    var usuario_rol = JSON.parse('<?php echo $usuario_rol; ?>');
</script>