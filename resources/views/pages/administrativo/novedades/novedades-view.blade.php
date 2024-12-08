<style>
    /* COL-12 */
    @media (max-width: 576px) { 
        .img-novedades {
            height: 100% !important;
        }
    }
    /* COL-12 */
    @media (max-width: 576px) {
        .img-novedades {
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

    .card-item-novedades {
        transition: .2s;
        cursor: pointer;
    }

    .card-item-novedades:hover {
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

    .filepond--item {
        width: calc(50% - 0.5em);
    }
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-8 col-sm-8">
                
                @can('novedades create')
                    <button type="button" class="btn btn-primary btn-sm" id="generateNovedadesNueva">
                        Nuevo registro
                    </button>
                @endcan
                <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadNovedades">
                    <i id="reloadNovedadesIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadNovedadesIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.administrativo.novedades.novedades-table')

            </div>
        </div>

        @include('pages.administrativo.novedades.novedades-form')
        @include('pages.administrativo.novedades.novedades-preview')

    </div>
</div>

<script>
    var crearNovedades = '<?php echo auth()->user()->can('novedades create'); ?>';
    var updateNovedades = '<?php echo auth()->user()->can('novedades update'); ?>';
    var deleteNovedades = '<?php echo auth()->user()->can('novedades delete'); ?>';
</script>