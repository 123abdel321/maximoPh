<!-- A --><td style="{{ $style }}">
    @if ($estadistica->nit)
        {{ $estadistica->nit->numero_documento }}
    @endif
</td>
<!-- B --><td style="{{ $style }}">
    @if ($estadistica->nit)
        {{ $estadistica->nit->nombre_completo }}
    @endif
</td>
<!-- C --><td style="{{ $style }}">
    @if ($estadistica->nit)
        {{ $estadistica->nit->apartamentos }}
    @endif
</td>
<!-- D --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->saldo_anterior }}</td>
<!-- E --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->valor_intereses }}</td>
<!-- F --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->factura }}</td>
<!-- G --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->total_facturas }}</td>
<!-- H --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->total_abono }}</td>
<!-- I --><td style="{{ $style }} font-weight: bold;">{{ $estadistica->saldo }}</td>