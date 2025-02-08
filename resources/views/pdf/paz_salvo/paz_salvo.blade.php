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

			.spacer-lite {
				height: 10px;
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

			.header-factura-descuento > th {
				border: 1px solid #ddd;
				background-color: #ebebeb;
				color: black;
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

			.page-break {
				page-break-after: always;
			}

			.minus {
				text-transform: lowercase;
			}

		</style>

	</head>

	<body class="main">

		<table >
			<thead>
				<tr>
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td colspan="7 padding5">
						<table>
							<tr>
								<td class=" padding5">
								</td>
								
								<td class="empresa padding5">
									<h1>{{ $empresa->razon_social }}</h1>
									<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
									<span>TEL: {{ $empresa->telefono }}</span><br>
									<span>{{ $empresa->direccion }}</span><br>
									<span>{{ $empresa->correo }}</span><br>
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
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td class="spacer-lite padding5"></td>
				</tr>
				<tr>
					<td colspan="padding5">
						<table>
							<tr>
								<td class="aling-top padding5">
									<table>
										<tbody>
											<tr>
												<td>
													{!! $texto !!}
												</td>
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
									<table class="width-100">
										<tbody>
											<tr>
												<td>
													<img src="{{$firma_digital}}" style="width: 180px">
												</td>
											</tr>
											<tr>
												<td>
													{!! $nombre_administrador !!}
												</td>
											</tr>
										</tbody>
									</table>
								</td>

								<td class="aling-top padding5">
								</td>
								
								<td class="table-total-factura padding5">
									<table>
										<tbody>
											<tr>
												<td>
													<img src="{{ $qrCode }}" alt="QR Code" style="width: 150px; height: 150px;">
												</td>
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
								www.maximoph.com
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table> 
		
	</body>

</html>