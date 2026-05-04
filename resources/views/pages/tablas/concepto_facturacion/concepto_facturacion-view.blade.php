<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('concepto_facturacion create')
                        <button type="button" class="btn btn-primary btn-sm" id="createConceptoFacturacion">Agregar conceptos facturación</button>
                    @endcan
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.concepto_facturacion.concepto_facturacion-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.concepto_facturacion.concepto_facturacion-form')
    
</div>

<script>
    var editarConceptoFacturacion =  @json(auth()->user()->can('concepto_facturacion update'));
    var eliminarConceptoFacturacion = @json(auth()->user()->can('concepto_facturacion delete'));
    var dias_pronto_pago = @json($dias_pronto_pago);
</script>