<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f4f4f4;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:20px;">
                <table width="600" border="0" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:8px;">
                    <!-- Header -->
                    <tr>
                        <td style="background:#075260; padding:20px; text-align:center; border-radius:8px 8px 0 0;">
                            <img src="https://maximoph.co/img/logo_blanco.png" alt="Logo" width="180">
                        </td>
                    </tr>
                    
                    <!-- Contenido -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="color:#333; margin:0 0 15px 0;">Hola, {{ $nombre }}</h2>
                            
                            <div style="color:#555; font-size:16px; line-height:1.6;">
                                {!! $mensaje !!}
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9f9f9; padding:15px; text-align:center; border-top:1px solid #eee;">
                            <p style="color:#777; font-size:14px; margin:5px 0;">
                                Si tienes preguntas, <a href="https://wa.me/3508973619" style="color:#075260;">contáctanos</a>
                            </p>
                            <p style="color:#999; font-size:12px; margin:5px 0;">
                                © {{ date('Y') }} MaximoPH
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>