<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo Electrónico</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd;">
                    <!-- Encabezado -->
                    <tr>
                        <td style="background-color: #075260; padding: 20px; text-align: center;">
                            <img src="https://maximoph.co/img/logo_blanco.png" alt="Logo" style="max-width: 200px;">
                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 20px; color: #555555;">
                            <h2 style="font-size: 20px; color: #333333; margin: 0 0 10px;">¡Hola, {{ $nombre }}!</h2>
                            <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">
                                Hemos recibido una solicitud para cambiar la contraseña de tu cuenta MaximoPH.
                            </p>
                            <div style="text-align: center;">
                                <p style="color: #0a93ac; margin-bottom: 10px; margin-top: 30px;">Para continuar con el proceso, utiliza el siguiente código:</p>
                                <p style="color: #0a93ac; font-size: 25px; font-weight: bold; letter-spacing: 0.25em; margin-top: 0px; width: 200px;">
                                    {{ $code_general }}
                                </p>
                            </div>
                            <p style="text-align: center; font-size: 12px; color: #555555;">
                                El código estará vigente solo por 1 hora.
                            </p>
                        </td>
                    </tr>

                    <!-- Pie de página -->
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 14px; color: #777777;">
                            <p style="margin: 0;">
                                ¿Tienes alguna pregunta? <a href="https://wa.me/3508973619?text=Hola,%20necesito%20más%20información%20sobre%20maximoph" style="color: #007bff; text-decoration: none;">Contáctanos</a>.
                            </p>
                            <p style="margin: 0;">
                                © 2024 MaximoPh. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
