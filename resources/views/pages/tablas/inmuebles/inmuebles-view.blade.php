<style>
    .error {
        color: red;
    }
    .edit-inmueble {
        width: 10px;
    }
    .drop-inmueble {
        width: 10px;
    }
    .fa-inmueble {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('inmueble create')
                    <button type="button" class="btn btn-primary btn-sm" id="createInmuebles">Agregar inmueble</button>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputInmuebles" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                
                @include('pages.tablas.inmuebles.inmuebles-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.inmuebles.inmuebles-form', [
        'valor_total_presupuesto_year_actual' => $valor_total_presupuesto_year_actual,
        'area_total_m2' => $area_total_m2
    ])
    
</div>

<script>
    var editarInmueble = '<?php echo auth()->user()->can('inmueble update'); ?>';
    var eliminarInmueble = '<?php echo auth()->user()->can('inmueble delete'); ?>';
    var area_total_m2 = JSON.parse('<?php echo $area_total_m2; ?>');
    var editar_valor_admon_inmueble = JSON.parse('<?php echo $editar_valor_admon_inmueble; ?>');
    var valor_total_presupuesto_year_actual = JSON.parse('<?php echo $valor_total_presupuesto_year_actual; ?>');
    var numero_total_unidades = JSON.parse('<?php echo $numero_total_unidades; ?>');
</script>