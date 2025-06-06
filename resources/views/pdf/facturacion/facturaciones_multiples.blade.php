<html lang="es">

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title></title>

		<style>
			body {
				margin: 0;
				font-family: "Lato", sans-serif;
				line-height: 16px;
				font-size: 15px;
				width: 100%;
				text-transform: uppercase;
			}

			.detalle-factura td {
				border-left: 1px solid #ddd;
				border-right: 1px solid #ddd;
				vertical-align: bottom;
			}

			.detalle-factura>tr:last-child {
				border-bottom: 1px solid #ddd;
				height: 100%;
			}


			.spacer {
				height: 30px;
			}

			.valor {
				text-align: right;
			}

			table {
				width: 100%;
				border-collapse: collapse;
			}

			.table-detail {
				font-size: 15px;
				width: 100%;
				border-collapse: collapse;
				height: 100%;
			}

			.header-factura > th {
				border: 1px solid #ddd;
				background-color: #05434e;
				color: white;
			}

			thead {
				display: table-header-group
			}

			tr {
				page-break-inside: avoid
			}

			.padding5 {
				padding: 5px;
			}

			.padding3 {
				padding: 2px;
			}

			.logo {
				width: 25%;
				text-align: center;
				vertical-align: middle;
				margin: 0px auto;
			}

			.logo img {
				height: 90px;
			}

			.empresa {
				text-align: center;
				width: 50%;
			}

			.empresa-footer {
				text-align: center;
			}

			.empresa-footer-left {
				text-align: center;
				
			}

			.consecutivo {
				width: 25%;
				text-align: center;
				border: 1px solid #f2f2f2;
				line-height: 3em;
			}

			.numero-consecutivo {
				color: #8d00ff;
				font-size: 2.8em;
			}
			
			.fecha-factura {
				color: black;
				font-size: 1.3em;
			}

			.ubicacion-factura {
				color: black;
				font-size: 1.5em;
			}
			
			.generado {
				width: 40%;
			}

			.footer {
				position: fixed;
				bottom: 35px;
				line-height: 15px;
				/* font-family: helvetica,arial,verdana,sans-serif; */
				font-size: 8px;
			}

			.header-total {
				border-bottom: 1px solid #ddd;
			}

			.table-total-factura {
				vertical-align: top;
				width: 40%;
			}

			.aling-top {
				vertical-align: top;
			}

			div.page {
				page-break-inside: avoid;
			}

		</style>

	</head>
	
	@foreach ($facturas as $factura)
		@if (count($factura->cuentas) && $factura->totales)
			<div class="page">
				<body class="main">

					<table >
						<thead>
							<tr>
								<td class="spacer padding5"></td>
							</tr>
							<tr>
								<td colspan="7 padding5">
									<table>
										<tr>
											<td class="consecutivo padding5">
												<p>
													@if ($factura->totales)
														<span span class="numero-consecutivo">N° {{ $factura->totales->consecutivo }}</span><br/>
														<span span class="fecha-factura">{{ $factura->totales->fecha_manual }}</span><br/>
														<span span class="ubicacion-factura">{{ $factura->cuentas[0]->apartamentos }}</span>
													@else
														N/A
													@endif
												</p>
											</td>
											
											<td class="empresa padding5">
												<h1>{{ $empresa->razon_social }}</h1>
												<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
												<span>{{ $empresa->direccion }}</span><br>
												<span>TEL: {{ $empresa->telefono }}</span><br>
											</td>
											
											<td class="logo padding5">
												@if ($empresa->logo)
													<img stype="height:90px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
												@else
													<img style="height:90px;" src="img/logo_contabilidad.png">
												@endif
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</thead>
					</table>

					<table>
						<thead class="">
							<tr>
								<td class="spacer padding5"></td>
							</tr>
							<tr>
								<td colspan="8 padding5">
									<table>
										<tr>
											<td class="aling-top padding5">
												<table>
													<thead>
														<tr>
															<th colspan="2" class="header-total padding5">PROPIETARIO</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td class="padding3">{{ $factura->cuentas[0]->nombre_nit }}</td>
														</tr>
														<tr>
															<td class="padding3">{{ $factura->cuentas[0]->tipo_documento }} N° {{ $factura->cuentas[0]->numero_documento }}</td>
														</tr>
														<tr>
													<td class="padding3">{{ $factura->cuentas[0]->direccion }}
														@if ($factura->cuentas[0]->telefono)
															- TEL: {{ $factura->cuentas[0]->telefono }}
														@endif
													</td>
												</tr>
													</tbody>
												</table>
											</td>
											
											<td class="table-total-factura padding5">
												<table>
													<thead>
														<tr>
															<th colspan="2" class="header-total padding5">TOTALES</th>
														</tr>
													</thead>
													<tbody>
														<tr >
															<th class="padding5">FECHA DOCUMENTO</th>
															<th class="valor padding5">{{ $factura->totales->fecha_manual }}</th>
														</tr>
														<tr >
															<th class="padding5">TOTAL DOCUMENTO</th>
															<th class="valor padding5">{{ number_format($factura->totales->saldo_final) }}</th>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</thead>
					</table>

					<table class="tabla-detalle-factura">
						<thead class="">
							<tr>
								<td class="spacer"></td>
							</tr>
							<tr class="header-factura padding5">
								<th class="padding5">NOMBRE</th>
								<th class="padding5">SALDO ANTERIOR</th>
								<th class="padding5">VALOR FACTURA</th>
								<th class="padding5">TOTAL ABONO</th>
								<th class="padding5">SALDO FINAL</th>
							</tr>
						</thead>
						<tbody class="detalle-factura">
							@foreach ($factura->cuentas as $cuenta)
								<tr>
									<td class="padding5 detalle-factura-descripcion">{{ $cuenta->nombre_cuenta }}</td>
									<td class="padding5 valor">{{ number_format($cuenta->saldo_anterior) }}</td>
									<td class="padding5 valor">{{ number_format($cuenta->total_facturas) }}</td>
									<td class="padding5 valor">{{ number_format($cuenta->total_abono) }}</td>
									<td class="padding5 valor">{{ number_format($cuenta->saldo_final) }}</td>
								</tr>
							@endforeach
							<tr style="background-color: #58978423;">
								<td class="padding5 detalle-factura-descripcion">TOTAL</td>
								<td class="padding5 valor">{{ number_format($factura->totales->saldo_anterior) }}</td>
								<td class="padding5 valor">{{ number_format($factura->totales->total_facturas) }}</td>
								<td class="padding5 valor">{{ number_format($factura->totales->total_abono) }}</td>
								<td class="padding5 valor">{{ number_format($factura->totales->saldo_final) }}</td>
							</tr>
						</tbody>
					</table>

					@if ($texto_1 || $texto_2)
						<table>
							<tr>
								@if ($texto_1)
								<td class="aling-top padding5">
									<table>
										<thead>
											<tr>
												<td colspan="2" class="empresa-footer padding5 minus">{{ $texto_1 }}</td>
											</tr>
										</thead>
									</table>
								</td>
								@endif
								@if ($texto_2)
								<td class="table-total-factura padding5">
									<table>
										<thead>
											<tr>
												<td colspan="2" class="empresa-footer padding5 minus">{{ $texto_2 }}</td>
											</tr>
										</thead>
									</table>
								</td>
								@endif
							</tr>
						</table>
					@endif
							
					<script type="text/php">
						if ( isset($pdf) ) {
							$pdf->page_script('
								$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
								$pdf->text(300, 800, "$PAGE_NUM / $PAGE_COUNT", $font, 8);
							');
						}
					</script>
			
					<table class="footer">
						<tr>
							<td class="padding5 ">
								<table>
									<tr >
										<td class="empresa-footer padding5">
											Maximo PH<br>
											{{ $fecha_pdf }}
										</td>
									</tr>
								</table>
							</td>
							<td class="padding5"></td>
							<td class="padding5 generado">
								<table>
									<tr>
										<td class="empresa-footer-left padding5">
											ESTE INFORME FU&Eacute; GENERADO POR MAXIMO PH <br>
											www.maximoph.co
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table> 
					
				</body>
			</div>
		@endif
	@endforeach

</html>