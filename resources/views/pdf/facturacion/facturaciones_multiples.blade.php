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

        .empresa-title {
            line-height: 1em;
            margin: 0 0 5px 0;
            font-size: 1.3em;
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
            width: 20%;
            text-align: center;
            vertical-align: middle;
            margin: 0px auto;
        }

        .logo img {
            height: 60px;
        }

        .empresa {
            text-align: center;
            width: 55%;
            font-size: 0.85em;
            line-height: 1.3em;
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
            line-height: 1.4em;
            padding: 5px;
        }

        .numero-consecutivo {
            color: #000000;
            font-size: 1.5em;
            font-weight: bold;
        }

        .fecha-factura {
            color: black;
            font-size: 0.95em;
        }

        .ubicacion-factura {
            color: black;
            font-size: 0.85em;
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .generado {
            width: 40%;
        }

        .footer {
            position: fixed;
            bottom: 35px;
            line-height: 15px;
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

<body>

@foreach ($facturas as $index => $factura)
    @if ($factura && property_exists($factura, 'consecutivo'))
    <div class="page">
        <table>
            <thead>
                <tr>
                    <td colspan="7">
                        <table>
                            <tr>
                                <td class="consecutivo">
                                    <p>
                                        <span class="numero-consecutivo">Cuenta de Cobro: {{ $factura->consecutivo }}</span><br/>
                                        <span class="fecha-factura">{{ $factura->fecha_texto }}</span>
                                        @if ($factura->nit)
                                        <br/>
                                        <span class="ubicacion-factura">{{ $factura->nit->apartamentos }}</span>
                                        @endif
                                    </p>
                                </td>

                                <td class="empresa">
                                    <h1 class="empresa-title">{{ $empresa->razon_social }}</h1>
                                    <span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>

									@if ($empresa->telefono)
                                    	<span>TEL: {{ $empresa->telefono }}</span><br>
									@endif
									@if ($empresa->direccion)
                                    	<span>{{ $empresa->direccion }}</span><br>
									@endif
									@if ($empresa->correo)
                                    	<span>{{ $empresa->correo }}</span>
									@endif
                                </td>

                                <td class="logo">
                                    @if ($empresa->logo)
                                        <img style="height:90px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
                                    @else
                                        <img style="height:90px;" src="img/logo_contabilidad.png">
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <table>
                            <thead>
                                <tr>
                                    <td class="spacer-lite padding5"></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="padding5">
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
                                                                <td class="padding3">{{ $factura->nit->nombre_nit }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">{{ $factura->nit->tipo_documento }} N° {{ $factura->nit->numero_documento }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">{{ $factura->nit->direccion }}
                                                                    @if($factura->nit->ciudad)
                                                                        {{ $factura->nit->ciudad }}
                                                                    @endif
                                                                    @if ($factura->nit->telefono)
                                                                        - TEL: {{ $factura->nit->telefono }}
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
                                                            <tr>
                                                                <td class="padding3">FECHA DE EXPEDICIÓN</td>
                                                                <td class="valor padding3">{{ $factura->fecha_manual }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">FECHA DE VENCIMIENTO</td>
                                                                <td class="valor padding3">{{ $factura->fecha_plazo }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">TOTAL A PAGAR</td>
                                                                <td class="valor padding3">{{ number_format($factura->saldo_final) }}</td>
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
                            <thead>
                                <tr>
                                    <td class="spacer-lite"></td>
                                </tr>
                                <tr class="header-factura padding5">
                                    <th class="padding5" style="font-size: 12px;">NOMBRE</th>
                                    <th class="padding5" style="font-size: 12px;">DOCUMENTO</th>
                                    <th class="padding5" style="font-size: 12px;">SALDO ANTERIOR</th>
                                    <th class="padding5" style="font-size: 12px;">VALOR FACTURA</th>
                                    <th class="padding5" style="font-size: 12px;">ANT. / PAGOS</th>
                                    <th class="padding5" style="font-size: 12px;">SALDO FINAL</th>
                                </tr>
                            </thead>
                            <tbody class="detalle-factura">
                                @foreach ($factura->cuentas as $cuenta)
                                <tr>
                                    <td class="padding5 detalle-factura-descripcion">{{ $cuenta->concepto }}</td>
                                    <td class="padding5 detalle-factura-descripcion">{{ $cuenta->documento_referencia }}</td>
                                    <td class="padding5 valor">{{ number_format($cuenta->saldo_anterior) }}</td>
                                    <td class="padding5 valor">{{ number_format($cuenta->total_facturas) }}</td>
                                    <td class="padding5 valor">{{ number_format($cuenta->total_abono) }}</td>
                                    <td class="padding5 valor">{{ number_format($cuenta->saldo_final) }}</td>
                                </tr>
                                @endforeach
                                <tr style="background-color: #58978423;">
                                    <td class="padding5">
                                        <b>TOTAL</b>
                                    </td>
                                    <td class="padding5 valor">{{ COUNT($factura->cuentas) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->saldo_anterior) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->total_facturas) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->total_abono) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->saldo_final) }}</td>
                                </tr>
                            </tbody>

                            @if ($factura->total_anticipos || $factura->descuento)
                            <thead>
                                <tr>
                                    <td class="spacer-lite"></td>
                                </tr>
                                <tr class="header-factura padding5">
                                    <th class="padding5">ANTICIPO</th>
                                    <th class="padding5">VALOR FACTURA</th>
                                    <th class="padding5">DESCUENTO</th>
                                    <th class="padding5">TOTAL FACTURA</th>
                                    @if ($factura->anticipos_disponibles)
                                        <th class="padding5">SALDO A FAVOR</th>
                                    @else
                                        <th class="padding5">TOTAL ADMON</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="detalle-factura">
                                <tr>
                                    <td class="padding5 valor">{{ number_format($factura->total_anticipos) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->total_facturas) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->descuento) }}</td>
                                    <td class="padding5 valor">{{ number_format($factura->total_facturas - $factura->descuento) }}</td>
                                    @if ($factura->anticipos_disponibles)
                                        <td class="padding5 valor">{{ number_format($factura->anticipos_disponibles) }}</td>
                                    @else
                                        <td class="padding5 valor">{{ number_format($factura->saldo_final) }}</td>
                                    @endif
                                </tr>
                            </tbody>
                            @endif

                        </table>

                        <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    @php
                                        $columns_count = 0;
                                        $has_qr = !empty($qrFactura);
                                        $has_texto1 = !empty($texto_1);
                                        $has_texto2 = !empty($texto_2);
                                        $items_count = ($has_qr ? 1 : 0) + ($has_texto1 ? 1 : 0) + ($has_texto2 ? 1 : 0);
                                        
                                        if($items_count == 0) {
                                            $liquidacion_colspan = 4;
                                        } elseif($items_count == 1) {
                                            $liquidacion_colspan = 4;
                                            $item_colspan = 3;
                                        } elseif($items_count == 2) {
                                            $liquidacion_colspan = 4;
                                            $item_colspan = 3;
                                        } else {
                                            $liquidacion_colspan = 4;
                                            $item_colspan = 3;
                                        }
                                    @endphp
                                    
                                    @if($has_qr)
                                    <td colspan="{{ $item_colspan ?? 2 }}" style="width: {{ 100/($items_count + ($liquidacion_colspan/2)) }}%;" class="padding5 aling-top">
                                        <img style="height: 130px; width: auto;" src="{{ $qrFactura }}" alt="QR Factura"/>
                                        <br>
                                        <span style="font-size: 9px;">Escanea para pagar</span>
                                    </td>
                                    @endif

                                    @if($has_texto1)
                                    <td colspan="{{ $item_colspan ?? 2 }}" style="width: {{ 100/($items_count + ($liquidacion_colspan/2)) }}%;" class="padding5 aling-top">
                                        <div style="word-wrap: break-word;">{{ $texto_1 }}</div>
                                    </td>
                                    @endif

                                    @if($has_texto2)
                                    <td colspan="{{ $item_colspan ?? 2 }}" style="width: {{ 100/($items_count + ($liquidacion_colspan/2)) }}%;" class="padding5 aling-top">
                                        <div style="word-wrap: break-word;">{{ $texto_2 }}</div>
                                    </td>
                                    @endif

                                    <td colspan="{{ $liquidacion_colspan }}" style="width: {{ ($liquidacion_colspan/12)*100 }}%;" class="padding5 aling-top">
                                        <table style="width: 100%; border: 1px solid #ddd;">
                                            <thead>
                                                <tr class="header-factura">
                                                    <th colspan="2" class="padding5" style="text-align: center; background-color: #05434e; color: white; font-size: 12px;">
                                                        ESTADO DE CUENTA
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="padding3">Saldo Anterior</td>
                                                    <td class="valor padding3">
                                                        {{ number_format($factura->totales->saldo_anterior) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="padding3">Valor Mensual</td>
                                                    <td class="valor padding3">
                                                        {{ number_format($factura->totales->total_facturas) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="padding3">Anticipos / Pagos</td>
                                                    <td class="valor padding3">
                                                        {{ number_format($factura->totales->total_abono) }}
                                                    </td>
                                                </tr>
                                                @if ($factura->pronto_pago && count($factura->descuentos) > 0)
                                                    @foreach ($factura->descuentos as $descuento)
                                                        <tr style="color: #05434e;">
                                                            <td class="padding3">Descuento hasta el {{ $descuento['fecha_limite'] }}</td>
                                                            <td class="valor padding3">
                                                                {{ number_format($factura->totales->saldo_final - $descuento['descuento']) }}
                                                            </td>
                                                        </tr>
                                                        <tr style="border-top: 1px solid #5c5c5cff; font-weight: bold;">
                                                            <td class="padding3" style="font-weight: bold;">Total con descuento</td>
                                                            <td class="valor padding3">
                                                                {{ number_format($descuento['descuento']) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr style="font-weight: bold;">
                                                        <td class="padding3" style="font-weight: bold;">Total sin descuento</td>
                                                        <td class="valor padding3">
                                                            {{ number_format($factura->totales->saldo_final) }}
                                                        </td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class="padding3" style="font-weight: bold;">Total a pagar</td>
                                                        <td class="valor padding3">
                                                            {{ number_format($factura->totales->saldo_final) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>    
                                        </table>
                                    </td>
                                </tr>
                            </thead>
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
                        <tr>
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
    </div>
    @endif
@endforeach

</body>
</html>