<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paz y Salvo</title>

    <style>
        @page {
            margin: 25px 30px 70px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.5;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* WATERMARK ENCIMA */
        .watermark {
            position: fixed;
            top: 42%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 85px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.18);
            transform: rotate(-28deg);
            z-index: 9999;
        }

        /* HEADER */
        .header {
            border-bottom: 2px solid #05434e;
            padding-bottom: 12px;
        }

        .header td {
            vertical-align: middle;
        }

        .empresa {
            width: 75%;
        }

        .empresa-nombre {
            font-size: 22px;
            font-weight: bold;
            color: #05434e;
            margin-bottom: 4px;
        }

        .empresa-info {
            font-size: 11px;
            color: #555;
            line-height: 16px;
        }

        .logo {
            width: 25%;
            text-align: right;
        }

        .logo img {
            max-height: 75px;
            max-width: 140px;
        }

        /* TITULO */
        .titulo-box {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .titulo-documento {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #05434e;
        }

        .titulo-linea {
            width: 120px;
            height: 2px;
            background: #05434e;
            margin: 8px auto 0;
        }

        .subtitulo {
            margin-top: 6px;
            font-size: 11px;
            color: #777;
        }

        /* CONTENIDO */
        .contenido {
            border: 1px solid #d9d9d9;
            padding: 18px 22px;
            text-align: justify;
            font-size: 12px;
            line-height: 21px;
            min-height: 220px;
        }

        .contenido p {
            margin: 0 0 10px 0;
        }

        /* FIRMA */
        .firma-section {
            margin-top: 28px;
        }

        .firma-left {
            width: 72%;
            vertical-align: bottom;
        }

        .firma-right {
            width: 28%;
            text-align: center;
            vertical-align: top;
        }

        .firma-img {
            width: 160px;
            max-height: 70px;
            object-fit: contain;
            display: block;
        }

        .firma-line {
            width: 260px;
            border-top: 1px solid #333;
            margin-top: 8px;
            padding-top: 6px;
        }

        .firma-nombre {
            font-weight: bold;
            font-size: 12px;
        }

        .firma-cargo {
            font-size: 11px;
            color: #555;
        }

        /* QR */
        .qr-box img {
            width: 110px;
            height: 110px;
        }

        .qr-text {
            font-size: 10px;
            color: #666;
            line-height: 14px;
            margin-top: 5px;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            border-top: 1px solid #d8d8d8;
            padding-top: 8px;
            font-size: 9px;
            color: #666;
        }

        .footer-left {
            width: 50%;
        }

        .footer-right {
            width: 50%;
            text-align: right;
        }

        /* PAGINA */
        .page-number {
            position: fixed;
            bottom: 10px;
            right: 0;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

    @if ($marca_agua_svg)
        <div class="watermark">
            NO VÁLIDO
        </div>
    @endif

    <!-- HEADER -->
    <table class="header">
        <tr>

            <td class="empresa">

                <div class="empresa-nombre">
                    {{ $empresa->razon_social }}
                </div>

                <div class="empresa-info">
                    <strong>NIT:</strong>
                    {{ $empresa->nit }}-{{ $empresa->dv }}
                    <br>

                    <strong>Dirección:</strong>
                    {{ $empresa->direccion }}
                    <br>

                    <strong>Tel:</strong>
                    {{ $empresa->telefono }}

                    @if($empresa->correo)
                        | <strong>Email:</strong> {{ $empresa->correo }}
                    @endif
                </div>

            </td>

            <td class="logo">
                @if ($empresa->logo)
                    <img src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
                @else
                    <img src="img/logo_contabilidad.png">
                @endif
            </td>

        </tr>
    </table>

    <!-- TITULO -->
    <div class="titulo-box">

        <div class="titulo-documento">
            PAZ Y SALVO
        </div>

        <div class="titulo-linea"></div>

        <div class="subtitulo">
            Documento emitido por la administración
        </div>

    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        {!! $texto !!}
    </div>

    <!-- FIRMA + QR -->
    <table class="firma-section">
        <tr>

            <td class="firma-left">

                @if($firma_digital)
                    <img
                        src="{{ $firma_digital }}"
                        class="firma-img"
                    >
                @endif

                <div class="firma-line">

                    <div class="firma-nombre">
                        {!! $nombre_administrador !!}
                    </div>

                    <div class="firma-cargo">
                        Administrador(a)
                    </div>

                </div>

            </td>

            <td class="firma-right">

                <div class="qr-box">

                    <img
                        src="{{ $qrCode }}"
                        alt="QR"
                    >

                    <div class="qr-text">
                        Validar autenticidad<br>
                        del documento
                    </div>

                </div>

            </td>

        </tr>
    </table>

    <!-- PAGINACION DOMPDF -->
    <script type="text/php">
        if (isset($pdf)) {

            $pdf->page_script('
                $font = $fontMetrics->get_font("Helvetica", "normal");

                $pdf->text(
                    510,
                    805,
                    "Página $PAGE_NUM de $PAGE_COUNT",
                    $font,
                    8
                );
            ');
        }
    </script>

    <!-- FOOTER -->
    <table class="footer">
        <tr>

            <td class="footer-left">
                <strong>MAXIMO PH</strong><br>
                {{ $fecha_pdf }}
            </td>

            <td class="footer-right">
                ESTE DOCUMENTO FUE GENERADO POR MAXIMO PH<br>
                www.maximoph.co
            </td>

        </tr>
    </table>

</body>
</html>