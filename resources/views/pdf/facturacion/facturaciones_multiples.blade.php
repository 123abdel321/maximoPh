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
            font-size: 2.0em;
        }
        
        .fecha-factura {
            color: black;
            font-size: 1.3em;
        }

        .ubicacion-factura {
            color: black;
            font-size: 1.3em;
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

        div.page {
            page-break-inside: avoid;
        }

        /* ESTILOS CORREGIDOS PARA EVITAR PÁGINAS EN BLANCO */
        .page {
            page-break-before: always;
            page-break-inside: avoid;
            position: relative;
            margin: 0;
            padding: 0;
        }
        
        /* SOLUCIÓN DEFINITIVA: usar first-of-type en lugar de first-child */
        .page:first-of-type {
            page-break-before: avoid !important;
        }

        /* Reset de márgenes de página */
        @page {
            margin: 0.5cm;
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
                                        <span class="numero-consecutivo">N° {{ $factura->consecutivo }}</span><br/>
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
                                    <span>TEL: {{ $empresa->telefono }}</span><br>
                                    <span>{{ $empresa->direccion }}</span><br>
                                    <span>{{ $empresa->correo }}</span><br>
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
                                                                <td class="padding3">FECHA DOCUMENTO</td>
                                                                <td class="valor padding3">{{ $factura->fecha_manual }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">FECHA VENCIMIENTO</td>
                                                                <td class="valor padding3">{{ $factura->fecha_plazo }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="padding3">TOTAL DOCUMENTO</td>
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
                                    <th class="padding5">NOMBRE</th>
                                    <th class="padding5">DOCUMENTO</th>
                                    <th class="padding5">SALDO ANTERIOR</th>
                                    <th class="padding5">VALOR FACTURA</th>
                                    <th class="padding5">ANTICIPOS</th>
                                    <th class="padding5">SALDO FINAL</th>
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
                                        <b>TOTAL
                                        @if ($factura->pronto_pago)
                                            SIN DESCUENTO
                                        @endif
                                        </b>
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

                        @if ($factura->pronto_pago && $factura->saldo_final > 0)
                            <table>
                                <thead>
                                    <tr>
                                        <td colspan="8" class="padding5">
                                            &nbsp;
                                        </td>
                                        <td class="table-total-factura padding5">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <td class="spacer-lite"></td>
                                                    </tr>
                                                    <tr class="header-factura-descuento padding5">
                                                        <th class="padding5">FECHA CON DESCUENTO</th>
                                                        <th class="padding5">VALOR CON DESCUENTO</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="detalle-factura">
                                                    @foreach ($factura->descuentos as $descuento)
                                                        <tr>
                                                            <td class="padding5">{{ $descuento['fecha_limite'] }}</td>
                                                            <td class="padding5 valor">{{ number_format($descuento['descuento']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </thead>
                            </table>			
                        @endif
                    </td>
                </tr>
            </thead>
        </table>

        @if ($texto_1 || $texto_2)
            <table>
                <tr>
                    @if ($texto_1)
                    <td class="aling-top padding5">
                        <table>
                            <thead>
                                <tr>
                                    <td colspan="2" class="empresa-footer padding5">{{ $texto_1 }}</td>
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
                                    <td colspan="2" class="empresa-footer padding5">{{ $texto_2 }}</td>
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