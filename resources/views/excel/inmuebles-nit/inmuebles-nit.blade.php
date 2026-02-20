
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            /* Estilos compatibles con wkhtmltopdf */
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 10px;
            }
            
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            .header-table td {
                padding: 5px;
                vertical-align: top;
            }
            
            .logo-cell {
                width: 120px;
                text-align: center;
                vertical-align: middle !important;
            }
            
            .data-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
            }
            
            .data-table th {
                background-color: #2c3e50;
                color: white;
                padding: 8px 4px;
                text-align: left;
                border: 1px solid #34495e;
                font-weight: bold;
            }
            
            .data-table td {
                padding: 6px 4px;
                border: 1px solid #ddd;
            }
            
            .empresa-nombre {
                font-size: 18px;
                font-weight: bold;
                color: #2c3e50;
            }
            
            .informe-nombre {
                font-size: 16px;
                font-weight: bold;
                color: #e74c3c;
            }
            
            .fecha-info {
                font-size: 12px;
                color: #7f8c8d;
            }
            
            .filtros {
                font-size: 11px;
                background-color: #ecf0f1;
                padding: 8px;
                border-radius: 4px;
            }
            
            /* Colores para diferentes niveles */
            .nivel-0 { background-color: #000000; color: white; font-weight: bold; }
            .nivel-1 { background-color: #2c3e50; color: white; font-weight: bold; }
            .nivel-2 { background-color: #34495e; color: white; font-weight: bold; }
            .nivel-4 { background-color: #2980b9; color: white; font-weight: 600; }
            .nivel-6 { background-color: #3498db; color: white; font-weight: 600; }
            .nivel-8 { background-color: #5dade2; color: white; font-weight: 600; }
            
            .grupo-nits { background-color: #d4d4d4; font-weight: 500; }
            .grupo-totales { background-color: #1797c1; font-weight: 600; color: white; }
            .total-final { background-color: #000000; font-weight: bold; color: white; }
            
            /* Estados de saldo */
            .saldo-alerta { background-color: #ff6666; font-weight: bold; }
            .saldo-normal { background-color: #ffffff; }
            
            .numero {
                text-align: right;
                font-family: 'Courier New', monospace;
            }
            
            .texto-centro {
                text-align: center;
            }
        </style>
    </head>

    <body>

        <!-- Encabezado mejorado -->
        <table class="header-table">
            <tr>
                <td class="logo-cell" rowspan="4">
                    <img src="{{ $logo_empresa }}" width="80" style="max-width: 80px;" />
                </td>
                <td class="empresa-nombre">{{ $nombre_empresa }}</td>
            </tr>
            <tr>
                <td class="informe-nombre">{{ $nombre_informe }}</td>
            </tr>
            <tr>
                <td class="fecha-info">
                    <strong>Fecha generación:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }} | 
                    <strong>Usuario:</strong> {{ $usuario ?? 'Sistema' }}
                </td>
            </tr>
        </table>

        <!-- Tabla de datos -->
        <table class="data-table">
            <thead>
            <tr>
                <th>Inmueble</th>
                <th>Zona</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Concepto</th>
                <th>Total %</th>
                <th>Area M2</th>
                <th>Coeficiente</th>
                <th>Valor admon</th>
                <th>Fecha entrega</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inmuebles as $inmueble)
                <tr>
                    <td>{{ $inmueble->nombre }}</td>
                    <td>{{ $inmueble->zona ? $inmueble->zona->nombre : '' }}</td>
                    <td>{{ count($inmueble->personas) ? $inmueble->personas[0]->nit->numero_documento : '' }}</td>
                    <td>{{ count($inmueble->personas) ? $inmueble->personas[0]->nit->nombre_completo : '' }}</td>
                    <td>{{ $inmueble->concepto ? $inmueble->concepto->nombre_concepto : '' }}</td>
                    <td>{{ number_format(100, 2) }}%</td>
                    <td>{{ number_format($inmueble->area, 2) }}</td>
                    <td>{{ number_format($inmueble->coeficiente, 6) }}</td>
                    <td>{{ number_format($inmueble->valor_total_administracion, 2) }}</td>
                    <td class="texto-centro">{{ \Carbon\Carbon::parse($inmueble->fecha_entrega)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>

</html>