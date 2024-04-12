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
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('porteria create')
                    <button type="button" class="btn btn-primary btn-sm" id="generatePorteriaNueva">
                        Agregar en porteria
                    </button>
                @endcan
                @can('porteria eventos')
                    <button type="button" class="btn btn-primary btn-sm" id="generateEventoPorteria">
                        Agregar evento
                    </button>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputPorteria" class="form-control form-control-sm search-table" onkeydown="searchPorteria(event)" placeholder="Buscar">
            </div>
        </div>

        <div class="card" style="height: 100%; display: none;">
            <div class="row" style="z-index: 9; margin-top: 7px;">

                <div class="form-group col-6 col-sm-4 col-md-4">
                    <label for="formZonaLabel">Persona<span style="color: red">*</span></label>
                    <select name="id_nit_porteria_filter" id="id_nit_porteria_filter" class="form-control form-control-sm">
                    </select>
                </div>
    
                <div class="form-group col-6 col-sm-4 col-md-4">
                    <label for="exampleFormControlSelect1">Categorias<span style="color: red">*</span></label>
                    <select class="form-control form-control-sm" id="id_tipo_porteria_filter">
                        <option value="">TODOS</option>
                        <option value="0">PERSONAS</option>
                        <option value="1">MASCOTAS</option>
                        <option value="2">VEHICULOS</option>
                    </select>
                </div>
    
                <div class="form-group col-6 col-sm-4 col-md-4">
                    <label for="example-text-input" class="form-control-label">Buscar</label>
                    <input type="text" class="form-control form-control-sm" name="searchInputPorteria" id="searchInputPorteria" onkeydown="searchPorteria(event)">
                </div>

            </div>
        </div>

        <div id="loading-porteria" class="row" style="margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); place-content: center;">
            <i class="fa fa-spinner fa-pulse fa-4x fa-fw" style="color: white;width: 150px;"></i>
        </div>

        <div id="items-tabla-porteria" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                
                @include('pages.administrativo.porteria.porteria_evento-table')

            </div>
        </div>
        
        <div id="items-card-porteria" class="row" style="margin-top: 1rem; padding-right: 0px; margin-left: -5px;">

        </div>

        @include('pages.administrativo.porteria.porteria-form')
        @include('pages.administrativo.porteria.porteria_evento-form')

    </div>
</div>

<script>
    var crearPorteria = '<?php echo auth()->user()->can('porteria create'); ?>';
    var eventoPorteria = '<?php echo auth()->user()->can('porteria eventos'); ?>';
</script>