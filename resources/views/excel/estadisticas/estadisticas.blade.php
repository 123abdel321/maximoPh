
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>
            <th>Cedula</th>
            <th>Nombre</th>
            <th>Ubicación</th>
            <th>Saldo anterior</th>
            <th>Intereses</th>
            <th>Factura</th>
            <th>Total factura</th>
            <th>Total abono</th>
            <th>Saldo final</th>
		</tr>
		</thead>
		<tbody>
		@foreach($estadisticas as $estadistica)
			<tr>
                @if($estadistica->total == 2)
                    @include('excel.estadisticas.celdas', ['style' => 'background-color: #cfe8f3; font-weight: bold;', 'estadistica' => $estadistica])
                @else
                    @include('excel.estadisticas.celdas', ['style' => 'background-color: #FFF;', 'estadistica' => $estadistica])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>